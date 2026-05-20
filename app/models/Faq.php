<?php
class Faq {
    private $db;
    private $columnsCache = null;
    public function __construct() {
        $this->db = new Database;
        // ensure schema elements exist (category column, messages table)
        $this->ensureCategoryColumnExists();
        $this->ensureMessagesTableExists();
    }

    private function columnExists($col){
        if($this->columnsCache === null){
            $this->db->query("SHOW COLUMNS FROM faqs");
            $rows = $this->db->resultSet();
            $this->columnsCache = [];
            foreach($rows as $r){
                if(is_object($r)){
                    $props = get_object_vars($r);
                    $this->columnsCache[] = array_values($props)[0];
                } elseif(is_array($r)){
                    $this->columnsCache[] = array_values($r)[0];
                }
            }
        }
        return in_array($col, $this->columnsCache);
    }

    private function ensureCategoryColumnExists(){
        if($this->columnExists('category')) return;
        try{
            $this->db->query("ALTER TABLE faqs ADD COLUMN category VARCHAR(100) NULL");
            $this->db->execute();
            // refresh cache
            $this->columnsCache = null;
        } catch(Exception $e){
            // ignore failures (permissions, etc.)
        }
    }

    private function ensureMessagesTableExists(){
        // create table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS faq_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) DEFAULT NULL,
            email VARCHAR(200) DEFAULT NULL,
            category VARCHAR(100) DEFAULT NULL,
            message TEXT NOT NULL,
            page_url VARCHAR(255) DEFAULT NULL,
            status VARCHAR(30) DEFAULT 'new',
            reply TEXT DEFAULT NULL,
            reply_by VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            replied_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try{
            $this->db->query($sql);
            $this->db->execute();
        } catch(Exception $e){
            // ignore
        }
    }

