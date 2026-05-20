<?php
class AdminFaqs extends Controller {
    public function __construct(){
        if(!isAdmin()){
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
    }
    public function index() {
        $faqModel = $this->model('Faq');

        // admin index


            // If a POST arrives with empty $_POST (commonly due to exceeded post_max_size), show a helpful error
            if($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)){
                $_SESSION['flash_error'] = 'Form submission failed — possible upload too large or invalid request.';
                $redirect = URLROOT . '/admin/faqs';
                if(!headers_sent()){
                    header('HTTP/1.1 303 See Other');
                    header('Location: ' . $redirect);
                    exit;
                }
                echo '<script>window.location.href="' . $redirect . '";</script>';
                exit;
            }

        // Central single-path POST dispatch: if a form posts to /AdminFaqs with __action, handle it here.
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['__action'])){
            $action = $_POST['__action'];
            switch($action){
                case 'createCategory':
                    $this->doCreateCategory();
                    break;
                case 'updateCategory':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $this->doUpdateCategory($id);
                    break;
                case 'deleteCategory':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $this->doDeleteCategory($id);
                    break;
                case 'createFaq':
                    $this->doCreateFaq();
                    break;
                case 'updateFaq':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $this->doUpdateFaq($id);
                    break;
                case 'deleteFaq':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $this->doDeleteFaq($id);
                    break;
                case 'replyMessage':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $this->doReplyMessage($id);
                    break;
            }

            // redirect back to admin dashboard to show the updated list and avoid resubmits
            $redirect = URLROOT . '/admin/faqs';
            if(!headers_sent()){
                header('HTTP/1.1 303 See Other');
                header('Location: ' . $redirect);
                exit;
            }
            echo '<script>window.location.href="' . $redirect . '";</script>';
            exit;
        }

        // Admin: require selecting category to list FAQs
        $cats = $faqModel->getCategories();
        $category = isset($_GET['category']) ? trim($_GET['category']) : null;
        $faqs = [];
        $paginationHtml = '';
        if($category){
            // Pagination
            $perPage = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $total = $faqModel->countByCategory($category);
            require_once APPROOT . '/helpers/Pagination.php';
            $base = URLROOT . '/admin/faqs?category=' . urlencode($category);
            $pagination = new Pagination($total, $perPage, $page, $base);
            $faqs = $faqModel->getPageByCategory($pagination->getLimit(), $pagination->getOffset(), $category);
            $paginationHtml = $pagination->createLinks();
        }

        $messages = $faqModel->getMessages(50,0);
        $newCount = $faqModel->getNewMessagesCount();

        $data = ['title' => 'Quản lý FAQ', 'faqs' => $faqs, 'pagination' => $paginationHtml, 'messages' => $messages, 'new_messages' => $newCount, 'categories' => $cats, 'current_category' => $category];
        $this->view('admin/faqs/index', $data);
    }

    public function replyMessage($id = null){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . URLROOT . '/admin/faqs');
            exit;
        }
        $reply = isset($_POST['reply']) ? trim($_POST['reply']) : '';
        $faqModel = $this->model('Faq');
        $adminName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['user_id']) ? 'admin#'.$_SESSION['user_id'] : null);
        $faqModel->replyMessage($id, $reply, $adminName);
        header('Location: ' . URLROOT . '/admin/faqs');
        exit;
    }

    public function create() {
        $faqModel = $this->model('Faq');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'question' => trim($_POST['question']),
                'answer' => isset($_POST['answer']) ? $_POST['answer'] : '-',
                'category' => isset($_POST['category']) ? trim($_POST['category']) : null,
                'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
            ];
            $faqModel->create($payload);
            header('Location: ' . URLROOT . '/admin/faqs');
            exit;
        }

        // GET -> show create form (reuse edit view)
        $categories = $faqModel->getAllCategories();
        $data = ['title' => 'Thêm FAQ', 'faq' => null, 'categories' => $categories];
        $this->view('admin/faqs/edit', $data);
    }

    public function edit($id = null) {
        $faqModel = $this->model('Faq');
        $faq = $faqModel->getById($id);
        $categories = $faqModel->getAllCategories();
        $data = ['title' => 'Sửa FAQ', 'faq' => $faq, 'categories' => $categories];
        $this->view('admin/faqs/edit', $data);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $faqModel = $this->model('Faq');
            $payload = [
                'question' => trim($_POST['question']),
                'answer' => $_POST['answer'],
                'category' => isset($_POST['category']) ? trim($_POST['category']) : null,
                'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
            ];
            $faqModel->update($id, $payload);
        }
        header('Location: ' . URLROOT . '/admin/faqs');
        exit;
    }

    public function delete($id = null) {
        $faqModel = $this->model('Faq');
        $faqModel->delete($id);
        header('Location: ' . URLROOT . '/admin/faqs');
        exit;
    }

    // Category management
    public function createCategory(){
        // Support both direct POST to /AdminFaqs/createCategory and centralized POST to /AdminFaqs with __action
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->doCreateCategory();
        }
        $redirect = URLROOT . '/admin/faqs';
        if(!headers_sent()){
            header('HTTP/1.1 303 See Other');
            header('Location: ' . $redirect);
            exit;
        }
        echo '<script>window.location.href="' . $redirect . '";</script>';
        exit;
    }

    public function updateCategory($id = null){
        // delegate to internal handler for single-path or direct route usage
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->doUpdateCategory($id);
        }
        $redirect = URLROOT . '/admin/faqs';
        if(!headers_sent()){
            header('HTTP/1.1 303 See Other');
            header('Location: ' . $redirect);
            exit;
        }
        echo '<script>window.location.href="' . $redirect . '";</script>';
        exit;
    }

    public function deleteCategory($id = null){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->doDeleteCategory($id);
        }
        $redirect = URLROOT . '/admin/faqs';
        if(!headers_sent()){
            header('HTTP/1.1 303 See Other');
            header('Location: ' . $redirect);
            exit;
        }
        echo '<script>window.location.href="' . $redirect . '";</script>';
        exit;
    }

    // --- Internal handlers for single-path dispatch (use $_POST / $_FILES) ---
    private function doCreateCategory(){
        $imageUrl = null;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        if(empty($title)){
            $_SESSION['flash_error'] = 'Tiêu đề category không được để trống.';
            return false;
        }
            if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
            require_once APPROOT . '/helpers/Upload.php';
            $u = new Upload($_FILES['image']);
            $u->setMaxSize(5 * 1024 * 1024);
            $res = $u->uploadImage(PUBLICROOT . '/uploads/faq_categories');
            if(isset($res['success']) && $res['success']){
                $imageUrl = URLROOT . '/uploads/faq_categories/' . $res['filename'];
            } else {
                $_SESSION['flash_error'] = 'Upload ảnh thất bại: ' . ($res['error'] ?? 'Không xác định');
                $root = dirname(APPROOT);
                $dir = $root . DIRECTORY_SEPARATOR . 'storage';
                if(!is_dir($dir)) @mkdir($dir, 0755, true);
                $file = $dir . DIRECTORY_SEPARATOR . 'faq_debug.log';
                $line = '[' . date('Y-m-d H:i:s') . '] Upload failed during createCategory: ' . ($res['error'] ?? 'Unknown') . PHP_EOL;
                @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
                return false;
            }
        }

        // Try direct DB insert here and surface any exception message to the admin via flash
        try{
            $db = new Database();
            // ensure table exists
            $db->query("CREATE TABLE IF NOT EXISTS faq_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                image VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->execute();

            // slug generation (simple)
            $slug = preg_replace('~[^\\pL0-9]+~u', '-', $title);
            $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
            $slug = preg_replace('~[^-a-zA-Z0-9]+~', '', $slug);
            $slug = strtolower(trim($slug, '-'));
            if(empty($slug)) $slug = 'category-'.time();

            // ensure unique
            $base = $slug; $i = 1;
            while(true){
                $db->query("SELECT COUNT(*) as c FROM faq_categories WHERE slug = :slug");
                $db->bind(':slug', $slug);
                $r = $db->single();
                if(!$r || $r->c == 0) break;
                $slug = $base . '-' . $i; $i++;
            }

            $db->query("INSERT INTO faq_categories (title, slug, image) VALUES (:title, :slug, :image)");
            $db->bind(':title', $title);
            $db->bind(':slug', $slug);
            $db->bind(':image', $imageUrl);
            $db->execute();
            $_SESSION['flash_success'] = 'Category đã được thêm.';
            return true;
        } catch(Exception $e){
            $_SESSION['flash_error'] = 'Lỗi DB: ' . $e->getMessage();
            error_log('AdminFaqs::doCreateCategory exception: ' . $e->getMessage());
            return false;
        }
    }

    private function doUpdateCategory($id){
        $faqModel = $this->model('Faq');
        $imageUrl = null;
        if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
            require_once APPROOT . '/helpers/Upload.php';
            $u = new Upload($_FILES['image']);
            $u->setMaxSize(5 * 1024 * 1024);
            $res = $u->uploadImage(PUBLICROOT . '/uploads/faq_categories');
            if(!empty($res['success'])){
                $imageUrl = URLROOT . '/uploads/faq_categories/' . $res['filename'];
            }
        }
        $payload = ['title' => trim($_POST['title']), 'image' => $imageUrl];
        $ok = $faqModel->updateCategory($id, $payload);
        if($ok) $_SESSION['flash_success'] = 'Category đã được cập nhật.'; else $_SESSION['flash_error'] = 'Không thể cập nhật category. Kiểm tra storage/faq_debug.log.';
        return (bool)$ok;
    }

    private function doDeleteCategory($id){
        $faqModel = $this->model('Faq');
        $ok = $faqModel->deleteCategory($id);
        if($ok) $_SESSION['flash_success'] = 'Category đã được xóa.'; else $_SESSION['flash_error'] = 'Không thể xóa category. Kiểm tra storage/faq_debug.log.';
        return (bool)$ok;
    }

    private function doCreateFaq(){
        $faqModel = $this->model('Faq');
        $payload = [
            'question' => trim($_POST['question']),
            'answer' => isset($_POST['answer']) ? $_POST['answer'] : '-',
            'category' => isset($_POST['category']) ? trim($_POST['category']) : null,
            'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
        ];
        $ok = $faqModel->create($payload);
        if($ok) $_SESSION['flash_success'] = 'FAQ đã được thêm.'; else $_SESSION['flash_error'] = 'Không thể tạo FAQ.';
        return (bool)$ok;
    }

    private function doUpdateFaq($id){
        $faqModel = $this->model('Faq');
        $payload = [
            'question' => trim($_POST['question']),
            'answer' => $_POST['answer'],
            'category' => isset($_POST['category']) ? trim($_POST['category']) : null,
            'status' => isset($_POST['status']) ? $_POST['status'] : 'active'
        ];
        $ok = $faqModel->update($id, $payload);
        if($ok) $_SESSION['flash_success'] = 'FAQ đã được cập nhật.'; else $_SESSION['flash_error'] = 'Không thể cập nhật FAQ.';
        return (bool)$ok;
    }

    private function doDeleteFaq($id){
        $faqModel = $this->model('Faq');
        $ok = $faqModel->delete($id);
        if($ok) $_SESSION['flash_success'] = 'FAQ đã được xóa.'; else $_SESSION['flash_error'] = 'Không thể xóa FAQ.';
        return (bool)$ok;
    }

    private function doReplyMessage($id){
        $faqModel = $this->model('Faq');
        $reply = isset($_POST['reply']) ? trim($_POST['reply']) : '';
        $adminName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['user_id']) ? 'admin#'.$_SESSION['user_id'] : null);
        $ok = $faqModel->replyMessage($id, $reply, $adminName);
        if($ok) $_SESSION['flash_success'] = 'Đã gửi trả lời.'; else $_SESSION['flash_error'] = 'Không thể gửi trả lời.';
        return (bool)$ok;
    }
}
