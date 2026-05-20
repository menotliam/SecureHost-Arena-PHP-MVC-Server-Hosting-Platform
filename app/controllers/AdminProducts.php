<?php
class AdminProducts extends Controller {
    private $productModel;

    public function __construct() {
        if (!isAdmin()) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $limit = 10; 
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

        $products = $this->productModel->getProducts($limit, $offset, $keyword);
        $totalProducts = $this->productModel->getTotalProducts($keyword);
        $totalPages = ceil($totalProducts / $limit);

        $data = [
            'products' => $products,
            'keyword' => $keyword,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];

        $this->view('admin/products/index', $data);
    }

    public function add() {
        $data = [
            'categories' => $this->productModel->getCategories(),
            'name' => '', 'slug' => '', 'price' => '', 'ram_mb' => '', 'cpu_cores' => '', 'disk_gb' => '', 'description' => '', 'image_url' => '',
            'name_err' => '', 'price_err' => '', 'image_err' => '',
            'ram_mb_err' => '', 'cpu_err' => '', 'disk_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Flag check AJAX
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            $data['name'] = trim($_POST['name']);
            $data['category_id'] = trim($_POST['category_id']);
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
            $data['price'] = trim($_POST['price']);
            $data['ram_mb'] = trim($_POST['ram_mb']);
            $data['cpu_cores'] = trim($_POST['cpu_cores']);
            $data['disk_gb'] = trim($_POST['disk_gb']);
            $data['description'] = trim($_POST['description']);
            $data['status'] = trim($_POST['status']);

            if (empty($data['name'])) { $data['name_err'] = 'Vui lòng nhập tên sản phẩm'; }
            if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) { $data['price_err'] = 'Giá sản phẩm không hợp lệ'; }
            if (empty($data['ram_mb']) || !is_numeric($data['ram_mb']) || $data['ram_mb'] < 0) { $data['ram_mb_err'] = 'RAM không hợp lệ'; }
            if (empty($data['cpu_cores']) || !is_numeric($data['cpu_cores']) || $data['cpu_cores'] < 0) { $data['cpu_err'] = 'CPU không hợp lệ'; }
            if (empty($data['disk_gb']) || !is_numeric($data['disk_gb']) || $data['disk_gb'] < 0) { $data['disk_err'] = 'Ổ cứng không hợp lệ'; }

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $originalName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
                $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($originalName));
                $fileName = time() . '_' . $safeBase;

                $uploadFileDir = rtrim(dirname(APPROOT), "\\/") . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
                if (!is_dir($uploadFileDir)) {
                    if (!mkdir($uploadFileDir, 0755, true) && !is_dir($uploadFileDir)) {
                        $data['image_err'] = 'Không thể tạo thư mục lưu trữ ảnh trên server.';
                    }
                }

                $dest_path = $uploadFileDir . $fileName;

                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
                $fileExtension = strtolower(pathinfo($safeBase, PATHINFO_EXTENSION));

