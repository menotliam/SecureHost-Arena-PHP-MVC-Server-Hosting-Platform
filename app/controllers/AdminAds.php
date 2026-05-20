<?php
class AdminAds extends Controller {
    private $adModel;

    public function __construct() {
        if (!isAdmin()) {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->adModel = $this->model('AdModel');
    }

    public function index() {
        $ads = $this->adModel->getAllAds();
        $data = [
            'title' => 'Quản lý Quảng cáo',
            'ads' => $ads
        ];
        $this->view('admin/ads/index', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'title' => trim($_POST['title']),
                'link_url' => trim($_POST['link_url']),
                'position' => $_POST['position'],
                'status' => $_POST['status'],
                'start_at' => $_POST['start_at'],
                'end_at' => $_POST['end_at'],
                'image_url' => ''
            ];

            // Image Upload
            if (!empty($_FILES['image']['name'])) {
                $target_dir = APPROOT . '/../public/uploads/ads/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '_ad.' . $imageFileType;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $data['image_url'] = '/uploads/ads/' . $new_filename;
                }
            }

            if ($this->adModel->addAd($data)) {
                header('Location: ' . URLROOT . '/admin/ads');
            } else {
                die('Lỗi thêm quảng cáo');
            }
        } else {
            $data = ['title' => 'Thêm Quảng cáo mới'];
            $this->view('admin/ads/add', $data);
        }
    }

    public function edit($id) {
        $ad = $this->adModel->getAdById($id);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'link_url' => trim($_POST['link_url']),
                'position' => $_POST['position'],
                'status' => $_POST['status'],
                'start_at' => $_POST['start_at'],
                'end_at' => $_POST['end_at'],
                'image_url' => $ad->image_url
            ];

            if (!empty($_FILES['image']['name'])) {
                $target_dir = APPROOT . '/../public/uploads/ads/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '_ad.' . $imageFileType;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $data['image_url'] = '/uploads/ads/' . $new_filename;
                }
            }

            if ($this->adModel->updateAd($data)) {
                header('Location: ' . URLROOT . '/admin/ads');
            } else {
                die('Lỗi cập nhật quảng cáo');
            }
        } else {
            $data = [
                'title' => 'Chỉnh sửa Quảng cáo',
                'ad' => $ad
            ];
            $this->view('admin/ads/edit', $data);
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->adModel->deleteAd($id)) {
                header('Location: ' . URLROOT . '/admin/ads');
            } else {
                die('Lỗi xóa quảng cáo');
            }
        }
    }
}
