<?php
class AdModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get active ads for a specific position
    public function getActiveAdsByPosition($position = 'sticky-sidebar') {
        $this->db->query('SELECT * FROM ads 
                          WHERE position = :position 
                          AND status = "active" 
                          AND (start_at IS NULL OR start_at <= NOW()) 
                          AND (end_at IS NULL OR end_at >= NOW()) 
                          ORDER BY created_at DESC');
        $this->db->bind(':position', $position);
        return $this->db->resultSet();
    }

    // Admin: Get all ads
    public function getAllAds() {
        $this->db->query('SELECT * FROM ads ORDER BY created_at DESC');
        return $this->db->resultSet();
    }

    // Get ad by ID
    public function getAdById($id) {
        $this->db->query('SELECT * FROM ads WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Add ad
    public function addAd($data) {
        $this->db->query('INSERT INTO ads (title, image_url, link_url, position, status, start_at, end_at) 
                          VALUES (:title, :image_url, :link_url, :position, :status, :start_at, :end_at)');
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':image_url', $data['image_url']);
        $this->db->bind(':link_url', $data['link_url']);
        $this->db->bind(':position', $data['position'] ?? 'sticky-sidebar');
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':start_at', $data['start_at'] ?: null);
        $this->db->bind(':end_at', $data['end_at'] ?: null);
        return $this->db->execute();
    }

    // Update ad
    public function updateAd($data) {
        $this->db->query('UPDATE ads SET title = :title, image_url = :image_url, link_url = :link_url, 
                          position = :position, status = :status, start_at = :start_at, end_at = :end_at 
                          WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':image_url', $data['image_url']);
        $this->db->bind(':link_url', $data['link_url']);
        $this->db->bind(':position', $data['position']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':start_at', $data['start_at'] ?: null);
        $this->db->bind(':end_at', $data['end_at'] ?: null);
        return $this->db->execute();
    }

    // Delete ad
    public function deleteAd($id) {
        $this->db->query('DELETE FROM ads WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
