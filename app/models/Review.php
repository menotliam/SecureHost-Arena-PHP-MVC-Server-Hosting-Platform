<?php
class Review {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Lấy tất cả đánh giá cho Admin
    public function getAllReviews() {
        $this->db->query("
            SELECT r.*, u.username, u.full_name, p.name as product_name 
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            LEFT JOIN products p ON r.product_id = p.id 
            ORDER BY r.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Cập nhật trạng thái (Duyệt / Ẩn)
    public function updateStatus($id, $status) {
        $this->db->query("UPDATE reviews SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Xóa đánh giá
    public function deleteReview($id) {
        $this->db->query("DELETE FROM reviews WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}