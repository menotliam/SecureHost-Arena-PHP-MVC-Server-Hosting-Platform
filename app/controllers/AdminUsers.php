<?php
class AdminUsers extends Controller {
    private $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->userModel = $this->model('User');
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

    public function index() {
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
            'flash' => $flash
        ];
        $this->view('admin/users/index', $data);
    }

    public function edit($userId = 0) {
        $userId = (int) $userId;
        if ($userId <= 0) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Người dùng không hợp lệ.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy người dùng.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        $errors = ['full_name' => '', 'email' => '', 'role' => '', 'status' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf('csrf_admin')) {
                $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
                header('Location: ' . URLROOT . '/adminusers');
                exit();
            }

            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $status = trim($_POST['status'] ?? '');

            if ($fullName === '') $errors['full_name'] = 'Họ tên không được để trống.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            } elseif ($this->userModel->isEmailUsedByAnother($email, $userId)) {
                $errors['email'] = 'Email này đã được sử dụng bởi tài khoản khác.';
            }
            if (!in_array($role, ['admin', 'member'], true)) $errors['role'] = 'Vai trò không hợp lệ.';
            if (!in_array($status, ['active', 'banned'], true)) $errors['status'] = 'Trạng thái không hợp lệ.';

            if (implode('', $errors) === '') {
                $updatedProfile = $this->userModel->updateProfile($userId, $fullName, $email);
                $updatedRole = $this->userModel->updateRole($userId, $role);
                $updatedStatus = $this->userModel->updateStatus($userId, $status);
                $isSuccess = $updatedProfile && $updatedRole && $updatedStatus;

                if ($isSuccess) {
                    if (($targetUser->role ?? '') !== $role) {
                        $this->logAudit('user_role_changed', 'user', $userId, [
                            'target_username' => $targetUser->username,
                            'old_role'        => $targetUser->role,
                            'new_role'        => $role
                        ]);
                    }
                    if (($targetUser->status ?? '') !== $status) {
                        $this->logAudit('user_status_changed', 'user', $userId, [
                            'target_username' => $targetUser->username,
                            'old_status'      => $targetUser->status,
                            'new_status'      => $status,
                            'source'          => 'admin_edit_form'
                        ]);
                    }
                    if (($targetUser->email ?? '') !== $email || ($targetUser->full_name ?? '') !== $fullName) {
                        $this->logAudit('profile_updated', 'user', $userId, [
                            'target_username' => $targetUser->username,
                            'old_name'        => (string) ($targetUser->full_name ?? ''),
                            'new_name'        => $fullName,
                            'old_email'       => (string) ($targetUser->email ?? ''),
                            'new_email'       => $email,
                            'source'          => 'admin_edit_form'
                        ]);
                    }
                }

                $_SESSION['admin_users_flash'] = [
                    'type' => $isSuccess ? 'success' : 'danger',
                    'message' => $isSuccess ? 'Đã cập nhật hồ sơ người dùng.' : 'Không thể cập nhật hồ sơ người dùng.'
                ];
                header('Location: ' . URLROOT . '/adminusers');
                exit();
            }
        }

        $data = [
            'title' => 'Chỉnh sửa hồ sơ người dùng',
            'user' => $targetUser,
            'errors' => $errors
        ];
        $this->view('admin/users/edit', $data);
    }

    public function toggleStatus($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        $userId = (int) $userId;
        $targetUser = $this->userModel->getUserById($userId);
        $targetStatus = trim($_POST['target_status'] ?? '');
        $updated = $this->userModel->updateStatus($userId, $targetStatus);

        if ($updated) {
            $this->logAudit('user_status_changed', 'user', $userId, [
                'target_username' => $targetUser ? $targetUser->username : '',
                'old_status'      => $targetUser ? $targetUser->status : '',
                'new_status'      => $targetStatus,
                'source'          => 'admin_quick_toggle'
            ]);
        }

        $_SESSION['admin_users_flash'] = [
            'type' => $updated ? 'success' : 'danger',
            'message' => $updated
                ? ($targetStatus === 'banned' ? 'Đã khóa hồ sơ người dùng.' : 'Đã mở khóa hồ sơ người dùng.')
                : 'Không thể cập nhật trạng thái.'
        ];

        header('Location: ' . URLROOT . '/adminusers');
        exit();
    }

    public function delete($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Yêu cầu không hợp lệ.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        $userId = (int) $userId;
        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Không tìm thấy thành viên để xóa.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        if ((int) $_SESSION['user_id'] === $userId) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Không thể xóa chính tài khoản quản trị đang đăng nhập.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        if (($targetUser->role ?? 'member') === 'admin') {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Chỉ cho phép xóa tài khoản thành viên.'];
            header('Location: ' . URLROOT . '/adminusers');
            exit();
        }

        $deleted = $this->userModel->deleteUserById($userId);
        if ($deleted) {
            $this->logAudit('user_deleted', 'user', $userId, [
                'deleted_username' => $targetUser->username,
                'deleted_email'    => $targetUser->email
            ]);
        }

        $_SESSION['admin_users_flash'] = [
            'type' => $deleted ? 'success' : 'danger',
            'message' => $deleted ? 'Đã xóa thành viên khỏi hệ thống.' : 'Không thể xóa thành viên.'
        ];
        header('Location: ' . URLROOT . '/adminusers');
        exit();
    }

    public function resetPassword($userId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->respondJson(['success' => false, 'message' => 'Yêu cầu không hợp lệ.'], 403);
        }

        $userId = (int) $userId;
        $targetUser = $this->userModel->getUserById($userId);
        if (!$targetUser) {
            $this->respondJson(['success' => false, 'message' => 'Không tìm thấy người dùng.'], 404);
        }

        if ((int) $_SESSION['user_id'] === $userId) {
            $this->respondJson(['success' => false, 'message' => 'Không thể reset mật khẩu tài khoản đang đăng nhập.'], 403);
        }

        $hashed = password_hash('resetpassword', PASSWORD_DEFAULT);
        if ($this->userModel->updatePassword($userId, $hashed)) {
            $this->logAudit('admin_reset_password', 'user', $userId, [
                'target_username' => $targetUser->username
            ]);
            $this->respondJson(['success' => true, 'message' => 'Reset mật khẩu thành công!']);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Không thể reset mật khẩu.'], 500);
        }
    }
}
