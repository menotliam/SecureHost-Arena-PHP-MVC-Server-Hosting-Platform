<?php
class Pages extends Controller {
    private $contactModel;
    private $adminNotificationModel;
    private $productModel;
    private $commentModel;
    private $userModel;
    private $orderModel;

    public function __construct() {
        $this->contactModel = $this->model('Contact');
        $this->adminNotificationModel = $this->model('AdminNotification');
        $this->productModel = $this->model('Product');
        $this->commentModel = $this->model('Comment');
        $this->userModel = $this->model('User');
        $this->orderModel = $this->model('Order');
    }

    public function index() {
        $settings = $this->getPublicSettings();
        $featuredProducts = [];
        $featuredReview = null;

        $idsRaw = trim($settings['home_product_ids'] ?? '');
        if ($idsRaw !== '') {
            $idList = array_values(array_filter(array_map('intval', explode(',', $idsRaw)), function ($n) {
                return (int) $n > 0;
            }));
            if (!empty($idList)) {
                try {
                    $featuredProducts = $this->productModel->getActiveProductsByIdsOrdered($idList);
                } catch (Throwable $error) {
                    $featuredProducts = [];
                }
            }
        }
        if (empty($featuredProducts)) {
            try {
                $featuredProducts = $this->productModel->getHomepagePackages(4);
            } catch (Throwable $error) {
                $featuredProducts = [];
            }
        }

        $reviewKey = trim($settings['home_review_key'] ?? '');
        if ($reviewKey !== '' && preg_match('/^[1-9][0-9]*:[1-9][0-9]*$/', $reviewKey)) {
            $rkParts = explode(':', $reviewKey, 2);
            try {
                $featuredReview = $this->commentModel->getApprovedProductReviewByKey((int) $rkParts[0], (int) $rkParts[1]);
            } catch (Throwable $error) {
                $featuredReview = null;
            }
        }

        if (!$featuredReview) {
            try {
                $featuredReview = $this->commentModel->getLatestFiveStarProductReview();
            } catch (Throwable $error) {
                $featuredReview = null;
            }
        }

        $data = [
            'title' => 'Trang chủ',
            'description' => 'Thuê máy chủ game, gói NVMe, CPU mạnh và băng thông ổn định — ' . SITENAME . '.',
            'featured_products' => $featuredProducts,
            'featured_review' => $featuredReview
        ];
        // Expose public settings and homepage flag to views (header/footer rely on these)
        $data['public_settings'] = $settings;
        $data['isHomePage'] = true;
        $this->view('client/pages/index', $data);
    }

    public function about() {
        $aboutModel = $this->model('About');
        $about = $aboutModel->get();
        $data = ['title' => 'Giới thiệu', 'about' => $about];
        $this->view('client/about', $data);
    }

    public function contact() {
        $rawUrl = isset($_GET['url']) ? trim((string) $_GET['url'], '/') : '';
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && strcasecmp($rawUrl, 'pages/contact') === 0) {
            header('Location: ' . URLROOT . '/contact', true, 301);
            exit();
        }

        $isLoggedIn = isset($_SESSION['user_id']);
        $currentUser = null;

        if ($isLoggedIn) {
            $currentUser = $this->userModel->getUserById((int) $_SESSION['user_id']);
            if (!$currentUser) {
                session_destroy();
                header('Location: ' . URLROOT . '/users/login');
                exit();
            }
        }
        if (empty($_SESSION['csrf_contact'])) {
            $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
        }

        $pendingOrders = [];
        if ($isLoggedIn) {
            try {
                $pendingOrders = $this->orderModel->getPendingOrdersForUser((int) $_SESSION['user_id']);
            } catch (Throwable $error) {
                $pendingOrders = [];
            }
        }

        $publicSettings = $this->getPublicSettings();
        $ticketCategories = $this->contactModel->getSupportTicketCategories();
        $contactTitle = trim((string) ($publicSettings['contact_page_title'] ?? ''));
        $contactDesc = trim((string) ($publicSettings['contact_page_intro'] ?? ''));
        if ($contactTitle === '') {
            $contactTitle = 'Liên hệ';
        }
        if ($contactDesc === '') {
            $contactDesc = 'Gửi ticket hỗ trợ — ' . SITENAME . '.';
        }

