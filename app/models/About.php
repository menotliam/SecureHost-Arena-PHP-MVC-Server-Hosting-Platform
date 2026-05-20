<?php
class About {
    private $db;
    public function __construct() {
        $this->db = new Database;
    }

    private $columnsCache = null;

    private function columnExists($col){
        if($this->columnsCache === null){
            $this->db->query("SHOW COLUMNS FROM about");
            $rows = $this->db->resultset();
            $this->columnsCache = [];
            foreach($rows as $r){
                // support both numeric and associative array return types
                if(is_array($r)){
                    $this->columnsCache[] = array_values($r)[0];
                } elseif(is_object($r)){
                    $props = get_object_vars($r);
                    $this->columnsCache[] = array_values($props)[0];
                }
            }
        }
        return in_array($col, $this->columnsCache);
    }

    public function get() {
        // Always return the canonical about row (id = 1) if exists, otherwise return latest as fallback
        $this->db->query("SELECT * FROM about WHERE id = 1 LIMIT 1");
        $row = $this->db->single();
        if($row) return $row;
        // fallback to latest row if id=1 not present
        $this->db->query("SELECT * FROM about ORDER BY id DESC LIMIT 1");
        return $this->db->single();
    }

    public function update($data) {
        $sql = "UPDATE about SET title = :title, subtitle = :subtitle, services_heading = :services_heading, partners_heading = :partners_heading, modpacks_heading = :modpacks_heading, gallery_heading = :gallery_heading, content = :content, image = :image, admin_id = :admin_id, uptime = :uptime, support = :support, performance = :performance, years_active = :years_active, founded_year = :founded_year, partners = :partners, modpacks = :modpacks, gallery = :gallery, services = :services, cta_heading = :cta_heading, cta_text = :cta_text, cta_button_text = :cta_button_text, cta_button_url = :cta_button_url";
        // optional visual/background/intro columns
        if($this->columnExists('background')) $sql .= ", background = :background";
        if($this->columnExists('intro_gif')) $sql .= ", intro_gif = :intro_gif";
        if($this->columnExists('intro_duration')) $sql .= ", intro_duration = :intro_duration";
        // optional columns
        if($this->columnExists('uptime_icon')) $sql .= ", uptime_icon = :uptime_icon";
        if($this->columnExists('support_icon')) $sql .= ", support_icon = :support_icon";
        if($this->columnExists('performance_icon')) $sql .= ", performance_icon = :performance_icon";
        if($this->columnExists('sections')) $sql .= ", sections = :sections";
        $sql .= " WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':image', isset($data['image']) ? $data['image'] : null);
        $this->db->bind(':admin_id', isset($data['admin_id']) ? $data['admin_id'] : null);
        $this->db->bind(':uptime', isset($data['uptime']) ? $data['uptime'] : null);
        $this->db->bind(':support', isset($data['support']) ? $data['support'] : null);
        $this->db->bind(':performance', isset($data['performance']) ? $data['performance'] : null);
        $this->db->bind(':years_active', isset($data['years_active']) ? $data['years_active'] : null);
        $this->db->bind(':founded_year', isset($data['founded_year']) ? $data['founded_year'] : null);
        $this->db->bind(':partners', isset($data['partners']) ? $data['partners'] : null);
        $this->db->bind(':modpacks', isset($data['modpacks']) ? $data['modpacks'] : null);
        $this->db->bind(':gallery', isset($data['gallery']) ? $data['gallery'] : null);
        $this->db->bind(':services', isset($data['services']) ? $data['services'] : null);
        $this->db->bind(':cta_heading', isset($data['cta_heading']) ? $data['cta_heading'] : null);
        $this->db->bind(':cta_text', isset($data['cta_text']) ? $data['cta_text'] : null);
        $this->db->bind(':cta_button_text', isset($data['cta_button_text']) ? $data['cta_button_text'] : null);
        $this->db->bind(':cta_button_url', isset($data['cta_button_url']) ? $data['cta_button_url'] : null);
        $this->db->bind(':subtitle', isset($data['subtitle']) ? $data['subtitle'] : null);
        $this->db->bind(':services_heading', isset($data['services_heading']) ? $data['services_heading'] : null);
        $this->db->bind(':partners_heading', isset($data['partners_heading']) ? $data['partners_heading'] : null);
        $this->db->bind(':modpacks_heading', isset($data['modpacks_heading']) ? $data['modpacks_heading'] : null);
        $this->db->bind(':gallery_heading', isset($data['gallery_heading']) ? $data['gallery_heading'] : null);
        // optional binds
        if($this->columnExists('uptime_icon')) $this->db->bind(':uptime_icon', isset($data['uptime_icon']) ? $data['uptime_icon'] : null);
        if($this->columnExists('support_icon')) $this->db->bind(':support_icon', isset($data['support_icon']) ? $data['support_icon'] : null);
        if($this->columnExists('performance_icon')) $this->db->bind(':performance_icon', isset($data['performance_icon']) ? $data['performance_icon'] : null);
        if($this->columnExists('sections')) $this->db->bind(':sections', isset($data['sections']) ? $data['sections'] : null);
        // optional background/intro binds
        if($this->columnExists('background')) $this->db->bind(':background', isset($data['background']) ? $data['background'] : null);
        if($this->columnExists('intro_gif')) $this->db->bind(':intro_gif', isset($data['intro_gif']) ? $data['intro_gif'] : null);
        if($this->columnExists('intro_duration')) $this->db->bind(':intro_duration', isset($data['intro_duration']) ? $data['intro_duration'] : null);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function create($data) {
        $cols = [
            'title','subtitle','services_heading','partners_heading','modpacks_heading','gallery_heading','content','image','admin_id','uptime','support','performance','years_active','founded_year','partners','modpacks','gallery','services','cta_heading','cta_text','cta_button_text','cta_button_url','background','intro_gif','intro_duration'
        ];
        // optional columns
        if($this->columnExists('uptime_icon')) $cols[] = 'uptime_icon';
        if($this->columnExists('support_icon')) $cols[] = 'support_icon';
        if($this->columnExists('performance_icon')) $cols[] = 'performance_icon';
        if($this->columnExists('sections')) $cols[] = 'sections';

        $placeholders = array_map(function($c){ return ':' . $c; }, $cols);
        // support explicit id insertion when provided (used by upsert)
        if(isset($data['id']) && $data['id']){
            array_unshift($cols, 'id');
            array_unshift($placeholders, ':id');
        }
        $this->db->query("INSERT INTO about (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")");
        if(isset($data['id']) && $data['id']) $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':image', isset($data['image']) ? $data['image'] : null);
        $this->db->bind(':admin_id', isset($data['admin_id']) ? $data['admin_id'] : null);
        $this->db->bind(':uptime', isset($data['uptime']) ? $data['uptime'] : null);
        $this->db->bind(':support', isset($data['support']) ? $data['support'] : null);
        $this->db->bind(':performance', isset($data['performance']) ? $data['performance'] : null);
        $this->db->bind(':years_active', isset($data['years_active']) ? $data['years_active'] : null);
        $this->db->bind(':founded_year', isset($data['founded_year']) ? $data['founded_year'] : null);
        $this->db->bind(':partners', isset($data['partners']) ? $data['partners'] : null);
        $this->db->bind(':modpacks', isset($data['modpacks']) ? $data['modpacks'] : null);
        $this->db->bind(':gallery', isset($data['gallery']) ? $data['gallery'] : null);
        $this->db->bind(':services', isset($data['services']) ? $data['services'] : null);
        $this->db->bind(':cta_heading', isset($data['cta_heading']) ? $data['cta_heading'] : null);
        $this->db->bind(':cta_text', isset($data['cta_text']) ? $data['cta_text'] : null);
        $this->db->bind(':cta_button_text', isset($data['cta_button_text']) ? $data['cta_button_text'] : null);
        $this->db->bind(':cta_button_url', isset($data['cta_button_url']) ? $data['cta_button_url'] : null);
        $this->db->bind(':services_heading', isset($data['services_heading']) ? $data['services_heading'] : null);
        $this->db->bind(':partners_heading', isset($data['partners_heading']) ? $data['partners_heading'] : null);
        $this->db->bind(':modpacks_heading', isset($data['modpacks_heading']) ? $data['modpacks_heading'] : null);
        $this->db->bind(':gallery_heading', isset($data['gallery_heading']) ? $data['gallery_heading'] : null);
        $this->db->bind(':subtitle', isset($data['subtitle']) ? $data['subtitle'] : null);
        // optional background/intro binds for create
        if($this->columnExists('background')) $this->db->bind(':background', isset($data['background']) ? $data['background'] : null);
        if($this->columnExists('intro_gif')) $this->db->bind(':intro_gif', isset($data['intro_gif']) ? $data['intro_gif'] : null);
        if($this->columnExists('intro_duration')) $this->db->bind(':intro_duration', isset($data['intro_duration']) ? $data['intro_duration'] : null);
        if($this->columnExists('uptime_icon')) $this->db->bind(':uptime_icon', isset($data['uptime_icon']) ? $data['uptime_icon'] : null);
        if($this->columnExists('support_icon')) $this->db->bind(':support_icon', isset($data['support_icon']) ? $data['support_icon'] : null);
        if($this->columnExists('performance_icon')) $this->db->bind(':performance_icon', isset($data['performance_icon']) ? $data['performance_icon'] : null);
        if($this->columnExists('sections')) $this->db->bind(':sections', isset($data['sections']) ? $data['sections'] : null);
        $ok = $this->db->execute();
        if($ok){
            return $this->db->lastInsertId();
        }
        return false;
    }

/**
     * Upsert the canonical about row (ensure id=1 is used)
     */
    public function upsert($data){
        $id = 1;
        $data['id'] = $id;

        // If payload does not include a valid title, preserve existing title from DB
        if(!isset($data['title']) || $data['title'] === null || $data['title'] === ''){
            $existing = $this->get();
            if($existing && isset($existing->title) && $existing->title !== ''){
                $data['title'] = $existing->title;
            } else {
                // ensure at least empty string (validation should prevent saving empty title)
                $data['title'] = '';
            }
        }

        // 1. Kiểm tra xem dòng id=1 đã tồn tại trong Database chưa
        $this->db->query("SELECT id FROM about WHERE id = :id");
        $this->db->bind(':id', $id);
        $exists = $this->db->single();

        // 2. Danh sách các cột cần lưu
        $cols = [
            'title','subtitle','services_heading','partners_heading','modpacks_heading','gallery_heading','content','image','admin_id','uptime','support','performance','years_active','founded_year','partners','modpacks','gallery','services','cta_heading','cta_text','cta_button_text','cta_button_url','background','intro_gif','intro_duration'
        ];
        if($this->columnExists('uptime_icon')) $cols[] = 'uptime_icon';
        if($this->columnExists('support_icon')) $cols[] = 'support_icon';
        if($this->columnExists('performance_icon')) $cols[] = 'performance_icon';
        if($this->columnExists('sections')) $cols[] = 'sections';

        if($exists){
            // 3A. Đã tồn tại -> Chạy lệnh UPDATE truyền thống (An toàn 100%)
            $setClause = [];
            foreach($cols as $c){
                $setClause[] = "`$c` = :$c";
            }
            $sql = "UPDATE about SET " . implode(', ', $setClause) . " WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            foreach($cols as $c){
                $this->db->bind(':' . $c, isset($data[$c]) ? $data[$c] : null);
            }
            $ok = $this->db->execute();
            return $ok ? $id : false;
        } else {
            // 3B. Chưa tồn tại -> Chạy lệnh INSERT truyền thống
            $allCols = array_merge(['id'], $cols);
            $placeholders = array_map(function($c){ return ':' . $c; }, $allCols);
            $sql = "INSERT INTO about (`" . implode('`, `', $allCols) . "`) VALUES (" . implode(', ', $placeholders) . ")";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            foreach($cols as $c){
                $this->db->bind(':' . $c, isset($data[$c]) ? $data[$c] : null);
            }
            $ok = $this->db->execute();
            return $ok ? $id : false;
        }
    }

    public function getById($id){
        $this->db->query("SELECT * FROM about WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getAll(){
        $this->db->query("SELECT * FROM about ORDER BY id DESC");
        return $this->db->resultSet();
    }

    public function cleanupEmptyTitles($keepId = null){
        // remove rows that have empty title, but keep the specified id
        $sql = "DELETE FROM about WHERE (title = '' OR title IS NULL)";
        if($keepId){ $sql .= " AND id != :keepId"; }
        $this->db->query($sql);
        if($keepId) $this->db->bind(':keepId', $keepId);
        return $this->db->execute();
    }
}
