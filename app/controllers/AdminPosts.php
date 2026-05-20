<?php
  class AdminPosts extends Controller {
    private $postModel;

    public function __construct(){
      // Protect admin routes
      if(!isAdmin()){
        header('Location: ' . URLROOT . '/users/login');
        exit();
      }
      $this->postModel = $this->model('Post');
    }

    public function index(){
      $newsList = $this->postModel->getAllNewsAdmin();

      $data = [
        'title' => 'Quản lý tin tức',
        'news' => $newsList
      ];

      $this->view('admin/posts/index', $data);
    }

    public function add(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'title' => trim($_POST['title']),
                'slug' => $this->slugify(trim($_POST['title'])),
                'content' => $_POST['content'],
                'meta_keywords' => trim($_POST['meta_keywords']),
                'meta_description' => trim($_POST['meta_description']),
                'status' => $_POST['status'],
                'author_id' => $_SESSION['user_id'],
                'thumbnail' => '',
                'is_breaking' => isset($_POST['is_breaking']) ? 1 : 0,
                'breaking_until' => !empty($_POST['breaking_until']) ? $_POST['breaking_until'] : null,
                'publish_at' => !empty($_POST['publish_at']) ? $_POST['publish_at'] : null,
                'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null
            ];

            $data['seo_score'] = $this->calculateSEOScore($data);

            // Image Upload Logic
            if(!empty($_FILES['thumbnail']['name'])){
                $target_dir = APPROOT . '/../public/uploads/';
                if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $imageFileType = strtolower(pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '_news.' . $imageFileType;
                $target_file = $target_dir . $new_filename;

                if(move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
                    $data['thumbnail'] = $new_filename;
                }
            }

            if($this->postModel->addNews($data)){
                header('Location: ' . URLROOT . '/admin/posts');
            } else {
                die('Lỗi thêm tin tức');
            }
        } else {
            $categories = $this->postModel->getCategories();
            $data = [
                'title' => 'Thêm tin tức mới',
                'categories' => $categories
            ];
            $this->view('admin/posts/add', $data);
        }
    }

    // AJAX Image Upload for Dropzone
    public function uploadImage(){
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['file'])){
            $target_dir = APPROOT . '/../public/uploads/';
            if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $imageFileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '_news.' . $imageFileType;
            $target_file = $target_dir . $new_filename;

            if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                echo json_encode(['success' => true, 'filename' => $new_filename]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move file']);
            }
            exit();
        }
    }

    public function edit($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $article = $this->postModel->getNewsById($id);
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'slug' => $this->slugify(trim($_POST['title'])),
                'content' => $_POST['content'],
                'meta_keywords' => trim($_POST['meta_keywords']),
                'meta_description' => trim($_POST['meta_description']),
                'status' => $_POST['status'],
                'thumbnail' => $article->thumbnail,
                'is_breaking' => isset($_POST['is_breaking']) ? 1 : 0,
                'breaking_until' => !empty($_POST['breaking_until']) ? $_POST['breaking_until'] : null,
                'publish_at' => !empty($_POST['publish_at']) ? $_POST['publish_at'] : null,
                'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null
            ];

            $data['seo_score'] = $this->calculateSEOScore($data);

            // Image Upload Logic
            if(!empty($_FILES['thumbnail']['name'])){
                $target_dir = APPROOT . '/../public/uploads/';
                $imageFileType = strtolower(pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '_news.' . $imageFileType;
                $target_file = $target_dir . $new_filename;

                if(move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
                    $data['thumbnail'] = $new_filename;
                }
            }

            if($this->postModel->updateNews($data)){
                header('Location: ' . URLROOT . '/admin/posts');
            } else {
                die('Lỗi cập nhật tin tức');
            }
        } else {
            $article = $this->postModel->getNewsById($id);
            $categories = $this->postModel->getCategories();
            $data = [
                'title' => 'Chỉnh sửa tin tức',
                'article' => $article,
                'categories' => $categories
            ];
            $this->view('admin/posts/edit', $data);
        }
    }

    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->postModel->deleteNews($id)){
                header('Location: ' . URLROOT . '/admin/posts');
            } else {
                die('Lỗi xóa tin tức');
            }
        }
    }

    public function analytics($id){
        $article = $this->postModel->getNewsById($id);
        if(!$article){
            header('Location: ' . URLROOT . '/admin/posts');
            exit();
        }

        $analytics = $this->postModel->getAnalytics($id);

        $data = [
            'title' => 'Phân tích: ' . $article->title,
            'article' => $article,
            'analytics' => $analytics
        ];

        $this->view('admin/posts/analytics', $data);
    }

    private function calculateSEOScore($data) {
        $score = 0;
        // Title length (optimal 50-60 chars)
        $titleLen = mb_strlen($data['title']);
        if($titleLen >= 40 && $titleLen <= 70) $score += 30;
        else if($titleLen > 0) $score += 15;

        // Meta description length (optimal 150-160 chars)
        $descLen = mb_strlen($data['meta_description']);
        if($descLen >= 120 && $descLen <= 165) $score += 30;
        else if($descLen > 0) $score += 15;

        // Content length
        $contentLen = str_word_count(strip_tags($data['content']));
        if($contentLen >= 300) $score += 20;
        else if($contentLen >= 100) $score += 10;

        // Keywords in title/description
        if(!empty($data['meta_keywords'])){
            $score += 20;
        }

        return min($score, 100);
    }

    private function slugify($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $text);
        $text = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $text);
        $text = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $text);
        $text = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $text);
        $text = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $text);
        $text = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $text);
        $text = preg_replace('/(đ)/', 'd', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