        $data = [
            'title' => $contactTitle,
            'description' => $contactDesc,
            'is_logged_in' => $isLoggedIn,
            'csrf_token' => $_SESSION['csrf_contact'],
            'support_ticket_categories' => $ticketCategories,
            'pending_orders' => $pendingOrders,
            'contact_support_assets' => true,
            'form' => [
                'name' => $isLoggedIn ? trim((string) ($currentUser->full_name ?: $currentUser->username)) : '',
                'email' => $isLoggedIn ? trim((string) $currentUser->email) : '',
                'ticket_category' => 'bugs_technical',
                'order_id' => '',
                'banned_username' => '',
                'message' => '',
                'website' => '',
            ],
            'errors' => [],
            'success_message' => '',
            'contact_initial_step' => 'gate',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['contact_initial_step'] = 'main';
        } elseif (!empty($_SESSION['contact_success'])) {
            $data['contact_initial_step'] = 'main';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            $submittedToken = trim((string) ($_POST['csrf_token'] ?? ''));
            if (!hash_equals((string) ($_SESSION['csrf_contact'] ?? ''), $submittedToken)) {
                $data['errors']['general'] = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
                $this->view('client/contact', $data);
                return;
            }

            // Honeypot – silently succeed if a bot filled the hidden field
            if (trim((string) ($_POST['website'] ?? '')) !== '') {
                $_SESSION['contact_success'] = 'Gửi ticket thành công. Kỹ sư hỗ trợ sẽ phản hồi sớm nhất.';
                header('Location: ' . URLROOT . '/contact');
                exit();
            }

            // Rate limiting: 60 seconds between submissions per session
            if ((time() - (int) ($_SESSION['contact_last_submit'] ?? 0)) < 60) {
                $data['errors']['general'] = 'Bạn vừa gửi liên hệ. Vui lòng đợi ít nhất 60 giây trước khi gửi tiếp.';
                $this->view('client/contact', $data);
                return;
            }

            $catRaw = trim((string) ($_POST['ticket_category'] ?? ''));
            $data['form']['ticket_category'] = array_key_exists($catRaw, $ticketCategories) ? $catRaw : 'others';
            $data['form']['message'] = trim((string) ($_POST['message'] ?? ''));
            $data['form']['website'] = trim((string) ($_POST['website'] ?? ''));
            $data['form']['order_id'] = trim((string) ($_POST['order_id'] ?? ''));
            $data['form']['banned_username'] = trim((string) ($_POST['banned_username'] ?? ''));
            $prevPasswordPlain = (string) ($_POST['previous_password'] ?? '');

            if ($isLoggedIn) {
                $data['form']['name'] = trim((string) ($currentUser->full_name ?: $currentUser->username));
                $data['form']['email'] = trim((string) $currentUser->email);
            } else {
                $data['form']['name'] = trim((string) ($_POST['name'] ?? ''));
                $data['form']['email'] = trim((string) ($_POST['email'] ?? ''));
            }

            if ($isLoggedIn) {
                if ($data['form']['name'] === '') {
                    $data['errors']['general'] = 'Tài khoản thiếu thông tin họ tên. Vui lòng cập nhật hồ sơ.';
                } elseif (!filter_var($data['form']['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['errors']['general'] = 'Email tài khoản không hợp lệ. Vui lòng cập nhật hồ sơ.';
                }
            } else {
                if ($data['form']['name'] === '') {
                    $data['errors']['name'] = 'Vui lòng nhập họ tên.';
                } elseif (strlen($data['form']['name']) > 100) {
                    $data['errors']['name'] = 'Họ tên tối đa 100 ký tự.';
                }

                if ($data['form']['email'] === '') {
                    $data['errors']['email'] = 'Vui lòng nhập email.';
                } elseif (!filter_var($data['form']['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['errors']['email'] = 'Email không đúng định dạng.';
                } elseif (strlen($data['form']['email']) > 100) {
                    $data['errors']['email'] = 'Email tối đa 100 ký tự.';
                }
            }

            if ($data['form']['ticket_category'] === 'purchase_issue' && !$isLoggedIn) {
                $data['errors']['ticket_category'] = 'Vấn đề đơn hàng yêu cầu đăng nhập.';
            }

            $orderIdInt = (int) $data['form']['order_id'];
            $verifiedOrder = null;
            if ($data['form']['ticket_category'] === 'purchase_issue') {
                if (!$isLoggedIn) {
                    // đã báo lỗi ở trên
                } elseif ($orderIdInt <= 0) {
                    $data['errors']['order_id'] = 'Vui lòng chọn đơn hàng liên quan.';
                } else {
                    $verifiedOrder = $this->orderModel->getPendingOrderByIdForUser($orderIdInt, (int) $_SESSION['user_id']);
                    if (!$verifiedOrder) {
                        $data['errors']['order_id'] = 'Đơn hàng không hợp lệ hoặc không còn trạng thái chờ xử lý.';
                    }
                }
            }

            if ($data['form']['ticket_category'] === 'banned') {
                $bu = $data['form']['banned_username'];
                if ($bu === '') {
                    $data['errors']['banned_username'] = 'Vui lòng nhập tên đăng nhập (username) cần hỗ trợ.';
                } elseif (strlen($bu) > 50 || !preg_match('/^[a-zA-Z0-9._-]+$/', $bu)) {
                    $data['errors']['banned_username'] = 'Username không hợp lệ (tối đa 50 ký tự, chỉ chữ, số, . _ -).';
                }
            }

            $previousPasswordBcrypt = null;
            if ($data['form']['ticket_category'] === 'forgot_password' && $prevPasswordPlain !== '') {
                if (strlen($prevPasswordPlain) < 6) {
                    $data['errors']['previous_password'] = 'Mật khẩu trước đó tối thiểu 6 ký tự (nếu nhập).';
                } elseif (strlen($prevPasswordPlain) > 128) {
                    $data['errors']['previous_password'] = 'Mật khẩu trước đó tối đa 128 ký tự.';
                } else {
                    $previousPasswordBcrypt = password_hash($prevPasswordPlain, PASSWORD_DEFAULT);
                }
            }

            if ($data['form']['message'] === '') {
                $data['errors']['message'] = 'Vui lòng nhập nội dung.';
            } elseif (strlen($data['form']['message']) < 10) {
                $data['errors']['message'] = 'Nội dung tối thiểu 10 ký tự.';
            } elseif (strlen($data['form']['message']) > 5000) {
                $data['errors']['message'] = 'Nội dung tối đa 5000 ký tự.';
            }

            $categoryLabel = $ticketCategories[$data['form']['ticket_category']] ?? 'Khác';
            $pfx = Contact::supportTicketSubjectPrefix();
            $slug = $data['form']['ticket_category'];
            $subjectTail = $categoryLabel;
            if ($slug === 'purchase_issue' && $verifiedOrder) {
                $subjectTail = $categoryLabel . ' — Đơn #' . (int) $verifiedOrder->id;
            }
            if ($slug === 'banned' && empty($data['errors']['banned_username'])) {
                $buBanned = trim((string) ($data['form']['banned_username'] ?? ''));
                if ($buBanned !== '') {
                    $subjectTail = $categoryLabel . ' — @' . $buBanned;
                }
            }
            $subjectLine = $pfx . $slug . '|' . $subjectTail;
            if (strlen($subjectLine) > 255) {
                $subjectLine = substr($subjectLine, 0, 252) . '...';
            }

            $plainMessage = $data['form']['message'];

            if (empty($data['errors'])) {
                $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : $this->contactModel->getOrCreateGuestUserId();
                if ($userId > 0) {
                    $contactPayload = [
                        'user_id' => $userId,
                        'name' => $data['form']['name'],
                        'email' => $data['form']['email'],
                        'subject' => $subjectLine,
                        'message' => $plainMessage,
                    ];
                    if ($previousPasswordBcrypt !== null) {
                        $contactPayload['previous_password_bcrypt'] = $previousPasswordBcrypt;
                    }
                    $created = $this->contactModel->createContact($contactPayload);

                    if ($created) {
                        try {
                            $createdAt = trim((string) ($created['created_at'] ?? ''));
                            if ($createdAt !== '') {
                                $this->adminNotificationModel->createTicketCreatedNotification([
                                    'user_id' => (int) ($created['user_id'] ?? 0),
                                    'contact_id' => (int) ($created['contact_id'] ?? 0),
                                    'name' => $data['form']['name'],
                                    'email' => $data['form']['email'],
                                    'subject' => $subjectLine,
                                    'category_label' => $categoryLabel,
                                    'created_at' => $createdAt
                                ]);
                            }
                        } catch (Throwable $error) {
                            // Keep contact flow successful even if notification sync fails.
                        }
                        $_SESSION['contact_success'] = 'Gửi ticket thành công. Kỹ sư hỗ trợ sẽ phản hồi sớm nhất.';
                        $_SESSION['contact_last_submit'] = time();
                        $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
                        header('Location: ' . URLROOT . '/contact');
                        exit();
                    }
                }

                $data['errors']['general'] = 'Không thể gửi liên hệ lúc này. Vui lòng thử lại.';
            }
        } elseif (!empty($_SESSION['contact_success'])) {
            $data['success_message'] = $_SESSION['contact_success'];
            unset($_SESSION['contact_success']);
        }

        $this->view('client/contact', $data);
    }

    public function faq() {
        $faqModel = $this->model('Faq');
        $perPage = 8;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $category = isset($_GET['category']) ? trim($_GET['category']) : null;
        $q = isset($_GET['q']) ? trim($_GET['q']) : null;
        $cats = $faqModel->getCategories();
        $faqs = [];
        $paginationHtml = '';
        
        if($q){
            $total = $faqModel->countSearchActive($q, $category);
            require_once APPROOT . '/helpers/Pagination.php';
            $base = URLROOT . '/pages/faq?q=' . urlencode($q);
            if($category) $base .= '&category=' . urlencode($category);
            $pagination = new Pagination($total, $perPage, $page, $base);
            $faqs = $faqModel->searchActive($pagination->getLimit(), $pagination->getOffset(), $q, $category);
            $paginationHtml = $pagination->createLinks();
        } elseif($category){
            $total = $faqModel->countActive($category);
            require_once APPROOT . '/helpers/Pagination.php';
            $base = URLROOT . '/pages/faq?category=' . urlencode($category);
            $pagination = new Pagination($total, $perPage, $page, $base);
            $faqs = $faqModel->getPageActive($pagination->getLimit(), $pagination->getOffset(), $category);
            $paginationHtml = $pagination->createLinks();
        } else {
            // General FAQ list if no search or category
            $total = $faqModel->countActive();
            require_once APPROOT . '/helpers/Pagination.php';
            $pagination = new Pagination($total, $perPage, $page, URLROOT . '/pages/faq');
            $faqs = $faqModel->getPageActive($pagination->getLimit(), $pagination->getOffset());
            $paginationHtml = $pagination->createLinks();
        }
        
        $data = [
            'title' => 'Hỏi đáp',
            'faqs' => $faqs,
            'pagination' => $paginationHtml,
            'categories' => $cats,
            'current_category' => $category
        ];
        $this->view('client/faq', $data);
    }

    // AJAX: receive a FAQ/chat message from client
    public function faqMessage(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            return;
        }
        $faqModel = $this->model('Faq');
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : null;
        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : null;
        $category = isset($_POST['category']) ? trim((string)$_POST['category']) : null;
        $message = isset($_POST['message']) ? trim((string)$_POST['message']) : '';
        $page_url = isset($_POST['page_url']) ? trim((string)$_POST['page_url']) : null;

        if($message === ''){
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Nội dung trống']);
            return;
        }

        $payload = ['name' => $name, 'email' => $email, 'category' => $category, 'message' => $message, 'page_url' => $page_url, 'status' => 'new'];
        $ok = $faqModel->createMessage($payload);
        header('Content-Type: application/json; charset=utf-8');
        if($ok) echo json_encode(['success' => true]); else echo json_encode(['success' => false, 'error' => 'Không thể lưu tin nhắn']);
    }

    // AJAX: fetch message history by email or page_url
    public function faqMessages(){
        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            return;
        }
        $faqModel = $this->model('Faq');
        $email = isset($_GET['email']) ? trim((string)$_GET['email']) : null;
        $page_url = isset($_GET['page_url']) ? trim((string)$_GET['page_url']) : null;
        if(!$email && !$page_url){
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            return;
        }
        $msgs = $faqModel->getConversation($email ?: null, $page_url ?: null, 200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'messages' => $msgs]);
    }
}
