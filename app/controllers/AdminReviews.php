<?php
class AdminReviews extends Controller {
    private $reviewModel;

    public function __construct() {
        if (!isAdmin()) {
            header('Location: ' . URLROOT . '/users/login');
            exit;
        }
        $this->reviewModel = $this->model('Review');
    }

    public function index() {
        $reviews = $this->reviewModel->getAllReviews();
        $data = [
            'title' => 'Quản lý Đánh giá',
            'reviews' => $reviews
        ];
        $this->view('admin/reviews/index', $data);
    }

    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = $_POST['status'];
            
            // Thực hiện update vào CSDL
            if ($this->reviewModel->updateStatus($id, $status)) {
                header('Location: ' . URLROOT . '/adminreviews');
                exit();
            } else {
                die('Có lỗi xảy ra khi cập nhật.!');
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->reviewModel->deleteReview($id);
            header('Location: ' . URLROOT . '/adminreviews');
            exit();
        }
    }
}