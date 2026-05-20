<?php
class Cart extends Controller {
    private $orderModel;
    private $productModel;

    public function __construct() {
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $cartItems = [];
        $totalAmount = 0;

        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $product = $this->productModel->getProductById($productId);
                if ($product) {
                    $product->quantity = $quantity;
                    $product->subtotal = $product->price * $quantity;
                    $totalAmount += $product->subtotal;
                    $cartItems[] = $product;
                }
            }
        }

        $data = [
            'title' => 'Giỏ hàng của bạn - Cloud Arena',
            'description' => 'Kiểm tra và quản lý các gói Server đang có trong giỏ hàng Cloud Arena của bạn trước khi tiến hành thanh toán.',
            'cartItems' => $cartItems,
            'totalAmount' => $totalAmount
        ];

        $this->view('client/cart/index', $data);
    }

    public function add($productId = null) {
        // 1. Kiểm tra ID truyền vào
        if (!$productId) {
            header('Location: ' . URLROOT . '/products');
            exit;
        }

        // 2. Kiểm tra sản phẩm có thực sự tồn tại trong Database không
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_GET['ajax']) && $_GET['ajax'] == '1');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã bị ẩn!']);
                exit;
            }
            header('Location: ' . URLROOT . '/products?error=not_found');
            exit;
        }

        $acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_GET['ajax']) && $_GET['ajax'] == '1')
            || (strpos($acceptHeader, 'application/json') !== false);
        
        $quantity = 1;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rawInput = file_get_contents('php://input');
            $inputData = json_decode($rawInput, true);
            if (is_array($inputData) && isset($inputData['quantity'])) {
                $quantity = (int)$inputData['quantity'];
            } else {
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            }
        }
        
        if ($quantity <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
                exit;
            }
            header('Location: ' . URLROOT . '/products/show/' . $productId . '?error=invalid_quantity');
            exit; 
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        $cartCount = array_sum($_SESSION['cart']);

        if ($isAjax) {
            header('Content-Type: application/json');
            // Trả về kèm tên sản phẩm để thông báo thân thiện hơn
            echo json_encode([
                'success' => true, 
                'message' => 'Đã thêm ' . $product->name . ' vào giỏ hàng!', 
                'cartCount' => $cartCount
            ]);
            exit;
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

    public function remove($productId) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        
        if ($isAjax) {
            // Recalculate totals
            $cartCount = empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);
            $totalAmount = 0;
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $pId => $qty) {
                    $product = $this->productModel->getProductById($pId);
                    if ($product) {
                        $totalAmount += $product->price * $qty;
                    }
                }
            }
            echo json_encode([
                'success' => true, 
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
                'cartCount' => $cartCount,
                'totalAmount' => number_format($totalAmount, 0, ',', '.') . 'đ',
                'isEmpty' => empty($_SESSION['cart'])
            ]);
            exit;
        }

        header('Location: ' . URLROOT . '/cart');
        exit;
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
            $userId = $_SESSION['user_id'];
            
            $address = isset($_POST['address']) ? filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) : '';
            $phone = isset($_POST['phone']) ? filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) : '';

            if (empty($address) || empty($phone)) {
                header('Location: ' . URLROOT . '/cart?error=missing_info');
                exit;
            }

            $totalAmount = 0;
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $product = $this->productModel->getProductById($productId);
                if ($product) {
                    $totalAmount += $product->price * $quantity;
                }
            }
            
            $orderId = $this->orderModel->createOrder($userId, $_SESSION['cart'], $totalAmount, $address, $phone);
            
            if ($orderId) {
                unset($_SESSION['cart']); 
                header('Location: ' . URLROOT . '/pages/success');
            } else {
                die('Có lỗi xảy ra khi đặt hàng.');
            }
        } else {
            $data = [
                'title' => 'Thanh toán đơn hàng - Cloud Arena',
                'description' => 'Tiến hành điền thông tin và thanh toán an toàn các gói dịch vụ Game Server tại Cloud Arena.'
            ];
            $this->view('client/cart/checkout', $data);
        }
    }
    public function update() {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($isAjax && isset($_POST['productId']) && isset($_POST['quantity'])) {
                $productId = (int)$_POST['productId'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId] = $quantity;
                } else {
                    unset($_SESSION['cart'][$productId]);
                }
                
                // Calculate new totals
                $itemSubtotal = 0;
                $totalAmount = 0;
                
                // Tránh lỗi khi mảng rỗng
                $cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                
                if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $pId => $qty) {
                        $product = $this->productModel->getProductById($pId);
                        if ($product) {
                            $sub = $product->price * $qty;
                            $totalAmount += $sub;
                            if ($pId == $productId) {
                                $itemSubtotal = $sub;
                            }
                        }
                    }
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'itemSubtotal' => number_format($itemSubtotal, 0, ',', '.') . 'đ',
                    'totalAmount' => number_format($totalAmount, 0, ',', '.') . 'đ',
                    'cartCount' => $cartCount
                ]);
                exit;
            } elseif (isset($_POST['quantities'])) {
                foreach ($_POST['quantities'] as $productId => $quantity) {
                    if ((int)$quantity > 0) {
                        // Cập nhật lại số lượng mới
                        $_SESSION['cart'][$productId] = (int)$quantity;
                    } else {
                        // Nếu người dùng nhập số lượng là 0 thì xoá khỏi giỏ hàng
                        unset($_SESSION['cart'][$productId]);
                    }
                }
            }
        }
        header('Location: ' . URLROOT . '/cart');
        exit;
    }
}