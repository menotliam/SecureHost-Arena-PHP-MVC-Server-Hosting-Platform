<?php
class Comment {
    private $db;
    public function __construct() {
        $this->db = new Database;
    }

    // Get comments for a specific news article
    public function getCommentsByNewsId($news_id) {
        $this->db->query('SELECT reviews.*, users.username, users.avatar 
                          FROM reviews 
                          JOIN users ON reviews.user_id = users.id 
                          WHERE reviews.news_id = :news_id AND reviews.status = "approved" 
                          ORDER BY reviews.created_at DESC');
        $this->db->bind(':news_id', $news_id);
        return $this->db->resultSet();
    }

    // Get ONLY new comments since last ID
    public function getNewComments($news_id, $last_id) {
        $this->db->query('SELECT reviews.*, users.username, users.avatar 
                          FROM reviews 
                          JOIN users ON reviews.user_id = users.id 
                          WHERE reviews.news_id = :news_id AND reviews.status = "approved" AND reviews.id > :last_id
                          ORDER BY reviews.created_at ASC');
        $this->db->bind(':news_id', $news_id);
        $this->db->bind(':last_id', $last_id);
        return $this->db->resultSet();
    }

    // Add a comment
    public function addComment($data) {
        // Simple banned words filter
        $banned_words = ['spam', 'badword1', 'badword2']; // Example words
        foreach($banned_words as $word){
            if(stripos($data['comment'], $word) !== false){
                return false; // Or throw an error
            }
        }

        $this->db->query('INSERT INTO reviews (user_id, news_id, comment, status) VALUES (:user_id, :news_id, :comment, :status)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':news_id', $data['news_id']);
        $this->db->bind(':comment', $data['comment']);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        return $this->db->execute();
    }

    // Toggle Like on Comment
    public function toggleLike($user_id, $review_id) {
        $this->db->query('SELECT * FROM review_likes WHERE user_id = :user_id AND review_id = :review_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':review_id', $review_id);

        if($this->db->single()){
            // Unlike
            $this->db->query('DELETE FROM review_likes WHERE user_id = :user_id AND review_id = :review_id');
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':review_id', $review_id);
            $this->db->execute();

            $this->db->query('UPDATE reviews SET likes_count = likes_count - 1 WHERE id = :id');
            $this->db->bind(':id', $review_id);
            return $this->db->execute();
        } else {
            // Like
            $this->db->query('INSERT INTO review_likes (user_id, review_id) VALUES (:user_id, :review_id)');
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':review_id', $review_id);
            $this->db->execute();

            $this->db->query('UPDATE reviews SET likes_count = likes_count + 1 WHERE id = :id');
            $this->db->bind(':id', $review_id);
            return $this->db->execute();
        }
    }

    // Check if user liked comment
    public function hasLiked($user_id, $review_id) {
        $this->db->query('SELECT * FROM review_likes WHERE user_id = :user_id AND review_id = :review_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':review_id', $review_id);
        return $this->db->single() ? true : false;
    }

    // Admin: Get all comments (news related)
    public function getNewsCommentsAdmin() {
        $this->db->query('SELECT reviews.*, users.username, news.title as news_title 
                          FROM reviews 
                          JOIN users ON reviews.user_id = users.id 
                          JOIN news ON reviews.news_id = news.id 
                          ORDER BY reviews.created_at DESC');
        return $this->db->resultSet();
    }

    // Update comment status (approve/hide)
    public function updateStatus($id, $status) {
        $this->db->query('UPDATE reviews SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Delete comment
    public function delete($id) {
        $this->db->query('DELETE FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Return a list of approved product reviews for admin pickers
     * limited by $limit. Returns lightweight rows for dropdowns.
     */
    public function listApprovedProductReviewsForPicker($limit = 120) {
        $sql = 'SELECT r.id, r.product_id, r.rating, r.comment, p.name AS product_name, u.username AS reviewer'
             . ' FROM reviews r'
             . ' LEFT JOIN products p ON r.product_id = p.id'
             . ' LEFT JOIN users u ON r.user_id = u.id'
             . " WHERE r.status = 'approved' AND r.product_id IS NOT NULL ORDER BY r.created_at DESC LIMIT :limit";
        $this->db->query($sql);
        $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Single approved product review by product_id and review id (home_review_key "productId:reviewId").
     * Includes user display fields for homepage card.
     */
    public function getApprovedProductReviewByKey($productId, $reviewId) {
        $sql = 'SELECT r.*, p.name AS product_name, u.full_name, u.username, u.avatar'
             . ' FROM reviews r'
             . ' LEFT JOIN products p ON r.product_id = p.id'
             . ' LEFT JOIN users u ON r.user_id = u.id'
             . " WHERE r.status = 'approved' AND r.product_id IS NOT NULL"
             . ' AND r.id = :review_id AND r.product_id = :product_id'
             . ' LIMIT 1';
        $this->db->query($sql);
        $this->db->bind(':review_id', (int) $reviewId, PDO::PARAM_INT);
        $this->db->bind(':product_id', (int) $productId, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Return latest five-star approved product review (single) or null.
     */
    public function getLatestFiveStarProductReview() {
        $sql = 'SELECT r.*, p.name AS product_name, u.full_name, u.username, u.avatar'
             . ' FROM reviews r'
             . ' LEFT JOIN products p ON r.product_id = p.id'
             . ' LEFT JOIN users u ON r.user_id = u.id'
             . " WHERE r.status = 'approved' AND COALESCE(r.rating,0) >= 5 AND r.product_id IS NOT NULL"
             . ' ORDER BY r.created_at DESC LIMIT 1';
        $this->db->query($sql);
        return $this->db->single();
    }
}
