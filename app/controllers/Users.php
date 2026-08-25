<?php
class Users extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
    }

    // GET /users/login  — show form
    // POST /users/login — process credentials
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT);
            exit();
        }

        if (empty($_SESSION['csrf_login'])) {
            $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $data = [
                'title'        => 'Đăng nhập',
                'csrf_token'   => $_SESSION['csrf_login'],
                'username'     => htmlspecialchars($username),
                'password'     => '',
                'username_err' => '',
                'password_err' => '',
                'login_err'    => '',
            ];

            $submittedToken = trim((string) ($_POST['csrf_token'] ?? ''));
            if (!hash_equals((string) ($_SESSION['csrf_login'] ?? ''), $submittedToken)) {
                $data['login_err'] = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang.';
                $this->view('client/users/login', $data);
                return;
            }

            // Database-backed Failed Login Tracking (Chống Brute-force theo IP & Username)
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $auditModel = $this->model('AuditLog');
            $failedCount = $auditModel->countRecentFailures($username, $ip, 10);
            if ($failedCount >= 5) {
                $latestFailure = $auditModel->getLatestFailureTime($username, $ip, 10);
                $passedSeconds = $latestFailure ? max(0, time() - strtotime($latestFailure)) : 0;
                $remainingSeconds = max(1, min(600, 600 - $passedSeconds));
                $remainingMinutes = (int) ceil($remainingSeconds / 60);

                $this->logAudit('login_blocked_lockout', 'user', null, [
                    'username'            => $username,
                    'failed_attempts'     => $failedCount,
                    'retry_after_seconds' => $remainingSeconds
                ]);

                $data['login_err'] = "Bạn đã đăng nhập sai {$failedCount} lần liên tiếp. Vui lòng thử lại sau {$remainingMinutes} phút.";
                $this->view('client/users/login', $data);
                return;
            }

            if (empty($username)) {
                $data['username_err'] = 'Vui lòng nhập tên đăng nhập.';
            }
            if (empty($password)) {
                $data['password_err'] = 'Vui lòng nhập mật khẩu.';
            }

            if (empty($data['username_err']) && empty($data['password_err'])) {
                $loggedInUser = $this->userModel->login($username, $password);

                if ($loggedInUser) {
                    if ($loggedInUser->status === 'banned') {
                        $this->logAudit('login_blocked_banned', 'user', (int) $loggedInUser->id, [
                            'username' => $username,
                            'reason'   => 'account_banned'
                        ]);
                        $data['login_err'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.';
                        $this->view('client/users/login', $data);
                        return;
                    }

                    // Set session
                    $_SESSION['user_id']   = $loggedInUser->id;
                    $_SESSION['user_name'] = $loggedInUser->full_name ?: $loggedInUser->username;
                    $_SESSION['user_role'] = $loggedInUser->role;
                    $_SESSION['user_avatar'] = $loggedInUser->avatar ?? '';
                    session_regenerate_id(true);

                    $this->logAudit('login_success', 'user', (int) $loggedInUser->id, [
                        'username' => $loggedInUser->username,
                        'role'     => $loggedInUser->role
                    ], (int) $loggedInUser->id);

                    // If "remember me" checked, extend the session cookie lifetime so browser keeps the session
                    if (!empty($_POST['remember-me'])) {
                        $cookieParams = session_get_cookie_params();
                        $expire = time() + 60 * 60 * 24 * 30; // 30 days
                        if (PHP_VERSION_ID >= 70300) {
                            setcookie(session_name(), session_id(), [
                                'expires' => $expire,
                                'path' => $cookieParams['path'] ?? '/',
                                'domain' => $cookieParams['domain'] ?? '',
                                'secure' => $cookieParams['secure'] ?? false,
                                'httponly' => true,
                                'samesite' => $cookieParams['samesite'] ?? 'Lax'
                            ]);
                            setcookie('remember_me', '1', [
                                'expires' => $expire,
                                'path' => $cookieParams['path'] ?? '/',
                                'domain' => $cookieParams['domain'] ?? '',
                                'secure' => $cookieParams['secure'] ?? false,
                                'httponly' => false,
                                'samesite' => $cookieParams['samesite'] ?? 'Lax'
                            ]);
                        } else {
                            setcookie(session_name(), session_id(), $expire, $cookieParams['path'] . '; SameSite=Lax', $cookieParams['domain'] ?? '', $cookieParams['secure'] ?? false, true);
                            setcookie('remember_me', '1', $expire, $cookieParams['path'] . '; SameSite=Lax', $cookieParams['domain'] ?? '', $cookieParams['secure'] ?? false, false);
                        }
                    }
                    unset($_SESSION['login_last_submit']);

                    if ($loggedInUser->role === 'admin') {
                        header('Location: ' . URLROOT . '/admin');
                    } else {
                        header('Location: ' . URLROOT);
                    }
                    exit();
                } else {
                    $this->logAudit('login_failed', 'user', null, [
                        'username' => $username,
                        'reason'   => 'invalid_credentials'
                    ]);
                    $data['login_err'] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                    $_SESSION['login_last_submit'] = time();
                }
            }

            $this->view('client/users/login', $data);
            return;
        }

        // GET — show blank form
        $data = [
            'title'        => 'Đăng nhập',
            'csrf_token'   => $_SESSION['csrf_login'],
            'username'     => '',
            'password'     => '',
            'username_err' => '',
            'password_err' => '',
            'login_err'    => '',
        ];
        $this->view('client/users/login', $data);
    }

    // GET /users/register  — show form
    // POST /users/register — process registration
    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT);
            exit();
        }

        if (empty($_SESSION['csrf_register'])) {
            $_SESSION['csrf_register'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username         = trim($_POST['username'] ?? '');
            $fullName         = trim($_POST['full_name'] ?? '');
            $email            = trim($_POST['email'] ?? '');
            $password         = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');

            $data = [
                'title'                => 'Đăng ký',
                'csrf_token'           => $_SESSION['csrf_register'],
                'username'             => htmlspecialchars($username),
                'full_name'            => htmlspecialchars($fullName),
                'email'                => htmlspecialchars($email),
                'password'             => '',
                'confirm_password'     => '',
                'username_err'         => '',
                'full_name_err'        => '',
                'email_err'            => '',
                'password_err'         => '',
                'confirm_password_err' => '',
            ];

            $submittedToken = trim((string) ($_POST['csrf_token'] ?? ''));
            if (!hash_equals((string) ($_SESSION['csrf_register'] ?? ''), $submittedToken)) {
                $data['username_err'] = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang.';
                $this->view('client/users/register', $data);
                return;
            }

            // Validate username
            if (empty($username)) {
                $data['username_err'] = 'Vui lòng nhập tên đăng nhập.';
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $data['username_err'] = 'Tên đăng nhập phải từ 3 đến 50 ký tự.';
            } elseif (!preg_match('/^[a-zA-Z0-9$@_!]+$/', $username)) {
                $data['username_err'] = 'Tên đăng nhập chỉ dùng chữ cái, số và ký tự $ @ _ !';
            } elseif ($this->userModel->findUserByUsername($username)) {
                $data['username_err'] = 'Tên đăng nhập đã tồn tại.';
            }

            // Validate email
            if (empty($email)) {
                $data['email_err'] = 'Vui lòng nhập email.';
            } elseif (!preg_match('/^[a-zA-Z0-9@.]+$/', $email)) {
                $data['email_err'] = 'Email chỉ được chứa chữ cái, số, @ và dấu chấm.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Email không hợp lệ.';
            } elseif ($this->userModel->findUserByEmail($email)) {
                $data['email_err'] = 'Email này đã được sử dụng.';
            }

            // Validate optional display name
            if ($fullName !== '') {
                if (strlen($fullName) > 100) {
                    $data['full_name_err'] = 'Tên hiển thị tối đa 100 ký tự.';
                } elseif (!preg_match('/^[a-zA-ZÀ-ỹ\s]+$/u', $fullName)) {
                    $data['full_name_err'] = 'Tên hiển thị chỉ được chứa chữ cái và khoảng cách.';
                }
            }

            // Validate password
            if (empty($password)) {
                $data['password_err'] = 'Vui lòng nhập mật khẩu.';
            } elseif (strlen($password) < 6) {
                $data['password_err'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            }

            // Validate confirm password
            if (empty($confirm_password)) {
                $data['confirm_password_err'] = 'Vui lòng xác nhận mật khẩu.';
            } elseif ($password !== $confirm_password) {
                $data['confirm_password_err'] = 'Mật khẩu xác nhận không khớp.';
            }

            $hasErrors = $data['username_err'] || $data['full_name_err'] || $data['email_err']
                      || $data['password_err'] || $data['confirm_password_err'];

            if (!$hasErrors) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);

                $registerData = [
                    'username' => $username,
                    'full_name' => $fullName === '' ? $username : $fullName,
                    'email'    => $email,
                    'password' => $data['password'],
                ];

                if ($this->userModel->register($registerData)) {
                    unset($_SESSION['csrf_register']);
                    $_SESSION['register_success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    header('Location: ' . URLROOT . '/users/login');
                    exit();
                } else {
                    $data['username_err'] = 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.';
                }
            }

            $this->view('client/users/register', $data);
            return;
        }

        // GET — show blank form
        $data = [
            'title'                => 'Đăng ký',
            'csrf_token'           => $_SESSION['csrf_register'],
            'username'             => '',
            'full_name'            => '',
            'email'                => '',
            'password'             => '',
            'confirm_password'     => '',
            'username_err'         => '',
            'full_name_err'        => '',
            'email_err'            => '',
            'password_err'         => '',
            'confirm_password_err' => '',
        ];
        $this->view('client/users/register', $data);
    }

    // GET /users/logout
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAudit('logout', 'user', (int) $_SESSION['user_id'], [
                'username' => (string) ($_SESSION['user_name'] ?? '')
            ], (int) $_SESSION['user_id']);
        }

        // Clear session variables
        $_SESSION = [];

        // Destroy session cookie if present
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Also clear our remember_me flag cookie if present
        $params = session_get_cookie_params();
        if (isset($_COOKIE['remember_me'])) {
            if (PHP_VERSION_ID >= 70300) {
                setcookie('remember_me', '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'] ?? '/',
                    'domain' => $params['domain'] ?? '',
                    'secure' => $params['secure'] ?? false,
                    'httponly' => false,
                    'samesite' => $params['samesite'] ?? 'Lax'
                ]);
            } else {
                setcookie('remember_me', '', time() - 42000, $params['path'] . '; SameSite=Lax', $params['domain'] ?? '', $params['secure'] ?? false, false);
            }
        }

        // Finally destroy the session
        session_destroy();

        // Redirect to homepage
        header('Location: ' . URLROOT . '/');
        exit();
    }

    public function profile() {
        $this->requireAuth();
        $currentUser = $this->userModel->getUserById((int) $_SESSION['user_id']);
        if (!$currentUser) {
            session_destroy();
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }

        if (empty($_SESSION['csrf_profile'])) {
            $_SESSION['csrf_profile'] = bin2hex(random_bytes(32));
        }

        $errors = [
            'full_name' => '',
            'email' => '',
            'current_password' => '',
            'new_password' => '',
            'confirm_password' => '',
            'avatar' => ''
        ];
        $successMessage = $_SESSION['profile_success'] ?? '';
        unset($_SESSION['profile_success']);

        $pub = $this->getPublicSettings();
        $profilePageTitle = trim($pub['profile_page_title'] ?? '') ?: 'Hồ sơ người dùng';
        $profilePageDesc = trim($pub['profile_page_intro'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedToken = trim((string) ($_POST['csrf_token'] ?? ''));
            if (!hash_equals((string) ($_SESSION['csrf_profile'] ?? ''), $submittedToken)) {
                $successMessage = '';
                $errors['full_name'] = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang.';
                $this->view('client/users/profile', [
                    'title'           => $profilePageTitle,
                    'description'    => $profilePageDesc,
                    'csrf_token'      => $_SESSION['csrf_profile'],
                    'user'            => $currentUser,
                    'errors'          => $errors,
                    'success_message' => ''
                ]);
                return;
            }

            $action = trim($_POST['action'] ?? '');

            if ($action === 'profile_info') {
                $fullName = trim($_POST['full_name'] ?? '');
                $email    = trim($_POST['email'] ?? '');

                if ($fullName === '') {
                    $errors['full_name'] = 'Họ và tên không được để trống.';
                } elseif (strlen($fullName) > 100) {
                    $errors['full_name'] = 'Tên hiển thị tối đa 100 ký tự.';
                } elseif (!preg_match('/^[a-zA-ZÀ-ỹ\s]+$/u', $fullName)) {
                    $errors['full_name'] = 'Tên hiển thị chỉ được chứa chữ cái và khoảng cách.';
                }

                if (empty($email)) {
                    $errors['email'] = 'Vui lòng nhập email.';
                } elseif (!preg_match('/^[a-zA-Z0-9@.]+$/', $email)) {
                    $errors['email'] = 'Email chỉ được chứa chữ cái, số, @ và dấu chấm.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Email không hợp lệ.';
                } elseif ($email !== $currentUser->email && $this->userModel->findUserByEmail($email)) {
                    $errors['email'] = 'Email đã được sử dụng.';
                }

                if ($errors['full_name'] === '' && $errors['email'] === '') {
                    $updated = $this->userModel->updateProfile((int) $_SESSION['user_id'], $fullName, $email);
                    if ($updated) {
                        $this->logAudit('profile_updated', 'user', (int) $_SESSION['user_id'], [
                            'old_name'  => (string) ($currentUser->full_name ?? ''),
                            'new_name'  => $fullName,
                            'old_email' => (string) ($currentUser->email ?? ''),
                            'new_email' => $email
                        ]);
                        $_SESSION['user_name']      = $fullName;
                        $_SESSION['csrf_profile']   = bin2hex(random_bytes(32));
                        $_SESSION['profile_success'] = 'Đã cập nhật thông tin cá nhân.';
                        header('Location: ' . URLROOT . '/users/profile');
                        exit();
                    }
                }
            }

            if ($action === 'change_password') {
                $currentPassword = trim($_POST['current_password'] ?? '');
                $newPassword = trim($_POST['new_password'] ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');

                if ($currentPassword === '') {
                    $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại.';
                } elseif (!password_verify($currentPassword, $currentUser->password)) {
                    $errors['current_password'] = 'Mật khẩu hiện tại không chính xác.';
                }

                if (strlen($newPassword) < 6) {
                    $errors['new_password'] = 'Mật khẩu mới cần ít nhất 6 ký tự.';
                }
                if ($confirmPassword !== $newPassword) {
                    $errors['confirm_password'] = 'Xác nhận mật khẩu không khớp.';
                }

                if ($errors['current_password'] === '' && $errors['new_password'] === '' && $errors['confirm_password'] === '') {
                    $updated = $this->userModel->updatePassword((int) $_SESSION['user_id'], password_hash($newPassword, PASSWORD_DEFAULT));
                    if ($updated) {
                        $this->logAudit('password_changed', 'user', (int) $_SESSION['user_id'], [
                            'action' => 'self_password_change'
                        ]);
                        $_SESSION['csrf_profile']    = bin2hex(random_bytes(32));
                        $_SESSION['profile_success'] = 'Đã cập nhật mật khẩu.';
                        header('Location: ' . URLROOT . '/users/profile');
                        exit();
                    }
                }
            }

            if ($action === 'upload_avatar') {
                if (!empty($_FILES['avatar']['name'])) {
                    $uploadDir = APPROOT . '/../public/uploads/avatars/';
                    $stored = SecureUpload::storeRasterUpload(
                        $_FILES['avatar'],
                        $uploadDir,
                        'av_',
                        SecureUpload::DEFAULT_MAX_BYTES
                    );
                    if (!$stored['ok']) {
                        $errors['avatar'] = $stored['message'] ?? 'Không thể tải ảnh lên. Vui lòng thử lại.';
                    } else {
                        $oldRel = (string) ($currentUser->avatar ?? '');
                        if ($oldRel !== '') {
                            $oldPath = '';
                            if (strpos($oldRel, '/uploads/avatars/') === 0) {
                                $oldPath = APPROOT . '/../public' . $oldRel;
                            } elseif (preg_match('#^uploads/avatars/[^/]+$#i', $oldRel)) {
                                $oldPath = APPROOT . '/../public/' . $oldRel;
                            }
                            if ($oldPath !== '' && is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }

                        $avatarRelativeUrl = '/uploads/avatars/' . $stored['filename'];
                        $this->userModel->updateAvatar((int) $_SESSION['user_id'], $avatarRelativeUrl);
                        $this->logAudit('avatar_updated', 'user', (int) $_SESSION['user_id'], [
                            'avatar' => $avatarRelativeUrl
                        ]);
                        $_SESSION['user_avatar']      = $avatarRelativeUrl;
                        $_SESSION['csrf_profile']     = bin2hex(random_bytes(32));
                        $_SESSION['profile_success'] = 'Đã cập nhật ảnh đại diện.';
                        header('Location: ' . URLROOT . '/users/profile');
                        exit();
                    }
                } else {
                    $errors['avatar'] = 'Vui lòng chọn ảnh đại diện.';
                }
            }
        }

        $user = $this->userModel->getUserById((int) $_SESSION['user_id']);
        if ($user) {
            $_SESSION['user_avatar'] = $user->avatar ?? '';
        }
        $data = [
            'title'           => $profilePageTitle,
            'description'    => $profilePageDesc,
            'csrf_token'      => $_SESSION['csrf_profile'],
            'user'            => $user,
            'errors'          => $errors,
            'success_message' => $successMessage
        ];
        $this->view('client/users/profile', $data);
    }

    // Default index to avoid missing method errors
    public function index() {
        header('Location: ' . URLROOT . '/users/login');
        exit;
    }

    public function dashboard()
    {
        $this->requireAuth();
        $orderModel = $this->model('Order');
        $services = $orderModel->getUserServices((int) $_SESSION['user_id']);
        $data = ['title' => 'Dashboard cá nhân', 'services' => $services];
        $this->view('client/users/dashboard', $data);
    }

    /**
     * Hiển thị danh sách đơn hàng của tôi
     */
    public function orders() {
        $this->requireAuth();

        $orderModel = $this->model('Order');
        $orders = $orderModel->getOrdersByUserId((int) $_SESSION['user_id']);

        $data = [
            'title' => 'Đơn hàng của tôi',
            'orders' => $orders
        ];

        $this->view('client/users/orders', $data);
    }

    /**
     * Chi tiết và theo dõi trạng thái một đơn hàng cụ thể
     */
    public function order_detail($id) {
        $this->requireAuth();

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);

        // Bảo mật: Chỉ cho phép xem đơn hàng của chính mình
        if (!$order || $order->user_id != $_SESSION['user_id']) {
            die('Bạn không có quyền xem đơn hàng này!');
        }

        $items = $orderModel->getOrderItems($id);

        $data = [
            'title' => 'Theo dõi đơn hàng #' . $id,
            'order' => $order,
            'items' => $items
        ];

        $this->view('client/users/order_detail', $data);
    }
}