    // Message CRUD
    public function createMessage($data){
        $this->ensureMessagesTableExists();
        $this->db->query("INSERT INTO faq_messages (name, email, category, message, page_url, status) VALUES (:name, :email, :category, :message, :page_url, :status)");
        $this->db->bind(':name', isset($data['name']) ? $data['name'] : null);
        $this->db->bind(':email', isset($data['email']) ? $data['email'] : null);
        $this->db->bind(':category', isset($data['category']) ? $data['category'] : null);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':page_url', isset($data['page_url']) ? $data['page_url'] : null);
        $this->db->bind(':status', isset($data['status']) ? $data['status'] : 'new');
        return $this->db->execute();
    }

    public function getMessages($limit = 50, $offset = 0){
        $this->ensureMessagesTableExists();
        $this->db->query("SELECT * FROM faq_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    public function getNewMessagesCount(){
        $this->ensureMessagesTableExists();
        $this->db->query("SELECT COUNT(*) as total FROM faq_messages WHERE status = 'new'");
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getMessageById($id){
        $this->ensureMessagesTableExists();
        $this->db->query("SELECT * FROM faq_messages WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get conversation messages by email or page_url. If both provided, prefer email.
    public function getConversation($email = null, $page_url = null, $limit = 100){
        $this->ensureMessagesTableExists();
        if($email){
            $this->db->query("SELECT * FROM faq_messages WHERE email = :email ORDER BY created_at ASC LIMIT :limit");
            $this->db->bind(':email', $email);
            $this->db->bind(':limit', (int)$limit);
            return $this->db->resultSet();
        }
        if($page_url){
            $this->db->query("SELECT * FROM faq_messages WHERE page_url = :page_url ORDER BY created_at ASC LIMIT :limit");
            $this->db->bind(':page_url', $page_url);
            $this->db->bind(':limit', (int)$limit);
            return $this->db->resultSet();
        }
        return [];
    }

    public function replyMessage($id, $reply, $replyBy = null){
        $this->ensureMessagesTableExists();
        $this->db->query("UPDATE faq_messages SET reply = :reply, reply_by = :reply_by, status = 'replied', replied_at = NOW() WHERE id = :id");
        $this->db->bind(':reply', $reply);
        $this->db->bind(':reply_by', $replyBy);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getAll() {
        $sql = "SELECT * FROM faqs WHERE status = 'active'";
        if($this->columnExists('category')) $sql .= " ORDER BY category ASC, id DESC"; else $sql .= " ORDER BY id DESC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function getAllAdmin() {
        $sql = "SELECT * FROM faqs";
        if($this->columnExists('category')) $sql .= " ORDER BY category ASC, id DESC"; else $sql .= " ORDER BY id DESC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function countAll(){
        $this->db->query("SELECT COUNT(*) as total FROM faqs");
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function countActive($category = null){
        if($category && $this->columnExists('category')){
            $this->db->query("SELECT COUNT(*) as total FROM faqs WHERE status = 'active' AND category = :cat");
            $this->db->bind(':cat', $category);
        } else {
            $this->db->query("SELECT COUNT(*) as total FROM faqs WHERE status = 'active'");
        }
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getPage($limit, $offset){
        $sql = "SELECT * FROM faqs ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $this->db->query($sql);
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    public function getPageByCategory($limit, $offset, $category){
        $sql = "SELECT * FROM faqs WHERE category = :cat ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $this->db->query($sql);
        $this->db->bind(':cat', $category);
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    public function countByCategory($category){
        $this->db->query("SELECT COUNT(*) as total FROM faqs WHERE category = :cat");
        $this->db->bind(':cat', $category);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getPageActive($limit, $offset, $category = null){
        if($category && $this->columnExists('category')){
            $this->db->query("SELECT * FROM faqs WHERE status = 'active' AND category = :cat ORDER BY id DESC LIMIT :limit OFFSET :offset");
            $this->db->bind(':cat', $category);
        } else {
            $this->db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY id DESC LIMIT :limit OFFSET :offset");
        }
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    // Search active FAQs by query (partial match in question or answer). Optionally limit to a category.
    public function searchActive($limit, $offset, $query, $category = null){
        $q = trim($query);
        if($q === '') return [];
        // create a loose LIKE pattern using words
        $parts = preg_split('/\s+/', $q);
        $like = '%' . implode('%', array_map(function($p){ return $p; }, $parts)) . '%';
        if($category && $this->columnExists('category')){
            $sql = "SELECT * FROM faqs WHERE status = 'active' AND category = :cat AND (question LIKE :q OR answer LIKE :q) ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $this->db->query($sql);
            $this->db->bind(':cat', $category);
        } else {
            $sql = "SELECT * FROM faqs WHERE status = 'active' AND (question LIKE :q OR answer LIKE :q) ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $this->db->query($sql);
        }
        $this->db->bind(':q', $like);
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    public function countSearchActive($query, $category = null){
        $q = trim($query);
        if($q === '') return 0;
        $parts = preg_split('/\s+/', $q);
        $like = '%' . implode('%', array_map(function($p){ return $p; }, $parts)) . '%';
        if($category && $this->columnExists('category')){
            $this->db->query("SELECT COUNT(*) as total FROM faqs WHERE status = 'active' AND category = :cat AND (question LIKE :q OR answer LIKE :q)");
            $this->db->bind(':cat', $category);
        } else {
            $this->db->query("SELECT COUNT(*) as total FROM faqs WHERE status = 'active' AND (question LIKE :q OR answer LIKE :q)");
        }
        $this->db->bind(':q', $like);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM faqs WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($data) {
        if($this->columnExists('category')){
            $this->db->query("INSERT INTO faqs (question, answer, status, category) VALUES (:question, :answer, :status, :category)");
            $this->db->bind(':category', isset($data['category']) ? $data['category'] : null);
        } else {
            $this->db->query("INSERT INTO faqs (question, answer, status) VALUES (:question, :answer, :status)");
        }
        $this->db->bind(':question', $data['question']);
        $this->db->bind(':answer', $data['answer']);
        $this->db->bind(':status', isset($data['status']) ? $data['status'] : 'active');
        return $this->db->execute();
    }

    public function update($id, $data) {
        if($this->columnExists('category')){
            $this->db->query("UPDATE faqs SET question = :question, answer = :answer, status = :status, category = :category WHERE id = :id");
            $this->db->bind(':category', isset($data['category']) ? $data['category'] : null);
        } else {
            $this->db->query("UPDATE faqs SET question = :question, answer = :answer, status = :status WHERE id = :id");
        }
        $this->db->bind(':question', $data['question']);
        $this->db->bind(':answer', $data['answer']);
        $this->db->bind(':status', isset($data['status']) ? $data['status'] : 'active');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM faqs WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getCategories(){
        // Prefer structured categories table if present
        try{
            $this->db->query("SHOW TABLES LIKE 'faq_categories'");
            $res = $this->db->resultSet();
            if(!empty($res)){
                // structured categories
                $this->db->query("SELECT id, title, slug, image FROM faq_categories ORDER BY title ASC");
                return $this->db->resultSet();
            }
        } catch(Exception $e){}

        if(!$this->columnExists('category')) return [];
        $this->db->query("SELECT DISTINCT category FROM faqs WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
        $rows = $this->db->resultSet();
        $cats = [];
        foreach($rows as $r){ if(is_object($r)) $cats[] = (object)['title'=>$r->category,'slug'=>$r->category,'image'=>null]; elseif(is_array($r)) $cats[] = (object)['title'=>$r['category'],'slug'=>$r['category'],'image'=>null]; }
        return $cats;
    }

    private function ensureCategoriesTableExists(){
        $sql = "CREATE TABLE IF NOT EXISTS faq_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            slug VARCHAR(150) NOT NULL UNIQUE,
            image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try{
            $this->db->query($sql);
            $this->db->execute();
        } catch(Exception $e){}
    }

    public function getAllCategories(){
        $this->ensureCategoriesTableExists();
        $this->db->query("SELECT id, title, slug, image FROM faq_categories ORDER BY title ASC");
        return $this->db->resultSet();
    }

    public function getCategoryById($id){
        $this->ensureCategoriesTableExists();
        $this->db->query("SELECT id, title, slug, image FROM faq_categories WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function createCategory($data){
        $this->ensureCategoriesTableExists();
        $title = isset($data['title']) ? trim($data['title']) : '';
        if(empty($title)){
            error_log('Faq::createCategory called with empty title');
            return false;
        }
        $slug = isset($data['slug']) ? $data['slug'] : $this->slugify($title);
        $image = isset($data['image']) ? $data['image'] : null;
        // ensure unique slug
        $base = $slug; $i=1;
        while($this->categorySlugExists($slug)){
            $slug = $base . '-' . $i; $i++;
        }
        try{
            $this->db->query("INSERT INTO faq_categories (title, slug, image) VALUES (:title, :slug, :image)");
            $this->db->bind(':title', $title);
            $this->db->bind(':slug', $slug);
            $this->db->bind(':image', $image);
            $ok = $this->db->execute();
            if($ok){
                $id = $this->db->lastInsertId();
                $this->writeDebug('createCategory success: id=' . $id . ' title=' . $title);
                return $id;
            }
            $this->writeDebug('createCategory execute returned false for title=' . $title);
            return false;
        } catch(Exception $e){
            $this->writeDebug('Faq::createCategory exception: ' . $e->getMessage());
            return false;
        }
    }

    public function updateCategory($id, $data){
        $this->ensureCategoriesTableExists();
        try{
            $this->db->query("UPDATE faq_categories SET title = :title, image = :image WHERE id = :id");
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':image', isset($data['image']) ? $data['image'] : null);
            $this->db->bind(':id', $id);
            $ok = $this->db->execute();
            if($ok) $this->writeDebug('updateCategory success: id=' . $id . ' title=' . $data['title']);
            else $this->writeDebug('updateCategory execute returned false: id=' . $id);
            return $ok;
        } catch(Exception $e){
            $this->writeDebug('Faq::updateCategory exception: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory($id){
        $this->ensureCategoriesTableExists();
        // unset category from faqs that referenced this slug
        $cat = $this->getCategoryById($id);
        if($cat && !empty($cat->slug)){
            $this->db->query("UPDATE faqs SET category = NULL WHERE category = :slug");
            $this->db->bind(':slug', $cat->slug);
            $this->db->execute();
        }
        try{
            $this->db->query("DELETE FROM faq_categories WHERE id = :id");
            $this->db->bind(':id', $id);
            $ok = $this->db->execute();
            if($ok) $this->writeDebug('deleteCategory success: id=' . $id);
            else $this->writeDebug('deleteCategory execute returned false: id=' . $id);
            return $ok;
        } catch(Exception $e){
            $this->writeDebug('Faq::deleteCategory exception: ' . $e->getMessage());
            return false;
        }
    }

    private function writeDebug($text){
        $root = dirname(APPROOT);
        $dir = $root . DIRECTORY_SEPARATOR . 'storage';
        if(!is_dir($dir)) @mkdir($dir, 0755, true);
        $file = $dir . DIRECTORY_SEPARATOR . 'faq_debug.log';
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $text . PHP_EOL;
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    private function categorySlugExists($slug){
        $this->ensureCategoriesTableExists();
        $this->db->query("SELECT COUNT(*) as c FROM faq_categories WHERE slug = :slug");
        $this->db->bind(':slug', $slug);
        $r = $this->db->single();
        return $r && $r->c > 0;
    }

    private function slugify($text){
        $text = preg_replace('~[^\pL0-9]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-a-zA-Z0-9]+~', '', $text);
        $text = strtolower(trim($text, '-'));
        if(empty($text)) return 'category-'.time();
        return $text;
    }
}