                if (empty($data['image_err'])) {
                    if (in_array($fileExtension, $allowedfileExtensions)) {
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            // store relative URL path used elsewhere in app
                            $data['image_url'] = 'media/' . $fileName;
                        } else {
                            $data['image_err'] = 'Có lỗi khi di chuyển file upload tới thư mục lưu trữ.';
                        }
                    } else {
                        $data['image_err'] = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP).';
                    }
                }
            } else {
                // Keep existing image on edit if none uploaded; for add, require image
                if ($data['image_url'] === '') {
                    $data['image_err'] = 'Vui lòng chọn hình ảnh sản phẩm.';
                }
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['image_err']) && empty($data['ram_mb_err']) && empty($data['cpu_err']) && empty($data['disk_err'])) {
                if ($this->productModel->addProduct($data)) {
                    if ($isAjax) {
                        echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!']);
                        exit;
                    }
                    header('Location: ' . URLROOT . '/admin/products');
                    exit;
                } else {
                    if ($isAjax) {
                        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu vào database.']);
                        exit;
                    }
                    die('Có lỗi xảy ra khi lưu vào database.');
                }
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'errors' => [
                        'name_err' => $data['name_err'],
                        'price_err' => $data['price_err'],
                        'ram_mb_err' => $data['ram_mb_err'],
                        'cpu_err' => $data['cpu_err'],
                        'disk_err' => $data['disk_err'],
                        'image_err' => $data['image_err']
                    ]]);
                    exit;
                }
            }
        }
        $this->view('admin/products/add', $data); 
    }

    public function edit($id) {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die('Sản phẩm không tồn tại.');
        }

        $data = [
            'id' => $id,
            'categories' => $this->productModel->getCategories(),
            'name' => $product->name, 'category_id' => $product->category_id, 'slug' => $product->slug, 'price' => $product->price, 'ram_mb' => $product->ram_mb, 'cpu_cores' => $product->cpu_cores, 'disk_gb' => $product->disk_gb, 'description' => $product->description, 'image_url' => $product->image_url, 'status' => $product->status,
            'name_err' => '', 'price_err' => '', 'image_err' => '',
            'ram_mb_err' => '', 'cpu_err' => '', 'disk_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Flag check AJAX
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            $data['name'] = trim($_POST['name']);
            $data['category_id'] = trim($_POST['category_id']);
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
            $data['price'] = trim($_POST['price']);
            $data['ram_mb'] = trim($_POST['ram_mb']);
            $data['cpu_cores'] = trim($_POST['cpu_cores']);
            $data['disk_gb'] = trim($_POST['disk_gb']);
            $data['description'] = trim($_POST['description']);
            $data['status'] = trim($_POST['status']);

            if (empty($data['name'])) { $data['name_err'] = 'Vui lòng nhập tên sản phẩm'; }
            if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) { $data['price_err'] = 'Giá sản phẩm không hợp lệ'; }
            if (empty($data['ram_mb']) || !is_numeric($data['ram_mb']) || $data['ram_mb'] < 0) { $data['ram_mb_err'] = 'RAM không hợp lệ'; }
            if (empty($data['cpu_cores']) || !is_numeric($data['cpu_cores']) || $data['cpu_cores'] < 0) { $data['cpu_err'] = 'CPU không hợp lệ'; }
            if (empty($data['disk_gb']) || !is_numeric($data['disk_gb']) || $data['disk_gb'] < 0) { $data['disk_err'] = 'Ổ cứng không hợp lệ'; }

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image']['tmp_name'];
                    $originalName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
                    $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($originalName));
                    $fileName = time() . '_' . $safeBase;

                    $uploadFileDir = rtrim(dirname(APPROOT), "\\/") . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
                    if (!is_dir($uploadFileDir)) {
                        if (!mkdir($uploadFileDir, 0755, true) && !is_dir($uploadFileDir)) {
                            $data['image_err'] = 'Không thể tạo thư mục lưu trữ ảnh trên server.';
                        }
                    }

                    $dest_path = $uploadFileDir . $fileName;

                    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
                    $fileExtension = strtolower(pathinfo($safeBase, PATHINFO_EXTENSION));

                    if (empty($data['image_err'])) {
                        if (in_array($fileExtension, $allowedfileExtensions)) {
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $data['image_url'] = 'media/' . $fileName;
                            } else {
                                $data['image_err'] = 'Có lỗi khi di chuyển file upload tới thư mục lưu trữ.';
                            }
                        } else {
                            $data['image_err'] = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP).';
                        }
                    }
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['image_err']) && empty($data['ram_mb_err']) && empty($data['cpu_err']) && empty($data['disk_err'])) {
                if ($this->productModel->updateProduct($data)) {
                    if ($isAjax) {
                        echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!']);
                        exit;
                    }
                    header('Location: ' . URLROOT . '/admin/products');
                    exit;
                } else {
                    if ($isAjax) {
                        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu vào database.']);
                        exit;
                    }
                    die('Có lỗi xảy ra khi lưu vào database.');
                }
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'errors' => [
                        'name_err' => $data['name_err'],
                        'price_err' => $data['price_err'],
                        'ram_mb_err' => $data['ram_mb_err'],
                        'cpu_err' => $data['cpu_err'],
                        'disk_err' => $data['disk_err'],
                        'image_err' => $data['image_err']
                    ]]);
                    exit;
                }
            }
        }
        
        $this->view('admin/products/edit', $data); 
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($this->productModel->deleteProduct($id)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm thành công!']);
                    exit;
                }
                header('Location: ' . URLROOT . '/admin/products');
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa sản phẩm.']);
                    exit;
                }
                die('Có lỗi xảy ra khi xóa sản phẩm');
            }
        } else {
            header('Location: ' . URLROOT . '/adminproducts');
        }
    }
}