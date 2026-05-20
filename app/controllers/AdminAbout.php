<?php
class AdminAbout extends Controller
{
    public function __construct()
    {
        if (!isAdmin()) {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                if (ob_get_length()) {
                    @ob_clean();
                }
                header('Content-Type: application/json', true, 401);
                echo json_encode(['success' => false, 'message' => 'Không có quyền. Vui lòng đăng nhập lại.']);
                exit;
            }
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
    }

    public function index()
    {
        // Allow POST to this route to act as update (some forms may submit to /admin/about)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // If both $_POST and $_FILES are empty, it's often caused by PHP upload/post limits
            $hasPost = !empty($_POST);
            $hasFiles = !empty($_FILES) && array_filter($_FILES, function($f){ return !empty($f['name']); });
            if (!$hasPost && !$hasFiles) {
                // record diagnostic info to help identify server-side POST issues (post_max_size, upload_max_filesize, max_input_vars)
                try {
                    $hdrs = function_exists('getallheaders') ? getallheaders() : [];
                    $debugEmpty = [
                        'time' => date('c'),
                        'uri' => $_SERVER['REQUEST_URI'] ?? '',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                        'headers' => $hdrs,
                        'post_count' => count($_POST),
                        'files_count' => count($_FILES),
                        'raw' => @file_get_contents('php://input')
                    ];
                    @file_put_contents(APPROOT . '/../public/debug-about-empty-request.log', json_encode($debugEmpty, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
                } catch (Throwable $e) {}

                $_SESSION['flash_error'] = 'Form submission failed — request body appears empty. This is commonly caused by PHP limits (post_max_size, upload_max_filesize, or max_input_vars). Try submitting without files or increase those limits in php.ini.';
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            }
            // Detect empty POST (commonly caused by exceeded post_max_size when uploading large files)
            if (empty($_POST) && !empty($_SERVER['REQUEST_METHOD'])) {
                try {
                    $hdrs = function_exists('getallheaders') ? getallheaders() : [];
                    $debugEmpty2 = [
                        'time' => date('c'),
                        'uri' => $_SERVER['REQUEST_URI'] ?? '',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                        'headers' => $hdrs,
                        'post_count' => count($_POST),
                        'files_count' => count($_FILES),
                        'raw' => @file_get_contents('php://input')
                    ];
                    @file_put_contents(APPROOT . '/../public/debug-about-empty-request.log', json_encode($debugEmpty2, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
                } catch (Throwable $e) {}

                $_SESSION['flash_error'] = 'Form submission failed — request body may be too large. Try removing file uploads or increasing post_max_size in PHP settings.';
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            }
            return $this->update();
        }
        $aboutModel = $this->model('About');
        $about = $aboutModel->get();
        $data = ['title' => 'Quản lý giới thiệu', 'about' => $about];
        $this->view('admin/about/index', $data);
    }

    public function update()
    {
        // Log invocation immediately for diagnosis
        try { @file_put_contents(APPROOT . '/../public/debug-about-invoked.log', date('c') . " - update invoked - METHOD=" . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n", FILE_APPEND); } catch (Throwable $e) {}

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aboutModel = $this->model('About');
            // debug logging: capture incoming post/files and current about rows
            try {
                $debug = [];
                $debug['time'] = date('c');
                $debug['post'] = array_map(function ($v) {
                    return is_array($v) ? array_slice($v, 0, 10) : $v;
                }, $_POST);
                $debug['files'] = array_map(function ($f) {
                    return ['name' => $f['name'] ?? null, 'size' => $f['size'] ?? null];
                }, $_FILES);
                $rows = $aboutModel->getAll();
                $debug['about_rows'] = json_decode(json_encode($rows), true);
                @file_put_contents(APPROOT . '/../public/debug-about.log', json_encode($debug, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
            } catch (Throwable $e) { /* ignore debug errors */
            }
            // Verify CSRF token for admin forms
            if (!$this->verifyCsrf('csrf_admin')) {
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                $msg = 'Yêu cầu không hợp lệ. Vui lòng thử lại.';
                if ($isAjax) {
                    if (ob_get_length()) { @ob_clean(); }
                    header('Content-Type: application/json', true, 400);
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                $_SESSION['flash_error'] = $msg;
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            // Safely obtain title: prefer submitted value, otherwise preserve existing
            $title = null;
            if (isset($_POST['title'])) {
                $title = trim((string)$_POST['title']);
            }
            if ($title === null || $title === '') {
                $existing = $aboutModel->get();
                $title = $existing && isset($existing->title) ? $existing->title : '';
            }
            // Validate title — do not allow empty title to be saved
            if ($title === '') {
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                $msg = 'Tiêu đề không được để trống. Vui lòng nhập Tiêu đề trước khi lưu.';
                if ($isAjax) {
                    if (ob_get_length()) {
                        @ob_clean();
                    }
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                $_SESSION['flash_error'] = $msg;
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            }
            // Safely obtain content: prefer submitted content, otherwise preserve existing
            if (isset($_POST['content'])) {
                $content = $_POST['content'];
            } else {
                $existingRow = $aboutModel->get();
                $content = $existingRow && isset($existingRow->content) ? $existingRow->content : '';
            }

            // Admin id: prefer explicit submission, otherwise fall back to current session user
            $admin_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);

            $imageFilename = null;
            $backgroundFilename = null;
            $introFilename = null;
            // Handle main image upload
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
                require_once APPROOT . '/helpers/Upload.php';
                $uploader = new Upload($_FILES['image']);
                $res = $uploader->uploadImage(APPROOT . '/../public/uploads');
                if ($res['success']) {
                    $imageFilename = $res['filename'];
                } else {
                    $errMsg = 'Upload ảnh thất bại: ' . ($res['error'] ?? 'Không rõ lỗi');
                    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                    if ($isAjax) {
                        if (ob_get_length()) {
                            @ob_clean();
                        }
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $errMsg]);
                        exit;
                    }
                    $_SESSION['flash_error'] = $errMsg;
                    header('Location: ' . URLROOT . '/admin/about');
                    exit;
                }
            } else {
                if (isset($_POST['image_text']) && !empty($_POST['image_text'])) {
                    $imageFilename = trim($_POST['image_text']);
                }
            }

            // Handle background upload (for hero/background layer)
            if (isset($_FILES['background']) && !empty($_FILES['background']['name'])) {
                require_once APPROOT . '/helpers/Upload.php';
                $uploader = new Upload($_FILES['background']);
                $res = $uploader->uploadImage(APPROOT . '/../public/uploads');
                if ($res['success']) {
                    $backgroundFilename = $res['filename'];
                } else {
                    $errMsg = 'Upload background thất bại: ' . ($res['error'] ?? 'Không rõ lỗi');
                    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                    if ($isAjax) {
                        if (ob_get_length()) {
                            @ob_clean();
                        }
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $errMsg]);
                        exit;
                    }
                    $_SESSION['flash_error'] = $errMsg;
                    header('Location: ' . URLROOT . '/admin/about');
                    exit;
                }
            } else {
                if (isset($_POST['background_text']) && !empty($_POST['background_text'])) {
                    $backgroundFilename = trim($_POST['background_text']);
                }
            }

            // Handle intro GIF upload
            if (isset($_FILES['intro_gif']) && !empty($_FILES['intro_gif']['name'])) {
                require_once APPROOT . '/helpers/Upload.php';
                $uploader = new Upload($_FILES['intro_gif']);
                $res = $uploader->uploadImage(APPROOT . '/../public/uploads');
                if ($res['success']) {
                    $introFilename = $res['filename'];
                } else {
                    $errMsg = 'Upload intro GIF thất bại: ' . ($res['error'] ?? 'Không rõ lỗi');
                    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                    if ($isAjax) {
                        if (ob_get_length()) {
                            @ob_clean();
                        }
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $errMsg]);
                        exit;
                    }
                    $_SESSION['flash_error'] = $errMsg;
                    header('Location: ' . URLROOT . '/admin/about');
                    exit;
                }
            } else {
                if (isset($_POST['intro_gif_text']) && !empty($_POST['intro_gif_text'])) {
                    $introFilename = trim($_POST['intro_gif_text']);
                }
            }

            // Helper to upload multiple files (returns array of filenames or nulls)
            $uploadMultiple = function ($fileArrayName) {
                $results = [];
                if (!isset($_FILES[$fileArrayName])) return $results;
                require_once APPROOT . '/helpers/Upload.php';
                $files = $_FILES[$fileArrayName];
                $count = is_array($files['name']) ? count($files['name']) : 0;
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    if (empty($file['name'])) {
                        $results[] = null;
                        continue;
                    }
                    $u = new Upload($file);
                    $res = $u->uploadImage(APPROOT . '/../public/uploads');
                    if ($res['success']) $results[] = $res['filename'];
                    else $results[] = null;
                }
                return $results;
            };

            // Process partners
            $partners = [];
            $partners_names = isset($_POST['partners_name']) && is_array($_POST['partners_name']) ? $_POST['partners_name'] : [];
            $partners_urls = isset($_POST['partners_url']) && is_array($_POST['partners_url']) ? $_POST['partners_url'] : [];
            $partners_logo_uploads = $uploadMultiple('partners_logo');
            $partners_logo_text = isset($_POST['partners_logo_text']) && is_array($_POST['partners_logo_text']) ? $_POST['partners_logo_text'] : [];
            $pcount = max(count($partners_names), count($partners_logo_uploads), count($partners_logo_text));
            for ($i = 0; $i < $pcount; $i++) {
                $name = isset($partners_names[$i]) ? trim($partners_names[$i]) : '';
                $url = isset($partners_urls[$i]) ? trim($partners_urls[$i]) : '';
                $logo = isset($partners_logo_uploads[$i]) && $partners_logo_uploads[$i] ? $partners_logo_uploads[$i] : (isset($partners_logo_text[$i]) ? trim($partners_logo_text[$i]) : null);
                if ($name || $logo) {
                    $partners[] = ['name' => $name, 'url' => $url, 'logo' => $logo];
                }
            }

            // Process modpacks
            $modpacks = [];
            $mp_names = isset($_POST['modpacks_name']) && is_array($_POST['modpacks_name']) ? $_POST['modpacks_name'] : [];
            $mp_urls = isset($_POST['modpacks_url']) && is_array($_POST['modpacks_url']) ? $_POST['modpacks_url'] : [];
            $mp_logo_uploads = $uploadMultiple('modpacks_logo');
            $mp_logo_text = isset($_POST['modpacks_logo_text']) && is_array($_POST['modpacks_logo_text']) ? $_POST['modpacks_logo_text'] : [];
            $mpcount = max(count($mp_names), count($mp_logo_uploads), count($mp_logo_text));
            for ($i = 0; $i < $mpcount; $i++) {
                $name = isset($mp_names[$i]) ? trim($mp_names[$i]) : '';
                $url = isset($mp_urls[$i]) ? trim($mp_urls[$i]) : '';
                $logo = isset($mp_logo_uploads[$i]) && $mp_logo_uploads[$i] ? $mp_logo_uploads[$i] : (isset($mp_logo_text[$i]) ? trim($mp_logo_text[$i]) : null);
                if ($name || $logo) {
                    $modpacks[] = ['name' => $name, 'url' => $url, 'logo' => $logo];
                }
            }

            // Services removed — managed via Modpacks if needed

            // Process sections (title + html content) - admin can add multiple sections
            $sections = [];
            $sec_titles = isset($_POST['section_title']) && is_array($_POST['section_title']) ? $_POST['section_title'] : [];
            $sec_contents = isset($_POST['section_content']) && is_array($_POST['section_content']) ? $_POST['section_content'] : [];
            $secCount = max(count($sec_titles), count($sec_contents));
            for ($i = 0; $i < $secCount; $i++) {
                $st = isset($sec_titles[$i]) ? trim($sec_titles[$i]) : '';
                $sc = isset($sec_contents[$i]) ? trim($sec_contents[$i]) : '';
                if ($st || $sc) {
                    $sections[] = ['title' => $st, 'content' => $sc];
                }
            }

            // Process gallery
            $gallery = [];
            $gallery_titles = isset($_POST['gallery_title']) && is_array($_POST['gallery_title']) ? $_POST['gallery_title'] : [];
            $gallery_captions = isset($_POST['gallery_caption']) && is_array($_POST['gallery_caption']) ? $_POST['gallery_caption'] : [];
            $gallery_uploads = $uploadMultiple('gallery_images');
            $gallery_text = isset($_POST['gallery_image_text']) && is_array($_POST['gallery_image_text']) ? $_POST['gallery_image_text'] : [];
            $gcount = max(count($gallery_titles), count($gallery_captions), count($gallery_uploads), count($gallery_text));
            for ($i = 0; $i < $gcount; $i++) {
                $g_title = isset($gallery_titles[$i]) ? trim($gallery_titles[$i]) : '';
                $g_caption = isset($gallery_captions[$i]) ? trim($gallery_captions[$i]) : '';
                $img = isset($gallery_uploads[$i]) && $gallery_uploads[$i] ? $gallery_uploads[$i] : (isset($gallery_text[$i]) ? trim($gallery_text[$i]) : null);
                if ($img) {
                    $gallery[] = ['image' => $img, 'title' => $g_title, 'caption' => $g_caption];
                }
            }

            $payload = [
                'id' => $id,
                'title' => $title,
                'subtitle' => isset($_POST['subtitle']) ? trim($_POST['subtitle']) : null,
                'content' => $content,
                'image' => $imageFilename,
                'background' => $backgroundFilename,
                'intro_gif' => $introFilename,
                'intro_duration' => isset($_POST['intro_duration']) ? (float)$_POST['intro_duration'] : null,
                'admin_id' => $admin_id,
                'uptime' => isset($_POST['uptime']) ? trim($_POST['uptime']) : null,
                'support' => isset($_POST['support']) ? trim($_POST['support']) : null,
                'performance' => isset($_POST['performance']) ? trim($_POST['performance']) : null,
                'years_active' => isset($_POST['years_active']) ? trim($_POST['years_active']) : null,
                'founded_year' => isset($_POST['founded_year']) ? (int)$_POST['founded_year'] : null,
                'partners' => !empty($partners) ? json_encode($partners, JSON_UNESCAPED_UNICODE) : null,
                'modpacks' => !empty($modpacks) ? json_encode($modpacks, JSON_UNESCAPED_UNICODE) : null,
                'gallery' => !empty($gallery) ? json_encode($gallery, JSON_UNESCAPED_UNICODE) : null,
                'sections' => !empty($sections) ? json_encode($sections, JSON_UNESCAPED_UNICODE) : null,
                'partners_heading' => isset($_POST['partners_heading']) ? trim($_POST['partners_heading']) : null,
                'modpacks_heading' => isset($_POST['modpacks_heading']) ? trim($_POST['modpacks_heading']) : null,
                'gallery_heading' => isset($_POST['gallery_heading']) ? trim($_POST['gallery_heading']) : null,
                'cta_heading' => isset($_POST['cta_heading']) ? trim($_POST['cta_heading']) : null,
                'cta_text' => isset($_POST['cta_text']) ? trim($_POST['cta_text']) : null,
                'cta_button_text' => isset($_POST['cta_button_text']) ? trim($_POST['cta_button_text']) : null,
                'cta_button_url' => isset($_POST['cta_button_url']) ? trim($_POST['cta_button_url']) : null
            ];

            // optional icon fields
            if (isset($_POST['uptime_icon'])) $payload['uptime_icon'] = trim($_POST['uptime_icon']);
            if (isset($_POST['support_icon'])) $payload['support_icon'] = trim($_POST['support_icon']);
            if (isset($_POST['performance_icon'])) $payload['performance_icon'] = trim($_POST['performance_icon']);

            try {
                // Enforce single canonical about row (id = 1) via upsert
                $result = $aboutModel->upsert($payload);
                // If AJAX request, return JSON and do not redirect
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    if (ob_get_length()) {
                        @ob_clean();
                    }
                    header('Content-Type: application/json');
                    if (!$result) {
                        echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu vào cơ sở dữ liệu.']);
                        exit;
                    }
                    // fetch saved about to return updated fields
                    $savedId = null;
                    if (is_numeric($result) && (int)$result > 0) {
                        $savedId = (int)$result;
                    } elseif (isset($payload['id']) && $payload['id']) {
                        $savedId = (int)$payload['id'];
                    } elseif ($id) {
                        $savedId = (int)$id;
                    }
                    $saved = $savedId ? $aboutModel->getById($savedId) : $aboutModel->get();
                    // cleanup old empty-title rows to avoid duplicate empty records
                    try {
                        $aboutModel->cleanupEmptyTitles($savedId ? $savedId : null);
                    } catch (Exception $e) { /* ignore cleanup errors */
                    }
                    $savedArr = $saved ? json_decode(json_encode($saved), true) : null;
                    // post-save debug: record payload and saved row for diagnosis
                    try {
                        $postDebug = ['time' => date('c'), 'payload' => $payload, 'saved' => $savedArr];
                        @file_put_contents(APPROOT . '/../public/debug-about.log', json_encode($postDebug, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
                    } catch (Throwable $e) { /* ignore */
                    }
                    echo json_encode(['success' => true, 'message' => 'Lưu thay đổi thành công.', 'about' => $savedArr]);
                    exit;
                }
                // Non-AJAX: set flash message then redirect so admin sees success
                try {
                    // fetch saved row for logging and ensure title present
                    $saved = $aboutModel->get();
                    $savedArr = $saved ? json_decode(json_encode($saved), true) : null;
                    $postDebug = ['time' => date('c'), 'payload' => $payload, 'saved' => $savedArr, 'note' => 'non-ajax flow'];
                    @file_put_contents(APPROOT . '/../public/debug-about.log', json_encode($postDebug, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
                } catch (Throwable $e) { /* ignore logging errors */
                }
                $_SESSION['flash_success'] = 'Lưu thay đổi thành công.';
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            } catch (Exception $e) {
                $msg = 'Lỗi khi lưu: ' . $e->getMessage();
                // AJAX response
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    if (ob_get_length()) {
                        @ob_clean();
                    }
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                }
                $_SESSION['flash_error'] = $msg;
                header('Location: ' . URLROOT . '/admin/about');
                exit;
            }
        }
        header('Location: ' . URLROOT . '/admin/about');
        exit;
    }
}
