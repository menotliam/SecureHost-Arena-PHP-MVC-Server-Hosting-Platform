<?php
class AuditLog {
    private $db;

    public function __construct() {
        $this->db = new Database;
        $this->ensureTableExists();
    }

    /**
     * Tự động tạo bảng audit_logs nếu chưa tồn tại (Self-healing migration)
     */
    public function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                actor_user_id INT NULL,
                event_type VARCHAR(50) NOT NULL,
                target_type VARCHAR(50) NULL,
                target_id INT NULL,
                ip_address VARCHAR(50) NOT NULL,
                metadata LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_audit_logs_event_type (event_type),
                INDEX idx_audit_logs_created_at (created_at),
                INDEX idx_audit_logs_ip (ip_address),
                INDEX idx_audit_logs_actor (actor_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
            $this->db->execute();
        } catch (Throwable $e) {
            // Ignore error if table exists or creation fails gracefully
        }
    }

    /**
     * Ghi nhận một sự kiện bảo mật / audit log vào Database
     */
    public function recordEvent($eventType, $actorUserId = null, $targetType = null, $targetId = null, $ipAddress = null, $metadata = []) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        $metaJson = null;
        if (!empty($metadata)) {
            $jsonOpts = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
            $metaJson = json_encode($metadata, $jsonOpts);
        }

        try {
            $sql = "INSERT INTO audit_logs (actor_user_id, event_type, target_type, target_id, ip_address, metadata, created_at)
                    VALUES (:actor_user_id, :event_type, :target_type, :target_id, :ip_address, :metadata, NOW())";
            $this->db->query($sql);
            $this->db->bind(':actor_user_id', $actorUserId ? (int) $actorUserId : null);
            $this->db->bind(':event_type', (string) $eventType);
            $this->db->bind(':target_type', $targetType ? (string) $targetType : null);
            $this->db->bind(':target_id', $targetId ? (int) $targetId : null);
            $this->db->bind(':ip_address', (string) $ipAddress);
            $this->db->bind(':metadata', $metaJson);
            return $this->db->execute();
        } catch (Throwable $e) {
            error_log('[AuditLog] Failed to record event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm số lần đăng nhập thất bại (login_failed) trong cửa sổ thời gian (windowMinutes)
     * Áp dụng tiêu chí kết hợp: Theo IP hoặc theo Username nhập vào
     */
    public function countRecentFailures($username = '', $ip = '', $windowMinutes = 10) {
        $windowMinutes = max(1, (int) $windowMinutes);
        $username = trim((string) $username);
        $ip = trim((string) $ip);

        if ($ip === '' && $username === '') {
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) as total FROM audit_logs 
                    WHERE event_type = 'login_failed' 
                    AND created_at >= (NOW() - INTERVAL {$windowMinutes} MINUTE)
                    AND (";

            $conditions = [];
            if ($ip !== '') {
                $conditions[] = "ip_address = :ip";
            }
            if ($username !== '') {
                $conditions[] = "metadata LIKE :user_pattern";
            }

            $sql .= implode(' OR ', $conditions) . ")";

            $this->db->query($sql);
            if ($ip !== '') {
                $this->db->bind(':ip', $ip);
            }
            if ($username !== '') {
                $userPattern = '%"username":"' . $username . '"%';
                $this->db->bind(':user_pattern', $userPattern);
            }

            $row = $this->db->single();
            return $row ? (int) $row->total : 0;
        } catch (Throwable $e) {
            error_log('[AuditLog] countRecentFailures error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy thời điểm thất bại gần nhất trong cửa sổ để tính toán thời gian khóa còn lại
     */
    public function getLatestFailureTime($username = '', $ip = '', $windowMinutes = 10) {
        $windowMinutes = max(1, (int) $windowMinutes);
        $username = trim((string) $username);
        $ip = trim((string) $ip);

        try {
            $sql = "SELECT MAX(created_at) as latest_time FROM audit_logs 
                    WHERE event_type = 'login_failed' 
                    AND created_at >= (NOW() - INTERVAL {$windowMinutes} MINUTE)
                    AND (";

            $conditions = [];
            if ($ip !== '') {
                $conditions[] = "ip_address = :ip";
            }
            if ($username !== '') {
                $conditions[] = "metadata LIKE :user_pattern";
            }

            $sql .= implode(' OR ', $conditions) . ")";

            $this->db->query($sql);
            if ($ip !== '') {
                $this->db->bind(':ip', $ip);
            }
            if ($username !== '') {
                $userPattern = '%"username":"' . $username . '"%';
                $this->db->bind(':user_pattern', $userPattern);
            }

            $row = $this->db->single();
            return $row && $row->latest_time ? $row->latest_time : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Lấy danh sách Audit Logs cho giao diện quản trị Admin với bộ lọc và phân trang
     */
    public function getLogs($filters = [], $page = 1, $perPage = 15) {
        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT a.*, u.username as actor_username, u.full_name as actor_name, u.role as actor_role
                FROM audit_logs a
                LEFT JOIN users u ON a.actor_user_id = u.id";

        $where = [];
        $params = [];

        if (!empty($filters['event_type'])) {
            $where[] = "a.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = "(a.ip_address LIKE :kw OR a.metadata LIKE :kw OR u.username LIKE :kw OR u.full_name LIKE :kw)";
            $params[':kw'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "a.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "a.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.id DESC LIMIT {$perPage} OFFSET {$offset}";

        $this->db->query($sql);
        foreach ($params as $param => $val) {
            $this->db->bind($param, $val);
        }

        return $this->db->resultSet();
    }

    /**
     * Đếm tổng số bản ghi Audit Log theo bộ lọc (phục vụ phân trang)
     */
    public function countLogs($filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM audit_logs a
                LEFT JOIN users u ON a.actor_user_id = u.id";

        $where = [];
        $params = [];

        if (!empty($filters['event_type'])) {
            $where[] = "a.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = "(a.ip_address LIKE :kw OR a.metadata LIKE :kw OR u.username LIKE :kw OR u.full_name LIKE :kw)";
            $params[':kw'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "a.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "a.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $this->db->query($sql);
        foreach ($params as $param => $val) {
            $this->db->bind($param, $val);
        }

        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    /**
     * Lấy danh sách các loại event_type độc nhất để hiển thị dropdown filter
     */
    public function getDistinctEventTypes() {
        try {
            $this->db->query("SELECT DISTINCT event_type FROM audit_logs ORDER BY event_type ASC");
            return $this->db->resultSet();
        } catch (Throwable $e) {
            return [];
        }
    }
}
