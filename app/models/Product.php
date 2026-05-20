<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    private function makeSlug($name) {
        $slug = strtolower(trim((string) $name));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'service-plan';
        }
        return $slug;
    }

    private function getUniqueSlug($name) {
        $baseSlug = $this->makeSlug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (true) {
            $this->db->query('SELECT id FROM products WHERE slug = :slug LIMIT 1');
            $this->db->bind(':slug', $slug);
            $existing = $this->db->single();
            if (!$existing) {
                return $slug;
            }
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }
    }

    public function countActiveServices() {
        try {
            $this->db->query("SELECT COUNT(*) as total FROM user_services WHERE status = 'active'");
            $row = $this->db->single();
            return $row ? (int) $row->total : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function getProducts($limit, $offset, $keyword = '', $isAdmin = false, $categoryId = null, $minPrice = null, $maxPrice = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";

        $where = [];
        if (!$isAdmin) {
            $where[] = "p.status = 'active'";
        }
        if (!empty($keyword)) {
            $where[] = "p.name LIKE :keyword";
        }
        if (!empty($categoryId)) {
            $where[] = "p.category_id = :category_id";
        }
        if ($minPrice !== null && $minPrice !== '') {
            $where[] = "p.price >= :min_price";
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $where[] = "p.price <= :max_price";
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);

        if (!empty($keyword)) { $this->db->bind(':keyword', '%' . $keyword . '%'); }
        if (!empty($categoryId)) { $this->db->bind(':category_id', (int)$categoryId, PDO::PARAM_INT); }
        if ($minPrice !== null && $minPrice !== '') { $this->db->bind(':min_price', (float)$minPrice); }
        if ($maxPrice !== null && $maxPrice !== '') { $this->db->bind(':max_price', (float)$maxPrice); }

        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getTotalProducts($keyword = '', $isAdmin = false, $categoryId = null, $minPrice = null, $maxPrice = null) {
        $sql = "SELECT COUNT(*) as total FROM products";

        $where = [];
        if (!$isAdmin) {
            $where[] = "status = 'active'";
        }
        if (!empty($keyword)) {
            $where[] = "name LIKE :keyword";
        }
        if (!empty($categoryId)) {
            $where[] = "category_id = :category_id";
        }
        if ($minPrice !== null && $minPrice !== '') {
            $where[] = "price >= :min_price";
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $where[] = "price <= :max_price";
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $this->db->query($sql);

        if (!empty($keyword)) { $this->db->bind(':keyword', '%' . $keyword . '%'); }
        if (!empty($categoryId)) { $this->db->bind(':category_id', (int)$categoryId, PDO::PARAM_INT); }
        if ($minPrice !== null && $minPrice !== '') { $this->db->bind(':min_price', (float)$minPrice); }
        if ($maxPrice !== null && $maxPrice !== '') { $this->db->bind(':max_price', (float)$maxPrice); }

        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    public function getProductById($id) {
        $this->db->query("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = :id");
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }
    
    public function getCategories() {
        $this->db->query("SELECT * FROM categories");
        return $this->db->resultSet();
    }

    public function addProduct($data) {
        $slug = isset($data['slug']) && trim($data['slug']) !== '' ? trim($data['slug']) : $this->getUniqueSlug($data['name'] ?? '');
        $this->db->query("INSERT INTO products (category_id, name, slug, description, price, ram_mb, cpu_cores, disk_gb, image_url, status) 
                          VALUES (:category_id, :name, :slug, :description, :price, :ram_mb, :cpu_cores, :disk_gb, :image_url, :status)");
        
        $this->db->bind(':category_id', (int)($data['category_id'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':name', $data['name'] ?? '');
        $this->db->bind(':slug', $slug);
        $this->db->bind(':description', $data['description'] ?? '');
        $this->db->bind(':price', (float)($data['price'] ?? 0));
        $this->db->bind(':ram_mb', (int)($data['ram_mb'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':cpu_cores', (int)($data['cpu_cores'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':disk_gb', (int)($data['disk_gb'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':image_url', $data['image_url'] ?? '');
        $this->db->bind(':status', $data['status'] ?? 'active');

        return $this->db->execute();
    }

    public function updateProduct($data) {
        $this->db->query("UPDATE products 
                          SET category_id = :category_id, name = :name, slug = :slug, description = :description, 
                              price = :price, ram_mb = :ram_mb, cpu_cores = :cpu_cores, disk_gb = :disk_gb, 
                              image_url = :image_url, status = :status 
                          WHERE id = :id");
        
        $this->db->bind(':id', (int)$data['id'], PDO::PARAM_INT);
        $this->db->bind(':category_id', (int)($data['category_id'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':name', $data['name'] ?? '');
        $this->db->bind(':slug', $data['slug'] ?? $this->getUniqueSlug($data['name'] ?? ''));
        $this->db->bind(':description', $data['description'] ?? '');
        $this->db->bind(':price', (float)($data['price'] ?? 0));
        $this->db->bind(':ram_mb', (int)($data['ram_mb'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':cpu_cores', (int)($data['cpu_cores'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':disk_gb', (int)($data['disk_gb'] ?? 0), PDO::PARAM_INT);
        $this->db->bind(':image_url', $data['image_url'] ?? '');
        $this->db->bind(':status', $data['status'] ?? 'active');

        return $this->db->execute();
    }

    public function deleteProduct($id) {
        $this->db->query("DELETE FROM products WHERE id = :id");
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function deletePackage($id) {
        return $this->deleteProduct($id);
    }

    public function getProductBySlug($slug) {
        $this->db->query("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.slug = :slug AND p.status = 'active'");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    /**
     * Danh sách gọn cho admin (mọi trạng thái), phân trang — tương thích bản Product “gói”.
     */
    public function getAdminPackages($page = 1, $perPage = 6) {
        $offset = max(0, ((int) $page - 1) * (int) $perPage);
        $this->db->query(
            'SELECT id, name, price, ram_mb, cpu_cores, disk_gb, image_url, status
             FROM products
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        $this->db->bind(':limit', (int) $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', (int) $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countAdminPackages() {
        $this->db->query('SELECT COUNT(*) AS total FROM products');
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    /**
     * Tạo gói nhanh với slug tự sinh (không trùng addProduct — form admin đầy đủ vẫn dùng addProduct).
     */
    public function createPackage($data) {
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            return false;
        }

        $slug = $this->getUniqueSlug($name);
        $categoryId = (int) ($data['category_id'] ?? 1);
        $price = (float) ($data['price'] ?? 0);
        $ramMb = (int) ($data['ram_mb'] ?? 0);
        $cpuCores = (int) ($data['cpu_cores'] ?? 0);
        $diskGb = (int) ($data['disk_gb'] ?? 0);
        $description = trim($data['description'] ?? '');
        $imageUrl = trim($data['image_url'] ?? '');

        $this->db->query(
            "INSERT INTO products (category_id, name, slug, description, price, ram_mb, cpu_cores, disk_gb, image_url, status)
             VALUES (:category_id, :name, :slug, :description, :price, :ram_mb, :cpu_cores, :disk_gb, :image_url, 'active')"
        );
        $this->db->bind(':category_id', $categoryId, PDO::PARAM_INT);
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        $this->db->bind(':description', $description);
        $this->db->bind(':price', $price);
        $this->db->bind(':ram_mb', $ramMb, PDO::PARAM_INT);
        $this->db->bind(':cpu_cores', $cpuCores, PDO::PARAM_INT);
        $this->db->bind(':disk_gb', $diskGb, PDO::PARAM_INT);
        $this->db->bind(':image_url', $imageUrl);

        return $this->db->execute();
    }

    /**
     * @param int $limit
     * @param string $sort 'recent' (mặc định) hoặc 'name' — Admin gọi không tham số vẫn giữ hành vi cũ.
     */
    public function getProductPickerList($limit = 200, $sort = 'recent') {
        $limit = max(1, min(500, (int) $limit));
        $orderBy = ($sort === 'name') ? 'name ASC' : 'created_at DESC';
        $sql = "SELECT id, name, slug FROM products WHERE status = 'active' ORDER BY $orderBy LIMIT :limit";
        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getActiveProductsByIdsOrdered(array $ids) {
        $safeIds = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return (int) $id > 0;
        })));
        if (empty($safeIds)) {
            return [];
        }

        $placeholders = [];
        foreach ($safeIds as $i => $id) {
            $placeholders[] = ':hpid' . $i;
        }
        $inList = implode(',', $placeholders);
        $fieldOrder = implode(',', $safeIds);

        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.id IN ($inList)
                ORDER BY FIELD(p.id, $fieldOrder)";

        $this->db->query($sql);
        foreach ($safeIds as $i => $id) {
            $this->db->bind(':hpid' . $i, $id, PDO::PARAM_INT);
        }

        return $this->db->resultSet();
    }

    /**
     * Lấy sản phẩm liên quan (cùng danh mục)
     */
    public function getRelatedProducts($category_id, $current_product_id, $limit = 4) {
        $this->db->query("SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = :category_id 
              AND p.id != :current_id 
              AND p.status = 'active' 
            ORDER BY RAND() 
            LIMIT :limit");
        $this->db->bind(':category_id', (int)$category_id, PDO::PARAM_INT);
        $this->db->bind(':current_id', (int)$current_product_id, PDO::PARAM_INT);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Lấy các đánh giá đã được duyệt cho 1 sản phẩm
     */
    public function getReviews($product_id) {
        $this->db->query("SELECT r.*, u.full_name, u.username, u.avatar 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = :product_id 
              AND r.status = 'approved' 
            ORDER BY r.created_at DESC");
        $this->db->bind(':product_id', (int)$product_id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getAverageRating($product_id) {
        $this->db->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = :product_id AND status = 'approved'");
        $this->db->bind(':product_id', (int)$product_id, PDO::PARAM_INT);
        $row = $this->db->single();
        if (!$row) return ['avg' => 0, 'count' => 0];
        return ['avg' => $row->avg_rating ? round((float)$row->avg_rating, 2) : 0.0, 'count' => (int)$row->cnt];
    }

    // Kiểm tra xem user này đã mua sản phẩm và đơn hàng đã hoàn tất chưa
    public function canReview($user_id, $product_id) {
        $this->db->query("SELECT COUNT(*) as count 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = :user_id 
              AND oi.product_id = :product_id 
              AND o.status = 'completed'");
        $this->db->bind(':user_id', (int)$user_id, PDO::PARAM_INT);
        $this->db->bind(':product_id', (int)$product_id, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ? ((int)$row->count > 0) : false;
    }

    // Kiểm tra xem user này đã từng đánh giá sản phẩm này chưa (Tránh spam)
    public function hasReviewed($user_id, $product_id) {
        $this->db->query("SELECT COUNT(*) as count FROM reviews WHERE user_id = :user_id AND product_id = :product_id");
        $this->db->bind(':user_id', (int)$user_id, PDO::PARAM_INT);
        $this->db->bind(':product_id', (int)$product_id, PDO::PARAM_INT);
        $row = $this->db->single();
        return $row ? ((int)$row->count > 0) : false;
    }

    // Lưu đánh giá mới vào DB
    public function addReview($user_id, $product_id, $rating, $comment, $autoApprove = false) {
        $status = $autoApprove ? 'approved' : 'pending';
        $this->db->query("INSERT INTO reviews (user_id, product_id, rating, comment, status) VALUES (:user_id, :product_id, :rating, :comment, :status)");
        $this->db->bind(':user_id', (int)$user_id, PDO::PARAM_INT);
        $this->db->bind(':product_id', (int)$product_id, PDO::PARAM_INT);
        $this->db->bind(':rating', (int)$rating, PDO::PARAM_INT);
        $this->db->bind(':comment', trim((string)$comment));
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    public function getHomepagePackages($limit = 4) {
        $limit = max(1, min(50, (int) $limit));
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT :limit";
        $this->db->query($sql);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}