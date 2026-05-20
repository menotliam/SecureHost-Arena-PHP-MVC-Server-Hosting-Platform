<?php
class AdminOrders extends Controller {
    private $orderModel;

    public function __construct() {
        if (!isAdmin()) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }
        $this->orderModel = $this->model('Order');
    }

    public function index() {
        $limit = 10;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getOrders($limit, $offset);
        $totalOrders = $this->orderModel->getTotalOrders();
        $totalPages = ceil($totalOrders / $limit);

        $data = [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];

        $this->view('admin/orders/index', $data);
    }

    // public function updateStatus($orderId) {
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         // Lấy dữ liệu gửi lên (có thể là form-data hoặc JSON raw tùy cách fetch)
    //         $inputData = json_decode(file_get_contents('php://input'), true);
    //         $status = isset($inputData['status']) ? filter_var($inputData['status'], FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    //         $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
    //         $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    //         if (!in_array($status, $valid_statuses)) {
    //             if ($isAjax) {
    //                 echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.']);
    //                 exit;
    //             }
    //             die('Trạng thái không hợp lệ.');
    //         }

    //         if ($this->orderModel->updateOrderStatus($orderId, $status)) {
    //             if ($isAjax) {
    //                 echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'new_status' => $status]);
    //                 exit;
    //             }
    //             header('Location: ' . URLROOT . '/adminorders');
    //         } else {
    //             if ($isAjax) {
    //                 echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL.']);
    //                 exit;
    //             }
    //             die('Có lỗi xảy ra khi cập nhật trạng thái.');
    //         }
    //     }
    // }
    public function updateStatus($orderId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Read input (support form-data POST or raw JSON body)
        $raw = file_get_contents('php://input');
        $inputData = [];
        $contentType = '';
        if (!empty($_SERVER['CONTENT_TYPE'])) {
            $contentType = strtolower($_SERVER['CONTENT_TYPE']);
        } elseif (!empty($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = strtolower($_SERVER['HTTP_CONTENT_TYPE']);
        }

        if (stripos($contentType, 'application/json') !== false) {
            $inputData = json_decode($raw, true) ?: [];
        } else {
            $inputData = $_POST;
            if (empty($inputData) && $raw) {
                $maybeJson = json_decode($raw, true);
                if (is_array($maybeJson)) {
                    $inputData = $maybeJson;
                }
            }
        }

        // If CSRF token provided in JSON body, populate $_POST/$_SERVER so verifyCsrf can find it
        if (!empty($inputData['csrf_token']) && empty($_POST['csrf_token'])) {
            $_POST['csrf_token'] = $inputData['csrf_token'];
        }
        if (!empty($inputData['csrf_token']) && empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $_SERVER['HTTP_X_CSRF_TOKEN'] = $inputData['csrf_token'];
        }

        // 1. Check CSRF
        if (!$this->verifyCsrf('csrf_admin')) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.']);
                exit;
            }
            die('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.');
        }

        $status = isset($inputData['status']) ? filter_var($inputData['status'], FILTER_SANITIZE_STRING) : filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses, true)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.']);
                exit;
            }
            die('Trạng thái không hợp lệ.');
        }

        // 2. If admin marks completed -> provision services
        if ($status === 'completed') {
            $currentOrder = $this->orderModel->getOrderById($orderId);
            if ($currentOrder && $currentOrder->status !== 'completed') {
                $this->orderModel->provisionServices($orderId);
            }
        }

        // 3. Update DB and respond
        if ($this->orderModel->updateOrderStatus($orderId, $status)) {
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'new_status' => $status]);
                exit;
            }

            header('Location: ' . URLROOT . '/admin/orders');
            exit();
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL.']);
                exit;
            }
            die('Có lỗi xảy ra khi cập nhật trạng thái.');
        }
    }
    public function show($id) {
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            die('Đơn hàng không tồn tại!');
        }
        
        // Lấy danh sách sản phẩm trong đơn hàng này
        $items = $this->orderModel->getOrderItems($id);
        
        $data = [
            'order' => $order,
            'items' => $items
        ];
        
        $this->view('admin/orders/show', $data);
    }
}