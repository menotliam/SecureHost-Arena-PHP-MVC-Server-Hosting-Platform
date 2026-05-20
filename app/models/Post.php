<?php
  class Post {
    private $db;

    public function __construct(){
      $this->db = new Database;
    }

    // Get All Categories
    public function getCategories(){
      $this->db->query('SELECT * FROM news_categories ORDER BY name ASC');
      return $this->db->resultSet();
    }

    // Get All News (with optional search and category filter)
    public function getNews($keyword = '', $category_slug = ''){
      $sql = 'SELECT news.*, users.username as author_name, news_categories.name as category_name, news_categories.slug as category_slug 
              FROM news 
              LEFT JOIN users ON news.author_id = users.id 
              LEFT JOIN news_categories ON news.category_id = news_categories.id
              WHERE news.status = "published" 
              AND (news.publish_at IS NULL OR news.publish_at <= CURRENT_TIMESTAMP)';

      if(!empty($keyword)){
        $sql .= ' AND (news.title LIKE :keyword OR news.content LIKE :keyword OR news.meta_keywords LIKE :keyword)';
      }

      if(!empty($category_slug)){
        $sql .= ' AND news_categories.slug = :category_slug';
      }

      $sql .= ' ORDER BY news.created_at DESC';

      $this->db->query($sql);

      if(!empty($keyword)){
        $this->db->bind(':keyword', '%' . $keyword . '%');
      }

      if(!empty($category_slug)){
        $this->db->bind(':category_slug', $category_slug);
      }

      return $this->db->resultSet();
    }

    // Admin: Get All News (including drafts)
    public function getAllNewsAdmin(){
      $this->db->query('SELECT news.*, users.username as author_name FROM news LEFT JOIN users ON news.author_id = users.id ORDER BY news.created_at DESC');
      return $this->db->resultSet();
    }

    // Get News By Slug
    public function getNewsBySlug($slug){
      $this->db->query('SELECT news.*, users.username as author_name, news_categories.name as category_name 
                        FROM news 
                        LEFT JOIN users ON news.author_id = users.id 
                        LEFT JOIN news_categories ON news.category_id = news_categories.id
                        WHERE news.slug = :slug');
      $this->db->bind(':slug', $slug);
      return $this->db->single();
    }

    // Get News By ID
    public function getNewsById($id){
      $this->db->query('SELECT news.*, users.username as author_name FROM news LEFT JOIN users ON news.author_id = users.id WHERE news.id = :id');
      $this->db->bind(':id', $id);
      return $this->db->single();
    }

    // Add News
    public function addNews($data){
      $this->db->query('INSERT INTO news (title, slug, content, thumbnail, author_id, category_id, meta_keywords, meta_description, status, is_breaking, breaking_until, publish_at, seo_score) 
                        VALUES(:title, :slug, :content, :thumbnail, :author_id, :category_id, :meta_keywords, :meta_description, :status, :is_breaking, :breaking_until, :publish_at, :seo_score)');
      // Bind values
      $this->db->bind(':title', $data['title']);
      $this->db->bind(':slug', $data['slug']);
      $this->db->bind(':content', $data['content']);
      $this->db->bind(':thumbnail', $data['thumbnail']);
      $this->db->bind(':author_id', $data['author_id']);
      $this->db->bind(':category_id', $data['category_id'] ?? null);
      $this->db->bind(':meta_keywords', $data['meta_keywords']);
      $this->db->bind(':meta_description', $data['meta_description']);
      $this->db->bind(':status', $data['status'] ?? 'published');
      $this->db->bind(':is_breaking', $data['is_breaking'] ?? 0);
      $this->db->bind(':breaking_until', $data['breaking_until'] ?? null);
      $this->db->bind(':publish_at', $data['publish_at'] ?? null);
      $this->db->bind(':seo_score', $data['seo_score'] ?? 0);

      // Execute
      return $this->db->execute();
    }

    // Update News
    public function updateNews($data){
      $this->db->query('UPDATE news SET title = :title, slug = :slug, content = :content, thumbnail = :thumbnail, 
                        category_id = :category_id, meta_keywords = :meta_keywords, meta_description = :meta_description, 
                        status = :status, is_breaking = :is_breaking, breaking_until = :breaking_until, 
                        publish_at = :publish_at, seo_score = :seo_score 
                        WHERE id = :id');
      // Bind values
      $this->db->bind(':id', $data['id']);
      $this->db->bind(':title', $data['title']);
      $this->db->bind(':slug', $data['slug']);
      $this->db->bind(':content', $data['content']);
      $this->db->bind(':thumbnail', $data['thumbnail']);
      $this->db->bind(':category_id', $data['category_id'] ?? null);
      $this->db->bind(':meta_keywords', $data['meta_keywords']);
      $this->db->bind(':meta_description', $data['meta_description']);
      $this->db->bind(':status', $data['status']);
      $this->db->bind(':is_breaking', $data['is_breaking']);
      $this->db->bind(':breaking_until', $data['breaking_until']);
      $this->db->bind(':publish_at', $data['publish_at']);
      $this->db->bind(':seo_score', $data['seo_score']);

      // Execute
      return $this->db->execute();
    }

    // Delete News
    public function deleteNews($id){
      $this->db->query('DELETE FROM news WHERE id = :id');
      $this->db->bind(':id', $id);
      return $this->db->execute();
    }

    // Increment Views (and log it)
    public function incrementViews($id, $ip_address = '', $source = ''){
      $this->db->query('UPDATE news SET views_count = views_count + 1 WHERE id = :id');
      $this->db->bind(':id', $id);
      $this->db->execute();

      // Log detailed view
      $this->db->query('INSERT INTO news_views (news_id, ip_address, source) VALUES (:news_id, :ip, :source)');
      $this->db->bind(':news_id', $id);
      $this->db->bind(':ip', $ip_address);
      $this->db->bind(':source', $source);
      return $this->db->execute();
    }

    // Get Trending News (last 24 hours)
    public function getTrendingNews($limit = 5){
      $this->db->query('SELECT news.*, COUNT(news_views.id) as recent_views 
                        FROM news 
                        JOIN news_views ON news.id = news_views.news_id 
                        WHERE news_views.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        AND news.status = "published"
                        GROUP BY news.id 
                        ORDER BY recent_views DESC 
                        LIMIT :limit');
      $this->db->bind(':limit', $limit);
      return $this->db->resultSet();
    }

    // Get Breaking News
    public function getBreakingNews(){
      $this->db->query('SELECT * FROM news 
                        WHERE is_breaking = 1 
                        AND (breaking_until IS NULL OR breaking_until >= NOW()) 
                        AND status = "published"
                        ORDER BY created_at DESC');
      return $this->db->resultSet();
    }

    // Get Related News
    public function getRelatedNews($news_id, $category_id, $limit = 3){
      $this->db->query('SELECT * FROM news 
                        WHERE category_id = :category_id 
                        AND id != :id 
                        AND status = "published"
                        ORDER BY created_at DESC 
                        LIMIT :limit');
      $this->db->bind(':category_id', $category_id);
      $this->db->bind(':id', $news_id);
      $this->db->bind(':limit', $limit);
      return $this->db->resultSet();
    }

    // Like News Article
    public function toggleLike($user_id, $news_id){
      // Check if already liked
      $this->db->query('SELECT * FROM news_likes WHERE user_id = :user_id AND news_id = :news_id');
      $this->db->bind(':user_id', $user_id);
      $this->db->bind(':news_id', $news_id);
      
      if($this->db->single()){
        // Unlike
        $this->db->query('DELETE FROM news_likes WHERE user_id = :user_id AND news_id = :news_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':news_id', $news_id);
        $this->db->execute();
        
        $this->db->query('UPDATE news SET likes_count = likes_count - 1 WHERE id = :id');
        $this->db->bind(':id', $news_id);
        return $this->db->execute();
      } else {
        // Like
        $this->db->query('INSERT INTO news_likes (user_id, news_id) VALUES (:user_id, :news_id)');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':news_id', $news_id);
        $this->db->execute();
        
        $this->db->query('UPDATE news SET likes_count = likes_count + 1 WHERE id = :id');
        $this->db->bind(':id', $news_id);
        return $this->db->execute();
      }
    }

    // Check if user liked news
    public function hasLiked($user_id, $news_id){
      $this->db->query('SELECT * FROM news_likes WHERE user_id = :user_id AND news_id = :news_id');
      $this->db->bind(':user_id', $user_id);
      $this->db->bind(':news_id', $news_id);
      return $this->db->single() ? true : false;
    }

    // Get Analytics
    public function getAnalytics($id){
      $data = [];
      
      // Total views
      $this->db->query('SELECT COUNT(*) as total FROM news_views WHERE news_id = :id');
      $this->db->bind(':id', $id);
      $data['total_views'] = $this->db->single()->total;
      
      // Top sources
      $this->db->query('SELECT source, COUNT(*) as count FROM news_views WHERE news_id = :id GROUP BY source ORDER BY count DESC LIMIT 5');
      $this->db->bind(':id', $id);
      $data['top_sources'] = $this->db->resultSet();
      
      // Views by date (last 7 days)
      $this->db->query('SELECT DATE(created_at) as date, COUNT(*) as count 
                        FROM news_views 
                        WHERE news_id = :id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        GROUP BY DATE(created_at)');
      $this->db->bind(':id', $id);
      $data['views_history'] = $this->db->resultSet();
      
      return $data;
    }
  }
