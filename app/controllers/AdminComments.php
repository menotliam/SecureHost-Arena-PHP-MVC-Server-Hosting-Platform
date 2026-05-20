<?php
class AdminComments extends Controller {
    private $commentModel;

    public function __construct() {
        if(!isAdmin()){
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->commentModel = $this->model('Comment');
    }

    public function index() {
        $comments = $this->commentModel->getNewsCommentsAdmin();
        $data = [
            'title' => 'Quản lý bình luận',
            'comments' => $comments
        ];
        $this->view('admin/comments/index', $data);
    }

    public function approve($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->commentModel->updateStatus($id, 'approved')){
                header('Location: ' . URLROOT . '/admin/comments');
            } else {
                die('Lỗi duyệt bình luận');
            }
        }
    }

    public function hide($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->commentModel->updateStatus($id, 'hidden')){
                header('Location: ' . URLROOT . '/admin/comments');
            } else {
                die('Lỗi ẩn bình luận');
            }
        }
    }

    public function delete($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->commentModel->delete($id)){
                header('Location: ' . URLROOT . '/admin/comments');
            } else {
                die('Lỗi xóa bình luận');
            }
        }
    }
}
