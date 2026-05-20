<?php
class Order {
    private $db;
    public function __construct() {
        $this->db = new Database;
    }

    public function getCartByUserId($user_id) {
        $this->db->query("SELECT * FROM carts WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    public function createCart($user_id, $session_id = null) {
        $this->db->query("INSERT INTO carts (user_id, session_id) VALUES (:user_id, :session_id)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':session_id', $session_id);
        return $this->db->execute();
    }

    public function addToCartItem($cart_id, $product_id, $duration_months = 1) {
        $this->db->query("SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
        $this->db->bind(':cart_id', $cart_id);
        $this->db->bind(':product_id', $product_id);
        $existing = $this->db->single();

        if ($existing) {
            $this->db->query("UPDATE cart_items SET duration_months = duration_months + :duration WHERE id = :id");
            $this->db->bind(':duration', $duration_months);
            $this->db->bind(':id', $existing->id);
            return $this->db->execute();
        } else {
            $this->db->query("INSERT INTO cart_items (cart_id, product_id, duration_months) VALUES (:cart_id, :product_id, :duration_months)");
            $this->db->bind(':cart_id', $cart_id);
            $this->db->bind(':product_id', $product_id);
            $this->db->bind(':duration_months', $duration_months);
            return $this->db->execute();
        }
    }

    public function getTotalOrders() {
        $this->db->query("SELECT COUNT(*) as total FROM orders");
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    public function getOrders($limit, $offset) {
        $this->db->query("SELECT o.*, u.username, u.email 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          ORDER BY o.created_at DESC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function updateOrderStatus($order_id, $status) {
        $this->db->query("UPDATE orders SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $order_id);
        return $this->db->execute();
    }

    public function createOrder($user_id, $cartItems, $totalAmount, $address = null, $phone = null) {
        // Thực hiện lưu orders
        $this->db->query("INSERT INTO orders (user_id, total_amount, status, address, phone) VALUES (:user_id, :total_amount, 'pending', :address, :phone)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':address', $address);
        $this->db->bind(':phone', $phone);
        
        if ($this->db->execute()) {
            // Use PDO lastInsertId for clarity
            $orderId = (int) $this->db->lastInsertId();
            
            if($orderId && !empty($cartItems)) {
                foreach($cartItems as $productId => $quantity) {
                    $this->db->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, (SELECT price FROM products WHERE id = :pid))");
                    $this->db->bind(':order_id', $orderId, PDO::PARAM_INT);
                    $this->db->bind(':product_id', $productId);
                    $this->db->bind(':quantity', $quantity);
                    $this->db->bind(':pid', $productId, PDO::PARAM_INT);
                    $this->db->execute();
                }
            }

            return true; 
        }
        return false;
    }
    
    
    // --- CHO TRANG PROFILE KHÁCH HÀNG ---
    public function getOrdersByUserId($user_id) {
        $this->db->query("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getOrderItems($order_id) {
        $this->db->query("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
        $this->db->bind(':order_id', $order_id);
        return $this->db->resultSet();
    }

    public function getUserServices($user_id) {
        $this->db->query("SELECT us.*, p.name AS product_name, p.image_url, p.cpu_cores, p.disk_gb FROM user_services us JOIN products p ON us.product_id = p.id WHERE us.user_id = :user_id ORDER BY us.id DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    // --- LOGIC CẤP PHÁT SERVER (TỰ ĐỘNG) CHO ADMIN ---
    public function provisionServices($order_id) {
        // Lấy thông tin đơn hàng
        $this->db->query("SELECT * FROM orders WHERE id = :id");
        $this->db->bind(':id', $order_id);
        $order = $this->db->single();

        // Lấy các sản phẩm trong đơn
        $this->db->query("SELECT oi.*, p.ram_mb FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
        $this->db->bind(':order_id', $order_id);
        $items = $this->db->resultSet();

        foreach($items as $item) {
            // Giả lập cấp phát IP và Port cho Server
            $ip = '103.130.' . rand(10, 250) . '.' . rand(10, 250);
            $port = rand(2000, 9999);
            // Tính ngày hết hạn (Dựa vào số tháng khách mua)
            $months = $item->duration_months > 0 ? $item->duration_months : 1;
            $expires_at = date('Y-m-d H:i:s', strtotime("+$months months"));

            // Insert vào bảng user_services
            $this->db->query("INSERT INTO user_services (user_id, product_id, ip_address, port, status, current_ram_mb, expires_at) VALUES (:user_id, :product_id, :ip, :port, 'active', :ram, :expires)");
            $this->db->bind(':user_id', $order->user_id);
            $this->db->bind(':product_id', $item->product_id);
            $this->db->bind(':ip', $ip);
            $this->db->bind(':port', $port);
            $this->db->bind(':ram', $item->ram_mb);
            $this->db->bind(':expires', $expires_at);
            $this->db->execute();
        }
        return true;
    }

    public function getOrderById($id) {
        $this->db->query("SELECT o.*, u.username, u.email, u.full_name 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    public function getMonthlyRevenue() {
        $this->db->query(
            "SELECT COALESCE(SUM(total_amount), 0) AS total
             FROM orders
             WHERE status = 'completed'
             AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')"
        );
        $row = $this->db->single();
        return $row ? (float) $row->total : 0;
    }

    public function getLastFiveMonthRevenue() {
        $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             WHERE status = 'completed'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month_key ASC"
        );
        return $this->db->resultSet();
    }

    public function getRevenueNotificationSummary($sinceTimestamp = null, $limit = 5) {
        $safeSince = trim((string) $sinceTimestamp) !== '' ? trim((string) $sinceTimestamp) : '1970-01-01 00:00:00';
        $safeLimit = max(1, (int) $limit);

        $this->db->query(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(total_amount), 0) AS total_revenue,
                    MAX(created_at) AS latest_created_at
             FROM orders
             WHERE status = 'completed'
               AND created_at > :since_timestamp"
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $meta = $this->db->single();

        $this->db->query(
            "SELECT id, user_id, total_amount, created_at
             FROM orders
             WHERE status = 'completed'
               AND created_at > :since_timestamp
             ORDER BY created_at DESC
             LIMIT :limit_rows"
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $this->db->bind(':limit_rows', $safeLimit);
        $items = $this->db->resultSet();

        return [
            'count' => $meta ? (int) $meta->total_orders : 0,
            'revenue' => $meta ? (float) $meta->total_revenue : 0,
            'latest_created_at' => ($meta && !empty($meta->latest_created_at)) ? (string) $meta->latest_created_at : null,
            'items' => $items
        ];
    }

    public function getLatestCompletedOrderCreatedAt() {
        $this->db->query(
            "SELECT MAX(created_at) AS latest_created_at
             FROM orders
             WHERE status = 'completed'"
        );
        $row = $this->db->single();
        if (!$row || empty($row->latest_created_at)) {
            return null;
        }
        return (string) $row->latest_created_at;
    }

    public function getRecentRevenueNotifications($limit = 30) {
        $safeLimit = max(1, (int) $limit);
        $this->db->query(
            "SELECT id, user_id, total_amount, created_at
             FROM orders
             WHERE status = 'completed'
             ORDER BY created_at DESC
             LIMIT :limit_rows"
        );
        $this->db->bind(':limit_rows', $safeLimit);
        return $this->db->resultSet();
    }

    public function countNewRevenueNotificationsSince($sinceTimestamp = null) {
        $safeSince = trim((string) $sinceTimestamp) !== '' ? trim((string) $sinceTimestamp) : '1970-01-01 00:00:00';
        $this->db->query(
            "SELECT COUNT(*) AS total
             FROM orders
             WHERE status = 'completed'
               AND created_at > :since_timestamp"
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    /**
     * Đơn chờ xử lý của user (cho form ticket vấn đề đơn hàng).
     *
     * @return object[]
     */
    public function getPendingOrdersForUser($userId) {
        $uid = (int) $userId;
        if ($uid <= 0) {
            return [];
        }

        $this->db->query(
            "SELECT o.id, o.total_amount, o.created_at,
                    COALESCE(GROUP_CONCAT(CONCAT(p.name, ' ×', oi.quantity) ORDER BY oi.id SEPARATOR ', '), '') AS items_label
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.user_id = :user_id AND o.status = 'pending'
             GROUP BY o.id, o.total_amount, o.created_at
             ORDER BY o.created_at DESC"
        );
        $this->db->bind(':user_id', $uid);

        return $this->db->resultSet();
    }

    public function getPendingOrderByIdForUser($orderId, $userId) {
        $oid = (int) $orderId;
        $uid = (int) $userId;
        if ($oid <= 0 || $uid <= 0) {
            return null;
        }

        $this->db->query(
            "SELECT o.id, o.total_amount, o.created_at, o.status
             FROM orders o
             WHERE o.id = :order_id AND o.user_id = :user_id AND o.status = 'pending'
             LIMIT 1"
        );
        $this->db->bind(':order_id', $oid);
        $this->db->bind(':user_id', $uid);

        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Tóm tắt đơn (mọi trạng thái) khi đối chiếu ticket — chỉ khi đúng chủ sở hữu.
     */
    public function getOrderTicketSummaryByIdForUser($orderId, $userId) {
        $oid = (int) $orderId;
        $uid = (int) $userId;
        if ($oid <= 0 || $uid <= 0) {
            return null;
        }

        $this->db->query(
            "SELECT o.id, o.total_amount, o.created_at, o.status,
                    COALESCE(GROUP_CONCAT(CONCAT(p.name, ' ×', oi.quantity) ORDER BY oi.id SEPARATOR ', '), '') AS items_label
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.id = :order_id AND o.user_id = :user_id
             GROUP BY o.id, o.total_amount, o.created_at, o.status
             LIMIT 1"
        );
        $this->db->bind(':order_id', $oid);
        $this->db->bind(':user_id', $uid);

        $row = $this->db->single();
        return $row ?: null;
    }
    public function getAllOrders() {
        $this->db->query("
            SELECT o.*, u.username, u.full_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        return $this->db->resultSet();
    }
}