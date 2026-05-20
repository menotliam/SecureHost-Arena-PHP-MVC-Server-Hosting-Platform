<?php
class Admin extends Controller {
    private $settingModel;
    private $userModel;
    private $productModel;
    private $orderModel;
    private $contactModel;
    private $adminNotificationModel;
    private $commentModel;

    public function __construct() {
        $this->requireAdmin();
        $this->settingModel = $this->model('Setting');
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
        $this->contactModel = $this->model('Contact');
        $this->adminNotificationModel = $this->model('AdminNotification');
        $this->commentModel = $this->model('Comment');
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
    }

    private function getNavBadges() {
        return $this->getAdminNavBadges();
    }

    private function getNotificationStateKey() {
        return 'admin_notification_state_' . (int) ($_SESSION['user_id'] ?? 0);
    }

    private function getNotificationState() {
        $raw = $this->settingModel->getValueByKey($this->getNotificationStateKey(), '');
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $lastOpenedId = (int) ($decoded['last_opened_id'] ?? 0);
        $lastOpenedAt = trim((string) ($decoded['last_opened_at'] ?? ''));
        if ($lastOpenedAt === '') {
            $ticketSeenAt = trim((string) ($decoded['ticket_last_seen_at'] ?? ''));
            $orderSeenAt = trim((string) ($decoded['order_last_seen_at'] ?? ''));
            $lastOpenedAt = max($ticketSeenAt, $orderSeenAt);
        }

        if ($lastOpenedId <= 0 && $lastOpenedAt !== '' && $lastOpenedAt !== '1970-01-01 00:00:00') {
            try {
                $lastOpenedId = (int) $this->adminNotificationModel->getLatestNotificationIdByCreatedAt($lastOpenedAt);
            } catch (Throwable $error) {
                $lastOpenedId = 0;
            }
        }

        return [
            'last_opened_id' => max(0, $lastOpenedId),
            'last_opened_at' => $lastOpenedAt !== '' ? $lastOpenedAt : '1970-01-01 00:00:00'
        ];
    }

    private function saveNotificationState($lastOpenedAt, $lastOpenedId = 0) {
        $payload = json_encode([
            'last_opened_id' => max(0, (int) $lastOpenedId),
            'last_opened_at' => trim((string) $lastOpenedAt) !== '' ? trim((string) $lastOpenedAt) : '1970-01-01 00:00:00'
        ]);
        if ($payload === false) {
            return false;
        }
        return $this->settingModel->upsertValue($this->getNotificationStateKey(), $payload);
    }

    private function isAjaxRequest() {
        $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        return $requestedWith === 'xmlhttprequest' || strpos($accept, 'application/json') !== false;
    }

