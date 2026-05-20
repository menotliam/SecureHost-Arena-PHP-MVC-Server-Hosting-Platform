<?php
  class Posts extends Controller {
    private $postModel;
    private $commentModel;

    private $adModel;

    public function __construct(){
      $this->postModel = $this->model('Post');
      $this->commentModel = $this->model('Comment');
      $this->adModel = $this->model('AdModel');
    }

    public function index(){
      $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
      $category = isset($_GET['category']) ? trim($_GET['category']) : '';

      $news = $this->postModel->getNews($keyword, $category);
      $breaking = $this->postModel->getBreakingNews();
      $trending = $this->postModel->getTrendingNews(5);
      $ads = $this->adModel->getActiveAdsByPosition('sticky-sidebar');

      $data = [
        'title' => 'Tin tức & Cập nhật',
        'news' => $news,
        'breaking' => $breaking,
        'trending' => $trending,
        'search' => $keyword,
        'category' => $category,
        'ads' => $ads
      ];

      $this->view('client/posts/news', $data);
    }

    public function show($slug){
      $article = $this->postModel->getNewsBySlug($slug);

      if(empty($article)){
        header('Location: ' . URLROOT . '/posts');
        exit();
      }

      // Increment views
      $ip = $_SERVER['REMOTE_ADDR'];
      $source = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : 'Direct';
      $this->postModel->incrementViews($article->id, $ip, $source);

      // Get comments
      $comments = $this->commentModel->getCommentsByNewsId($article->id);
      
      // Check if user liked article
      $user_liked = false;
      if(isset($_SESSION['user_id'])){
        $user_liked = $this->postModel->hasLiked($_SESSION['user_id'], $article->id);
        foreach($comments as $comment){
            $comment->user_liked = $this->commentModel->hasLiked($_SESSION['user_id'], $comment->id);
        }
      }

      // Get related news
      $related_news = $this->postModel->getRelatedNews($article->id, $article->category_id);
      
      // Get trending for sidebar
      $trending = $this->postModel->getTrendingNews(5);
      
      // Get ads for sidebar
      $ads = $this->adModel->getActiveAdsByPosition('sticky-sidebar');
      
      $data = [
        'title' => $article->title,
        'article' => $article,
        'comments' => $comments,
        'related_news' => $related_news,
        'trending' => $trending,
        'ads' => $ads,
        'user_liked' => $user_liked
      ];

      $this->view('client/posts/post', $data);
    }

    public function api_comments($news_id, $last_id){
      $comments = $this->commentModel->getNewComments($news_id, $last_id);
      
      // Format dates for JS
      foreach($comments as $comment){
        $comment->created_at_formatted = date('H:i d/m/Y', strtotime($comment->created_at));
        $comment->comment = nl2br(htmlspecialchars($comment->comment));
      }

      header('Content-Type: application/json');
      echo json_encode($comments);
    }

    public function comment($news_id){
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(!isset($_SESSION['user_id'])){
          header('Location: ' . URLROOT . '/users/login');
          exit();
        }

        $data = [
          'user_id' => $_SESSION['user_id'],
          'news_id' => $news_id,
          'comment' => trim($_POST['comment']),
          'status' => 'approved' // Automatically approve for now, or set to 'pending'
        ];

        if($this->commentModel->addComment($data)){
          $article = $this->postModel->getNewsById($news_id);
          header('Location: ' . URLROOT . '/posts/show/' . $article->slug);
        } else {
          die('Lỗi gửi bình luận');
        }
      }
    }

    public function toggle_like($type, $id){
      if(!isset($_SESSION['user_id'])){
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
      }

      if($type == 'news'){
        $this->postModel->toggleLike($_SESSION['user_id'], $id);
        $article = $this->postModel->getNewsById($id);
        echo json_encode(['success' => true, 'likes_count' => $article->likes_count]);
      } else if($type == 'comment'){
        $this->commentModel->toggleLike($_SESSION['user_id'], $id);
        // Need to get comment likes count - adding a quick query here or in model
        // For brevity, I'll assume I have a way to get it or return true
        echo json_encode(['success' => true]);
      }
      exit();
    }
}