    private function respondJson($payload, $statusCode = 200) {
        http_response_code((int) $statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit();
    }

    public function notifications() {
        try {
            $this->adminNotificationModel->syncRecentTicketNotifications(200);
            $this->adminNotificationModel->syncCompletedOrderNotifications(200);
            $state = $this->getNotificationState();
            $lastOpenedId = (int) ($state['last_opened_id'] ?? 0);
            $lastOpenedAt = $state['last_opened_at'];
            $rows = $this->adminNotificationModel->getRecentNotifications(40);

            $items = [];
            foreach ($rows as $row) {
                $createdAt = (string) ($row->created_at ?? '');
                $relativeUrl = trim((string) ($row->url ?? ''));
                $href = $relativeUrl !== '' ? (URLROOT . '/' . ltrim($relativeUrl, '/')) : (URLROOT . '/admin');
                $icon = $row->type === 'revenue' ? 'ti-wallet' : 'ti-email';
                $notificationId = (int) ($row->id ?? 0);
                $items[] = [
                    'type' => (string) ($row->type ?? 'ticket'),
                    'icon' => $icon,
                    'title' => (string) ($row->title ?? 'Thông báo'),
                    'message' => (string) ($row->message ?? ''),
                    'href' => $href,
                    'created_at' => $createdAt,
                    'created_at_label' => $createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '',
                    'is_new' => $lastOpenedId > 0
                        ? ($notificationId > $lastOpenedId)
                        : ($createdAt !== '' ? ($createdAt > $lastOpenedAt) : false)
                ];
            }
            $unseenCount = $lastOpenedId > 0
                ? $this->adminNotificationModel->countUnreadSinceId($lastOpenedId)
                : $this->adminNotificationModel->countUnreadSince($lastOpenedAt);
        } catch (Throwable $error) {
            $items = [];
            $unseenCount = 0;
        }

        $this->respondJson([
            'success' => true,
            'unseen_count' => (int) $unseenCount,
            'items' => $items
        ]);
    }

    public function markNotificationsSeen() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'message' => 'Phương thức không hợp lệ.'
            ], 405);
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->respondJson(['success' => false, 'message' => 'Yêu cầu không hợp lệ.'], 403);
        }

        try {
            $this->adminNotificationModel->syncRecentTicketNotifications(200);
            $this->adminNotificationModel->syncCompletedOrderNotifications(200);
            $latestNotificationCreatedAt = $this->adminNotificationModel->getLatestNotificationCreatedAt();
            $latestNotificationId = $this->adminNotificationModel->getLatestNotificationId();
            $openedAt = date('Y-m-d H:i:s');
            if (!empty($latestNotificationCreatedAt) && strcmp((string) $latestNotificationCreatedAt, $openedAt) > 0) {
                $openedAt = (string) $latestNotificationCreatedAt;
            }
            $saved = $this->saveNotificationState($openedAt, $latestNotificationId);
        } catch (Throwable $error) {
            $saved = false;
        }

        $this->respondJson([
            'success' => (bool) $saved
        ], $saved ? 200 : 500);
    }

    private function uploadBrandingAsset($currentFileName = '') {
        if (
            !isset($_FILES['branding_asset']) ||
            !is_array($_FILES['branding_asset']) ||
            (int) ($_FILES['branding_asset']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            return ['success' => true, 'filename' => $currentFileName, 'message' => ''];
        }

        $uploadDir = APPROOT . '/../public/uploads/branding/';
        return SecureUpload::storeBrandingUpload($_FILES['branding_asset'], $uploadDir, $currentFileName);
    }

    /**
     * Hero background: raster images only (jpg/png/gif/webp), stored under uploads/branding/.
     *
     * @return array{success:bool,filename:string,message:string}
     */
    private function uploadHeroBackgroundAsset($currentFileName = '') {
        if (
            !isset($_FILES['hero_bg_asset']) ||
            !is_array($_FILES['hero_bg_asset']) ||
            (int) ($_FILES['hero_bg_asset']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            return ['success' => true, 'filename' => $currentFileName, 'message' => ''];
        }

        $uploadDir = APPROOT . '/../public/uploads/branding/';
        $result = SecureUpload::storeRasterUpload($_FILES['hero_bg_asset'], $uploadDir, 'hero_bg_', 3145728);
        if (empty($result['ok'])) {
            return ['success' => false, 'filename' => $currentFileName, 'message' => (string) ($result['message'] ?? 'Không thể tải ảnh nền lên.')];
        }

        $newFile = (string) ($result['filename'] ?? '');
        $oldFile = basename((string) $currentFileName);
        if ($oldFile !== '' && $oldFile !== $newFile && is_file($uploadDir . $oldFile)) {
            @unlink($uploadDir . $oldFile);
        }

        return ['success' => true, 'filename' => $newFile, 'message' => ''];
    }

    private function normalizeSettingsSection($section) {
        $s = strtolower(trim((string) $section));
        if ($s === 'about') {
            return 'profile';
        }
        if (in_array($s, ['homepage', 'contact', 'profile'], true)) {
            return $s;
        }
        return 'homepage';
    }

    private function buildHomeProductIdsFromPost() {
        $slots = [];
        for ($i = 1; $i <= 4; $i++) {
            $raw = trim((string) ($_POST['home_product_slot_' . $i] ?? ''));
            if ($raw === '') {
                continue;
            }
            $id = (int) $raw;
            if ($id > 0) {
                $slots[] = $id;
            }
        }
        return implode(',', $slots);
    }

    private function validateHomeProductIdsString($idsString, &$errors) {
        if ($idsString === '') {
            return;
        }
        $parts = array_filter(array_map('intval', explode(',', $idsString)), function ($id) {
            return $id > 0;
        });
        $unique = array_unique($parts);
        foreach ($unique as $pid) {
            $rows = $this->productModel->getActiveProductsByIdsOrdered([$pid]);
            if (empty($rows)) {
                $errors['home_product_ids'] = 'Một hoặc nhiều sản phẩm được chọn không hợp lệ hoặc không còn hoạt động.';
                return;
            }
        }
    }

    public function index() {
        $recentUsers = $this->userModel->getRecentUsers(4);
        $revenueRows = $this->orderModel->getLastFiveMonthRevenue();
        $revenueMap = [];
        foreach ($revenueRows as $row) {
            $revenueMap[$row->month_key] = (float) $row->revenue;
        }

        $revenueSeries = [];
        for ($i = 4; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} month"));
            $revenueSeries[] = [
                'label' => 'Thg ' . date('m', strtotime($monthKey . '-01')),
                'revenue' => $revenueMap[$monthKey] ?? 0
            ];
        }

        $activeUsers = $this->userModel->countAdminUsers(['status' => 'active']);
        $unreadTickets = $this->contactModel->countContacts(['status' => 'unread']);

        $data = [
            'title' => 'Bảng điều khiển',
            'stats' => [
                'active_servers' => $this->productModel->countActiveServices(),
                'total_users' => $this->userModel->countAllUsers(),
                'active_users' => $activeUsers,
                'new_users' => $this->userModel->countNewUsersSince(7),
                'monthly_revenue' => $this->orderModel->getMonthlyRevenue(),
                'unread_tickets' => $unreadTickets,
                'system_uptime' => '99.9%'
            ],
            'recent_users' => $recentUsers,
            'revenue_series' => $revenueSeries,
            'system_usage' => [
                'cpu' => 45,
                'memory' => 62,
                'disk' => 38
            ],
            'nav_badges' => $this->getNavBadges()
        ];
        $this->view('admin/index', $data);
    }

    public function services() {
        header('Location: ' . URLROOT . '/adminproducts');
        exit();
    }

    public function tickets() {
        header('Location: ' . URLROOT . '/admincontacts');
        exit();
    }

    public function news() {
        header('Location: ' . URLROOT . '/adminnews');
        exit();
    }

    public function users() {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'role' => trim($_GET['role'] ?? '')
        ];
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 10;

        $totalUsers = $this->userModel->countAdminUsers($filters);
        $lastPage = max(1, (int) ceil($totalUsers / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $users = $this->userModel->getAdminUsers($filters, $page, $perPage);
        $activeUsers = $this->userModel->countAdminUsers(['status' => 'active']);
        $bannedUsers = $this->userModel->countAdminUsers(['status' => 'banned']);

        $flash = $_SESSION['admin_users_flash'] ?? null;
        unset($_SESSION['admin_users_flash']);

        $data = [
            'title' => 'Quản lý người dùng',
            'subtitle' => '',
            'users' => $users,
            'filters' => $filters,
            'pagination' => [
                'page' => $page,
                'last_page' => $lastPage,
                'total' => $totalUsers
            ],
            'summary' => [
                'total' => $this->userModel->countAllUsers(),
                'active' => $activeUsers,
                'banned' => $bannedUsers
            ],
            'flash' => $flash,
            'nav_badges' => $this->getNavBadges()
        ];
        $this->view('admin/users/index', $data);
    }

    public function editUser($userId = 0) {
        $userId = (int) $userId;
        if ($userId <= 0) {
            $_SESSION['admin_users_flash'] = [
                'type' => 'danger',
                'message' => 'Người dùng không hợp lệ.'
            ];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            $_SESSION['admin_users_flash'] = [
                'type' => 'danger',
                'message' => 'Không tìm thấy người dùng cần chỉnh sửa.'
            ];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $errors = [
            'full_name' => '',
            'email' => '',
            'role' => '',
            'status' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf('csrf_admin')) {
                $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
                header('Location: ' . URLROOT . '/admin/users');
                exit();
            }

            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $status = trim($_POST['status'] ?? '');

            if ($fullName === '') {
                $errors['full_name'] = 'Họ tên không được để trống.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            } elseif ($this->userModel->isEmailUsedByAnother($email, $userId)) {
                $errors['email'] = 'Email này đã được sử dụng bởi tài khoản khác.';
            }
            if (!in_array($role, ['admin', 'member'], true)) {
                $errors['role'] = 'Vai trò không hợp lệ.';
            }
            if (!in_array($status, ['active', 'banned'], true)) {
                $errors['status'] = 'Trạng thái không hợp lệ.';
            }

            if (implode('', $errors) === '') {
                $updatedProfile = $this->userModel->updateProfile($userId, $fullName, $email);
                $updatedRole = $this->userModel->updateRole($userId, $role);
                $updatedStatus = $this->userModel->updateStatus($userId, $status);
                $isSuccess = $updatedProfile && $updatedRole && $updatedStatus;

                $_SESSION['admin_users_flash'] = [
                    'type' => $isSuccess ? 'success' : 'danger',
                    'message' => $isSuccess ? 'Đã cập nhật hồ sơ người dùng.' : 'Không thể cập nhật hồ sơ người dùng.'
                ];

                header('Location: ' . URLROOT . '/admin/users');
                exit();
            }

            $targetUser->full_name = $fullName;
            $targetUser->email = $email;
            $targetUser->role = $role;
            $targetUser->status = $status;
        }

        $data = [
            'title' => 'Chỉnh sửa hồ sơ người dùng',
            'subtitle' => '',
            'user' => $targetUser,
            'errors' => $errors,
            'nav_badges' => $this->getNavBadges()
        ];
        $this->view('admin/users/edit', $data);
    }

    public function updateUserRole($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            if ($this->isAjaxRequest()) {
                $this->respondJson(['success' => false, 'message' => 'Yêu cầu không hợp lệ.'], 403);
            }
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $role = trim($_POST['role'] ?? '');
        $updated = $this->userModel->updateRole((int) $userId, $role);
        if ($this->isAjaxRequest()) {
            $this->respondJson([
                'success' => (bool) $updated,
                'message' => $updated ? 'Vai trò đã cập nhật (tự động).' : 'Không thể cập nhật vai trò.'
            ], $updated ? 200 : 422);
        }
        $_SESSION['admin_users_flash'] = [
            'type' => $updated ? 'success' : 'danger',
            'message' => $updated ? 'Đã cập nhật vai trò người dùng.' : 'Không thể cập nhật vai trò.'
        ];

        header('Location: ' . URLROOT . '/admin/users');
        exit();
    }

    public function toggleUserStatus($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $targetStatus = trim($_POST['target_status'] ?? '');
        $updated = $this->userModel->updateStatus((int) $userId, $targetStatus);
        $_SESSION['admin_users_flash'] = [
            'type' => $updated ? 'success' : 'danger',
            'message' => $updated
                ? ($targetStatus === 'banned' ? 'Đã khóa hồ sơ người dùng.' : 'Đã mở khóa hồ sơ người dùng.')
                : 'Không thể cập nhật trạng thái.'
        ];

        header('Location: ' . URLROOT . '/admin/users');
        exit();
    }

    public function resetPassword($userId = 0) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit();
        }

        $userId = (int) $userId;
        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
            exit();
        }

        if ((int) $_SESSION['user_id'] === $userId) {
            echo json_encode(['success' => false, 'message' => 'Không thể reset mật khẩu tài khoản đang đăng nhập.']);
            exit();
        }

        $hashed = password_hash('resetpassword', PASSWORD_DEFAULT);
        if ($this->userModel->updatePassword($userId, $hashed)) {
            echo json_encode(['success' => true, 'message' => 'Reset mật khẩu thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể reset mật khẩu. Vui lòng thử lại.']);
        }
        exit();
    }

    public function deleteUser($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $userId = (int) $userId;
        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            $_SESSION['admin_users_flash'] = [
                'type' => 'danger',
                'message' => 'Không tìm thấy thành viên để xóa.'
            ];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        if ((int) $_SESSION['user_id'] === $userId) {
            $_SESSION['admin_users_flash'] = [
                'type' => 'danger',
                'message' => 'Không thể xóa chính tài khoản quản trị đang đăng nhập.'
            ];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        if (($targetUser->role ?? 'member') === 'admin') {
            $_SESSION['admin_users_flash'] = [
                'type' => 'danger',
                'message' => 'Chỉ cho phép xóa tài khoản thành viên.'
            ];
            header('Location: ' . URLROOT . '/admin/users');
            exit();
        }

        $deleted = $this->userModel->deleteUserById($userId);
        $_SESSION['admin_users_flash'] = [
            'type' => $deleted ? 'success' : 'danger',
            'message' => $deleted ? 'Đã xóa thành viên khỏi hệ thống.' : 'Không thể xóa thành viên.'
        ];
        header('Location: ' . URLROOT . '/admin/users');
        exit();
    }

    public function contacts() {
        header('Location: ' . URLROOT . '/admincontacts');
        exit();
    }

    public function settings($section = 'homepage') {
        $section = $this->normalizeSettingsSection($section);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf('csrf_admin')) {
                $_SESSION['admin_settings_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
                header('Location: ' . URLROOT . '/admin/settings/' . $section);
                exit();
            }

            $postedSection = $this->normalizeSettingsSection($_POST['settings_section'] ?? $section);
            $existingSettings = $this->settingModel->getPublicSettings();
            $formData = $existingSettings;
            $errors = [];

            if ($postedSection === 'homepage') {
                $formData['site_logo_text'] = trim($_POST['site_logo_text'] ?? '');
                $formData['home_hero_title_gradient'] = trim($_POST['home_hero_title_gradient'] ?? '');
                $formData['home_hero_title_plain'] = trim($_POST['home_hero_title_plain'] ?? '');
                $formData['home_hero_subtitle'] = trim($_POST['home_hero_subtitle'] ?? '');
                $formData['home_card_tech_title'] = trim($_POST['home_card_tech_title'] ?? '');
                $formData['home_about_kicker'] = trim($_POST['home_about_kicker'] ?? '');
                $formData['home_about_heading'] = trim($_POST['home_about_heading'] ?? '');
                $formData['home_about_lead'] = trim($_POST['home_about_lead'] ?? '');
                $formData['home_about_feat1_title'] = trim($_POST['home_about_feat1_title'] ?? '');
                $formData['home_about_feat1_text'] = trim($_POST['home_about_feat1_text'] ?? '');
                $formData['home_about_feat2_title'] = trim($_POST['home_about_feat2_title'] ?? '');
                $formData['home_about_feat2_text'] = trim($_POST['home_about_feat2_text'] ?? '');
                $formData['home_about_feat3_title'] = trim($_POST['home_about_feat3_title'] ?? '');
                $formData['home_about_feat3_text'] = trim($_POST['home_about_feat3_text'] ?? '');
                $formData['home_product_ids'] = $this->buildHomeProductIdsFromPost();

                $rk = trim((string) ($_POST['home_review_key'] ?? ''));
                $formData['home_review_key'] = $rk;

                $formData['site_about_snippet'] = trim($_POST['site_about_snippet'] ?? '');
                $formData['site_hotline'] = trim($_POST['site_hotline'] ?? '');
                $formData['site_contact_email'] = trim($_POST['site_contact_email'] ?? '');
                $formData['site_address'] = trim($_POST['site_address'] ?? '');

                if ($formData['site_hotline'] === '') {
                    $errors['site_hotline'] = 'Hotline không được để trống.';
                }
                if (!filter_var($formData['site_contact_email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['site_contact_email'] = 'Email liên hệ không hợp lệ.';
                }
                if ($formData['site_address'] === '') {
                    $errors['site_address'] = 'Địa chỉ không được để trống.';
                }

                if ($formData['site_logo_text'] === '') {
                    $errors['site_logo_text'] = 'Tên hiển thị logo không được để trống.';
                }
                if ($formData['home_hero_title_gradient'] === '') {
                    $errors['home_hero_title_gradient'] = 'Dòng tiêu đề gradient không được để trống.';
                }
                if ($formData['home_hero_title_plain'] === '') {
                    $errors['home_hero_title_plain'] = 'Dòng tiêu đề phụ không được để trống.';
                }
                if ($formData['home_hero_subtitle'] === '') {
                    $errors['home_hero_subtitle'] = 'Đoạn mô tả hero không được để trống.';
                }

                if ($rk !== '') {
                    if (!preg_match('/^[1-9][0-9]*:[1-9][0-9]*$/', $rk)) {
                        $errors['home_review_key'] = 'Giá trị review không hợp lệ.';
                    } else {
                        $parts = explode(':', $rk, 2);
                        $found = $this->commentModel->getApprovedProductReviewByKey((int) $parts[0], (int) $parts[1]);
                        if (!$found) {
                            $errors['home_review_key'] = 'Không tìm thấy review đã duyệt tương ứng.';
                        }
                    }
                }

                $this->validateHomeProductIdsString($formData['home_product_ids'], $errors);

                $formData['site_logo_image'] = trim($existingSettings['site_logo_image'] ?? '');
                $formData['home_hero_bg_image'] = trim($existingSettings['home_hero_bg_image'] ?? '');

                if (!empty($_POST['clear_hero_bg'])) {
                    $oldBg = basename($formData['home_hero_bg_image']);
                    $formData['home_hero_bg_image'] = '';
                    if ($oldBg !== '') {
                        $bgPath = APPROOT . '/../public/uploads/branding/' . $oldBg;
                        if (is_file($bgPath)) {
                            @unlink($bgPath);
                        }
                    }
                }

                if (empty($errors)) {
                    $uploadResult = $this->uploadBrandingAsset($formData['site_logo_image']);
                    if (!$uploadResult['success']) {
                        $errors['branding_asset'] = $uploadResult['message'];
                    } else {
                        $formData['site_logo_image'] = $uploadResult['filename'];
                    }
                }

                if (empty($errors) && empty($_POST['clear_hero_bg'])) {
                    $heroUpload = $this->uploadHeroBackgroundAsset($formData['home_hero_bg_image']);
                    if (!$heroUpload['success']) {
                        $errors['hero_bg_asset'] = $heroUpload['message'];
                    } else {
                        $formData['home_hero_bg_image'] = $heroUpload['filename'];
                    }
                }
            } elseif ($postedSection === 'profile') {
                $formData['profile_page_title'] = trim($_POST['profile_page_title'] ?? '');
                $formData['profile_page_intro'] = trim($_POST['profile_page_intro'] ?? '');
                $formData['profile_section_avatar_title'] = trim($_POST['profile_section_avatar_title'] ?? '');
                $formData['profile_avatar_upload_label'] = trim($_POST['profile_avatar_upload_label'] ?? '');
                $formData['profile_avatar_hint'] = trim($_POST['profile_avatar_hint'] ?? '');
                $formData['profile_section_personal_title'] = trim($_POST['profile_section_personal_title'] ?? '');
                $formData['profile_section_password_title'] = trim($_POST['profile_section_password_title'] ?? '');
                $formData['profile_label_display_name'] = trim($_POST['profile_label_display_name'] ?? '');
                $formData['profile_label_email'] = trim($_POST['profile_label_email'] ?? '');
                $formData['profile_label_current_password'] = trim($_POST['profile_label_current_password'] ?? '');
                $formData['profile_label_new_password'] = trim($_POST['profile_label_new_password'] ?? '');
                $formData['profile_label_confirm_password'] = trim($_POST['profile_label_confirm_password'] ?? '');
                $formData['profile_btn_save'] = trim($_POST['profile_btn_save'] ?? '');
                $formData['profile_btn_update_password'] = trim($_POST['profile_btn_update_password'] ?? '');

                $req = [
                    'profile_page_title' => 'Tiêu đề trang hồ sơ không được để trống.',
                    'profile_page_intro' => 'Mô tả đầu trang không được để trống.',
                    'profile_section_avatar_title' => 'Tiêu đề khối ảnh đại diện không được để trống.',
                    'profile_avatar_upload_label' => 'Nhãn nút tải ảnh không được để trống.',
                    'profile_avatar_hint' => 'Ghi chú định dạng ảnh không được để trống.',
                    'profile_section_personal_title' => 'Tiêu đề khối thông tin không được để trống.',
                    'profile_section_password_title' => 'Tiêu đề khối mật khẩu không được để trống.',
                    'profile_label_display_name' => 'Nhãn họ tên không được để trống.',
                    'profile_label_email' => 'Nhãn email không được để trống.',
                    'profile_label_current_password' => 'Nhãn mật khẩu hiện tại không được để trống.',
                    'profile_label_new_password' => 'Nhãn mật khẩu mới không được để trống.',
                    'profile_label_confirm_password' => 'Nhãn xác nhận mật khẩu không được để trống.',
                    'profile_btn_save' => 'Nhãn nút lưu không được để trống.',
                    'profile_btn_update_password' => 'Nhãn nút cập nhật mật khẩu không được để trống.'
                ];
                foreach ($req as $field => $msg) {
                    if ($formData[$field] === '') {
                        $errors[$field] = $msg;
                    }
                }
            } elseif ($postedSection === 'contact') {
                $contactPostKeys = [
                    'site_map_embed_url',
                    'contact_gate_headline',
                    'contact_gate_headline_accent',
                    'contact_gate_subtitle',
                    'contact_node_card_title',
                    'contact_node_region',
                    'contact_node_online_label',
                    'contact_node_latency_label',
                    'contact_gate_cta_body',
                    'contact_gate_cta_button',
                    'contact_discord_typed_block',
                    'contact_discord_invite_url',
                    'contact_page_title',
                    'contact_page_intro',
                    'contact_sidebar_title',
                    'contact_main_term_title',
                    'contact_main_name_label',
                    'contact_main_email_label',
                    'contact_main_issue_label',
                    'contact_main_issue_hint',
                    'contact_main_msg_label',
                    'contact_main_msg_placeholder',
                    'contact_main_btn_send',
                    'contact_main_btn_reset',
                    'contact_main_cat_heading',
                    'contact_main_back',
                    'contact_main_status_title',
                    'contact_main_status_online',
                    'contact_main_topo_title',
                    'contact_main_stat_lbl_1',
                    'contact_main_stat_val_1',
                    'contact_main_stat_lbl_2',
                    'contact_main_stat_lbl_3',
                    'contact_cat_desc_purchase_issue',
                    'contact_cat_desc_forgot_password',
                    'contact_cat_desc_bugs_technical',
                    'contact_cat_desc_banned',
                    'contact_cat_desc_billing_payment',
                    'contact_cat_desc_others',
                    'contact_form_purchase_order_lbl',
                    'contact_form_purchase_guest',
                    'contact_form_purchase_empty',
                    'contact_form_purchase_opt',
                    'contact_form_forgot_pw_lbl',
                    'contact_form_forgot_pw_ph',
                    'contact_form_banned_user_lbl',
                    'contact_form_banned_user_ph',
                ];
                foreach ($contactPostKeys as $ck) {
                    $formData[$ck] = trim($_POST[$ck] ?? '');
                }

                $contactUiMax = [
                    'contact_main_term_title' => 160,
                    'contact_main_name_label' => 80,
                    'contact_main_email_label' => 80,
                    'contact_main_issue_label' => 120,
                    'contact_main_issue_hint' => 400,
                    'contact_main_msg_label' => 120,
                    'contact_main_msg_placeholder' => 500,
                    'contact_main_btn_send' => 60,
                    'contact_main_btn_reset' => 40,
                    'contact_main_cat_heading' => 160,
                    'contact_main_back' => 120,
                    'contact_main_status_title' => 160,
                    'contact_main_status_online' => 120,
                    'contact_main_topo_title' => 160,
                    'contact_main_stat_lbl_1' => 80,
                    'contact_main_stat_val_1' => 40,
                    'contact_main_stat_lbl_2' => 80,
                    'contact_main_stat_lbl_3' => 80,
                    'contact_cat_desc_purchase_issue' => 300,
                    'contact_cat_desc_forgot_password' => 300,
                    'contact_cat_desc_bugs_technical' => 300,
                    'contact_cat_desc_banned' => 300,
                    'contact_cat_desc_billing_payment' => 300,
                    'contact_cat_desc_others' => 300,
                    'contact_form_purchase_order_lbl' => 160,
                    'contact_form_purchase_guest' => 300,
                    'contact_form_purchase_empty' => 300,
                    'contact_form_purchase_opt' => 120,
                    'contact_form_forgot_pw_lbl' => 160,
                    'contact_form_forgot_pw_ph' => 300,
                    'contact_form_banned_user_lbl' => 120,
                    'contact_form_banned_user_ph' => 300,
                ];
                foreach ($contactUiMax as $fk => $mx) {
                    if (strlen($formData[$fk]) > $mx) {
                        $errors[$fk] = 'Tối đa ' . $mx . ' ký tự.';
                    }
                }

                if ($formData['site_map_embed_url'] !== '' && filter_var($formData['site_map_embed_url'], FILTER_VALIDATE_URL) === false) {
                    $errors['site_map_embed_url'] = 'URL bản đồ không hợp lệ.';
                }
                $reqContact = [
                    'contact_gate_headline' => 'Tiêu đề cổng (phần trước) không được để trống.',
                    'contact_gate_headline_accent' => 'Tiêu đề cổng (phần nhấn màu) không được để trống.',
                    'contact_gate_subtitle' => 'Mô tả phụ cổng không được để trống.',
                    'contact_node_card_title' => 'Tiêu đề card node không được để trống.',
                    'contact_node_region' => 'Nhãn khu vực node không được để trống.',
                    'contact_node_online_label' => 'Nhãn trạng thái online không được để trống.',
                    'contact_node_latency_label' => 'Nhãn độ trễ không được để trống.',
                    'contact_gate_cta_body' => 'Nội dung ô CTA không được để trống.',
                    'contact_gate_cta_button' => 'Nhãn nút Tạo Ticket không được để trống.',
                    'contact_discord_typed_block' => 'Nội dung terminal Discord không được để trống.',
                    'contact_page_title' => 'Tiêu đề trang (meta) không được để trống.',
                    'contact_page_intro' => 'Mô tả trang (meta) không được để trống.',
                ];
                foreach ($reqContact as $field => $msg) {
                    if ($formData[$field] === '') {
                        $errors[$field] = $msg;
                    }
                }
                if (strlen($formData['contact_discord_typed_block']) > 2000) {
                    $errors['contact_discord_typed_block'] = 'Nội dung terminal tối đa 2000 ký tự.';
                }
                if ($formData['contact_discord_invite_url'] !== '') {
                    $parsed = @parse_url($formData['contact_discord_invite_url']);
                    $scheme = isset($parsed['scheme']) ? strtolower((string) $parsed['scheme']) : '';
                    $host = isset($parsed['host']) ? strtolower((string) $parsed['host']) : '';
                    $allowedHosts = ['discord.gg', 'discord.com', 'www.discord.com'];
                    $hostOk = in_array($host, $allowedHosts, true);
                    if ($scheme !== 'https' || !$hostOk || filter_var($formData['contact_discord_invite_url'], FILTER_VALIDATE_URL) === false) {
                        $errors['contact_discord_invite_url'] = 'Chỉ chấp nhận URL https tới discord.gg hoặc discord.com.';
                    }
                }
                if ($formData['contact_sidebar_title'] !== '' && strlen($formData['contact_sidebar_title']) > 120) {
                    $errors['contact_sidebar_title'] = 'Tiêu đề sidebar tối đa 120 ký tự.';
                }
            }

            if (empty($errors)) {
                $saved = $this->settingModel->updatePublicSettings($formData);
                $_SESSION['admin_settings_flash'] = [
                    'type' => $saved ? 'success' : 'danger',
                    'message' => $saved ? 'Đã lưu cài đặt.' : 'Không thể lưu cài đặt. Vui lòng thử lại.'
                ];
                header('Location: ' . URLROOT . '/admin/settings/' . $postedSection);
                exit();
            }

            $data = [
                'title' => 'Cài đặt giao diện công khai',
                'subtitle' => '',
                'settings_section' => $postedSection,
                'settings' => $formData,
                'picker_products' => $this->productModel->getProductPickerList(),
                'picker_reviews' => $this->commentModel->listApprovedProductReviewsForPicker(120),
                'errors' => $errors,
                'flash' => [
                    'type' => 'danger',
                    'message' => 'Vui lòng kiểm tra lại các trường dữ liệu.'
                ],
                'nav_badges' => $this->getNavBadges(),
                'contact_ticket_categories' => $this->contactModel->getSupportTicketCategories(),
            ];
            $this->view('admin/settings/index', $data);
            return;
        }

        $flash = $_SESSION['admin_settings_flash'] ?? null;
        unset($_SESSION['admin_settings_flash']);

        $data = [
            'title' => 'Cài đặt giao diện công khai',
            'subtitle' => '',
            'settings_section' => $section,
            'settings' => $this->settingModel->getPublicSettings(),
            'picker_products' => $this->productModel->getProductPickerList(),
            'picker_reviews' => $this->commentModel->listApprovedProductReviewsForPicker(120),
            'errors' => [],
            'flash' => $flash,
            'nav_badges' => $this->getNavBadges(),
            'contact_ticket_categories' => $this->contactModel->getSupportTicketCategories(),
        ];
        $this->view('admin/settings/index', $data);
    }
}