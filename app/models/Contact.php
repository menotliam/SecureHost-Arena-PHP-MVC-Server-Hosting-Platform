<?php
class Contact {
    private $db;
    private $allowedStatuses = ['unread', 'read', 'replied'];
    private $allowedPriorities = ['low', 'normal', 'high', 'urgent'];
    private $metaKeyPrefix = 'contact_ticket_meta_';

    /** Prefix subject trong DB để lọc theo loại ticket (admin). */
    public static function supportTicketSubjectPrefix() {
        return 'CA:';
    }

    /**
     * Nhãn loại ticket từ subject dạng CA:slug|...
     */
    public static function ticketTypeLabelFromSubject($subject) {
        $s = (string) $subject;
        if ($s === '' || !preg_match('/^CA:([^|]+)\|/', $s, $m)) {
            return '';
        }
        $slug = $m[1];
        $cats = (new self())->getSupportTicketCategories();
        return isset($cats[$slug]) ? $cats[$slug] : $slug;
    }

    /**
     * Mã đơn trong subject ticket "Vấn đề đơn hàng" (định dạng … Đơn #123).
     *
     * @return int 0 nếu không áp dụng
     */
    public static function purchaseComplaintOrderIdFromSubject($subject) {
        $s = (string) $subject;
        if ($s === '' || !preg_match('/^CA:purchase_issue\|/', $s)) {
            return 0;
        }
        if (preg_match('/Đơn\s*#\s*(\d+)/u', $s, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * Username trong subject ticket "Khóa tài khoản" (định dạng … — @username).
     */
    public static function bannedTicketUsernameFromSubject($subject) {
        $s = (string) $subject;
        if ($s === '' || !preg_match('/^CA:banned\|/', $s)) {
            return '';
        }
        if (preg_match('/ — @([a-zA-Z0-9._-]{1,50})$/u', $s, $m)) {
            return $m[1];
        }
        return '';
    }

    /**
     * Nội dung hiển thị trong luồng khách: bỏ khối meta cũ (--- SUPPORT META --- ... --- NỘI DUNG ---).
     */
    public static function customerMessageBodyForDisplay($storedMessage) {
        $raw = (string) $storedMessage;
        if (strpos($raw, '--- SUPPORT META ---') === false) {
            return trim($raw);
        }
        $marker = '--- NỘI DUNG ---';
        $pos = strpos($raw, $marker);
        if ($pos !== false) {
            return trim(substr($raw, $pos + strlen($marker)));
        }
        return trim($raw);
    }

    /**
     * Chuỗi hash mật khẩu trong meta (bcrypt hoặc argon2) — kiểm tra an toàn trước khi lưu / hiển thị admin.
     */
    public static function isStoredPasswordHashFormat($value) {
        $t = trim((string) $value);
        if ($t === '' || strlen($t) > 500) {
            return false;
        }
        if (preg_match('/^\$2[ayb]\$\d{2}\$/', $t)) {
            return true;
        }
        if (strncmp($t, '$argon2id$', 11) === 0 || strncmp($t, '$argon2i$', 10) === 0) {
            return true;
        }
        return false;
    }

    /**
     * Danh mục ticket (đồng bộ client + admin).
     *
     * @return array<string, string>
     */
    public function getSupportTicketCategories() {
        return [
            'purchase_issue' => 'Vấn đề đơn hàng',
            'forgot_password' => 'Quên mật khẩu',
            'bugs_technical' => 'Lỗi / Vấn đề kỹ thuật',
            'banned' => 'Khóa tài khoản / Blacklist',
            'billing_payment' => 'Thanh toán & Hóa đơn',
            'others' => 'Khác',
        ];
    }

    public function __construct() {
        $this->db = new Database;
    }

    public function getOrCreateGuestUserId() {
        $guestUsername = 'guest_contact';
        $guestEmail = 'guest@cloud-arena.local';

        $this->db->query('SELECT id FROM users WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $guestUsername);
        $existingUser = $this->db->single();
        if ($existingUser) {
            return (int) $existingUser->id;
        }

        $this->db->query(
            'INSERT INTO users (username, password, email, full_name, role, status)
             VALUES (:username, :password, :email, :full_name, :role, :status)'
        );
        $this->db->bind(':username', $guestUsername);
        $this->db->bind(':password', password_hash('guest-contact-placeholder', PASSWORD_DEFAULT));
        $this->db->bind(':email', $guestEmail);
        $this->db->bind(':full_name', 'Guest Contact');
        $this->db->bind(':role', 'member');
        $this->db->bind(':status', 'active');

        if (!$this->db->execute()) {
            return 0;
        }

        $this->db->query('SELECT id FROM users WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $guestUsername);
        $createdUser = $this->db->single();
        return $createdUser ? (int) $createdUser->id : 0;
    }

    private function getNextContactId($userId) {
        $this->db->query(
            'SELECT
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM contacts
                        WHERE user_id = :user_id_check AND contact_id = 1
                    ) THEN 1
                    ELSE (
                        SELECT MIN(c1.contact_id) + 1
                        FROM contacts c1
                        WHERE c1.user_id = :user_id_gap
                          AND NOT EXISTS (
                              SELECT 1
                              FROM contacts c2
                              WHERE c2.user_id = :user_id_gap_inner
                                AND c2.contact_id = c1.contact_id + 1
                          )
                    )
                END AS next_id'
        );
        $this->db->bind(':user_id_check', (int) $userId);
        $this->db->bind(':user_id_gap', (int) $userId);
        $this->db->bind(':user_id_gap_inner', (int) $userId);
        $row = $this->db->single();
        return ($row && (int) $row->next_id > 0) ? (int) $row->next_id : 1;
    }

    private function getMetaKey($userId, $contactId) {
        return $this->metaKeyPrefix . (int) $userId . '_' . (int) $contactId;
    }

    private function normalizeMeta($metaValue) {
        $defaultMeta = [
            'priority' => 'normal',
            'admin_reply' => null,
            'replied_at' => null,
            'previous_password_bcrypt' => null
        ];

        if (empty($metaValue)) {
            return $defaultMeta;
        }

        $decoded = json_decode($metaValue, true);
        if (!is_array($decoded)) {
            return $defaultMeta;
        }

        $priority = isset($decoded['priority']) && in_array($decoded['priority'], $this->allowedPriorities, true)
            ? $decoded['priority']
            : 'normal';

        $prevBcrypt = null;
        if (!empty($decoded['previous_password_bcrypt']) && is_string($decoded['previous_password_bcrypt'])) {
            $h = trim($decoded['previous_password_bcrypt']);
            if (self::isStoredPasswordHashFormat($h)) {
                $prevBcrypt = $h;
            }
        }

        return [
            'priority' => $priority,
            'admin_reply' => isset($decoded['admin_reply']) ? $decoded['admin_reply'] : null,
            'replied_at' => isset($decoded['replied_at']) ? $decoded['replied_at'] : null,
            'previous_password_bcrypt' => $prevBcrypt
        ];
    }

    private function applyMetaToContact($contact) {
        if (!$contact) {
            return null;
        }

        $meta = $this->normalizeMeta($contact->meta_value ?? null);
        $contact->priority = $meta['priority'];
        $contact->admin_reply = $meta['admin_reply'];
        $contact->replied_at = $meta['replied_at'];
        $contact->previous_password_bcrypt = $meta['previous_password_bcrypt'];
        unset($contact->meta_value);

        return $contact;
    }

    private function fetchMetaArray($userId, $contactId) {
        $this->db->query(
            'SELECT value FROM settings WHERE key_name = :key_name LIMIT 1'
        );
        $this->db->bind(':key_name', $this->getMetaKey($userId, $contactId));
        $row = $this->db->single();
        $raw = ($row && isset($row->value)) ? (string) $row->value : '';
        return $this->normalizeMeta($raw !== '' ? $raw : null);
    }

    /**
     * @param array<string, mixed> $metaOverrides Chỉ truyền key cần ghi đè, ví dụ previous_password_bcrypt khi tạo ticket.
     */
    private function upsertMeta($userId, $contactId, $priority = 'normal', $adminReply = null, $repliedAt = null, array $metaOverrides = []) {
        $cur = $this->fetchMetaArray($userId, $contactId);
        $safePriority = in_array($priority, $this->allowedPriorities, true) ? $priority : 'normal';

        $prevBcrypt = $cur['previous_password_bcrypt'] ?? null;
        if (array_key_exists('previous_password_bcrypt', $metaOverrides)) {
            $v = $metaOverrides['previous_password_bcrypt'];
            if ($v === null || $v === '') {
                $prevBcrypt = null;
            } elseif (is_string($v)) {
                $t = trim($v);
                $prevBcrypt = self::isStoredPasswordHashFormat($t) ? $t : null;
            }
        }

        $metaKey = $this->getMetaKey($userId, $contactId);
        $metaValue = json_encode([
            'priority' => $safePriority,
            'admin_reply' => $adminReply !== null ? trim((string) $adminReply) : null,
            'replied_at' => $repliedAt,
            'previous_password_bcrypt' => $prevBcrypt
        ]);

        $this->db->query(
            'INSERT INTO settings (key_name, value)
             VALUES (:key_name, :value)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $this->db->bind(':key_name', $metaKey);
        $this->db->bind(':value', $metaValue);

        return $this->db->execute();
    }

    private function buildFilterSql($filters, &$params) {
        $conditions = [];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], $this->allowedStatuses, true)) {
            $conditions[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['priority']) && in_array($filters['priority'], $this->allowedPriorities, true)) {
            $conditions[] = 's.value LIKE :priority_like';
            $params[':priority_like'] = '%"priority":"' . $filters['priority'] . '"%';
        }

        if (!empty($filters['keyword'])) {
            $conditions[] = '(c.name LIKE :keyword OR c.email LIKE :keyword OR c.subject LIKE :keyword OR c.message LIKE :keyword)';
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        $ticketCat = isset($filters['ticket_category']) ? trim((string) $filters['ticket_category']) : '';
        if ($ticketCat !== '') {
            $allowed = array_keys($this->getSupportTicketCategories());
            if (in_array($ticketCat, $allowed, true)) {
                $conditions[] = 'c.subject LIKE :ticket_cat_like';
                $params[':ticket_cat_like'] = self::supportTicketSubjectPrefix() . $ticketCat . '|%';
            }
        }

        if (empty($conditions)) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function createContact($data) {
        $userId = (int) $data['user_id'];
        $contactId = $this->getNextContactId($userId);
        $subject = isset($data['subject']) ? trim($data['subject']) : '';

        $this->db->query(
            'INSERT INTO contacts (user_id, contact_id, name, email, subject, message, status)
             VALUES (:user_id, :contact_id, :name, :email, :subject, :message, :status)'
        );
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':contact_id', $contactId);
        $this->db->bind(':name', trim($data['name']));
        $this->db->bind(':email', trim($data['email']));
        $this->db->bind(':subject', $subject);
        $this->db->bind(':message', trim($data['message']));
        $this->db->bind(':status', 'unread');

        if (!$this->db->execute()) {
            return false;
        }

        $metaOverrides = [];
        if (!empty($data['previous_password_bcrypt']) && is_string($data['previous_password_bcrypt'])) {
            $metaOverrides['previous_password_bcrypt'] = trim($data['previous_password_bcrypt']);
        }
        $this->upsertMeta($userId, $contactId, 'normal', null, null, $metaOverrides);
        $createdAt = '';
        $createdRow = $this->getContactByKey($userId, $contactId);
        if ($createdRow && !empty($createdRow->created_at)) {
            $createdAt = (string) $createdRow->created_at;
        }

        return [
            'user_id' => $userId,
            'contact_id' => $contactId,
            'created_at' => $createdAt
        ];
    }

    public function getContacts($filters = [], $page = 1, $perPage = 10) {
        $offset = max(0, ((int) $page - 1) * (int) $perPage);
        $params = [];
        $filterSql = $this->buildFilterSql($filters, $params);

        $sql = "SELECT c.user_id, c.contact_id, c.name, c.email, c.subject, c.message, c.status, c.created_at,
                       s.value AS meta_value, u.username AS user_name
                FROM contacts c
                LEFT JOIN settings s
                    ON s.key_name = CONCAT(:meta_prefix, c.user_id, '_', c.contact_id)
                LEFT JOIN users u
                    ON u.id = c.user_id" . $filterSql . "
                ORDER BY
                    CASE c.status
                        WHEN 'unread' THEN 1
                        WHEN 'read' THEN 2
                        WHEN 'replied' THEN 3
                        ELSE 4
                    END ASC,
                    CASE
                        WHEN s.value LIKE '%\"priority\":\"urgent\"%' THEN 1
                        WHEN s.value LIKE '%\"priority\":\"high\"%' THEN 2
                        WHEN s.value LIKE '%\"priority\":\"normal\"%' THEN 3
                        WHEN s.value LIKE '%\"priority\":\"low\"%' THEN 4
                        ELSE 3
                    END ASC,
                    c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        $this->db->bind(':meta_prefix', $this->metaKeyPrefix);
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        $this->db->bind(':limit', (int) $perPage);
        $this->db->bind(':offset', $offset);

        $rows = $this->db->resultSet();
        foreach ($rows as $row) {
            $this->applyMetaToContact($row);
        }

        return $rows;
    }

    public function countContacts($filters = []) {
        $params = [];
        $filterSql = $this->buildFilterSql($filters, $params);

        $sql = 'SELECT COUNT(*) AS total
                FROM contacts c
                LEFT JOIN settings s
                    ON s.key_name = CONCAT(:meta_prefix, c.user_id, "_", c.contact_id)' . $filterSql;

        $this->db->query($sql);
        $this->db->bind(':meta_prefix', $this->metaKeyPrefix);
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        $row = $this->db->single();

        return $row ? (int) $row->total : 0;
    }

    public function getContactByKey($userId, $contactId) {
        $this->db->query(
            'SELECT c.user_id, c.contact_id, c.name, c.email, c.subject, c.message, c.status, c.created_at,
                    s.value AS meta_value, u.username AS user_name
             FROM contacts c
             LEFT JOIN settings s
                ON s.key_name = CONCAT(:meta_prefix, c.user_id, "_", c.contact_id)
             LEFT JOIN users u
                ON u.id = c.user_id
             WHERE c.user_id = :user_id AND c.contact_id = :contact_id
             LIMIT 1'
        );
        $this->db->bind(':meta_prefix', $this->metaKeyPrefix);
        $this->db->bind(':user_id', (int) $userId);
        $this->db->bind(':contact_id', (int) $contactId);
        return $this->applyMetaToContact($this->db->single());
    }

    public function updateStatus($userId, $contactId, $status) {
        if (!in_array($status, $this->allowedStatuses, true)) {
            return false;
        }

        $this->db->query(
            'UPDATE contacts
             SET status = :status
             WHERE user_id = :user_id AND contact_id = :contact_id'
        );
        $this->db->bind(':status', $status);
        $this->db->bind(':user_id', (int) $userId);
        $this->db->bind(':contact_id', (int) $contactId);
        return $this->db->execute();
    }

    public function updatePriority($userId, $contactId, $priority) {
        $contact = $this->getContactByKey($userId, $contactId);
        if (!$contact) {
            return false;
        }

        return $this->upsertMeta(
            (int) $userId,
            (int) $contactId,
            $priority,
            $contact->admin_reply ?? null,
            $contact->replied_at ?? null
        );
    }

    public function replyToContact($userId, $contactId, $replyMessage) {
        $contact = $this->getContactByKey($userId, $contactId);
        if (!$contact) {
            return false;
        }

        $savedMeta = $this->upsertMeta(
            (int) $userId,
            (int) $contactId,
            $contact->priority ?? 'normal',
            trim($replyMessage),
            date('Y-m-d H:i:s')
        );

        if (!$savedMeta) {
            return false;
        }

        return $this->updateStatus($userId, $contactId, 'replied');
    }

    public function deleteContact($userId, $contactId) {
        $this->db->query(
            'DELETE FROM settings
             WHERE key_name = :key_name'
        );
        $this->db->bind(':key_name', $this->getMetaKey($userId, $contactId));
        $this->db->execute();

        $this->db->query(
            'DELETE FROM contacts
             WHERE user_id = :user_id AND contact_id = :contact_id'
        );
        $this->db->bind(':user_id', (int) $userId);
        $this->db->bind(':contact_id', (int) $contactId);
        return $this->db->execute();
    }

    public function getTicketNotificationSummary($sinceTimestamp = null, $limit = 5) {
        $safeSince = trim((string) $sinceTimestamp) !== '' ? trim((string) $sinceTimestamp) : '1970-01-01 00:00:00';
        $safeLimit = max(1, (int) $limit);

        $this->db->query(
            'SELECT COUNT(*) AS total, MAX(created_at) AS latest_created_at
             FROM contacts
             WHERE created_at > :since_timestamp'
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $meta = $this->db->single();
        $total = $meta ? (int) $meta->total : 0;
        $latestCreatedAt = ($meta && !empty($meta->latest_created_at)) ? (string) $meta->latest_created_at : null;

        $this->db->query(
            'SELECT user_id, contact_id, name, email, subject, status, created_at
             FROM contacts
             WHERE created_at > :since_timestamp
             ORDER BY created_at DESC
             LIMIT :limit_rows'
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $this->db->bind(':limit_rows', $safeLimit);
        $items = $this->db->resultSet();

        return [
            'count' => $total,
            'latest_created_at' => $latestCreatedAt,
            'items' => $items
        ];
    }

    public function getLatestContactCreatedAt() {
        $this->db->query(
            'SELECT MAX(created_at) AS latest_created_at
             FROM contacts'
        );
        $row = $this->db->single();
        if (!$row || empty($row->latest_created_at)) {
            return null;
        }
        return (string) $row->latest_created_at;
    }

    public function getRecentTicketNotifications($limit = 30) {
        $safeLimit = max(1, (int) $limit);
        $this->db->query(
            'SELECT user_id, contact_id, name, email, subject, status, created_at
             FROM contacts
             ORDER BY created_at DESC
             LIMIT :limit_rows'
        );
        $this->db->bind(':limit_rows', $safeLimit);
        return $this->db->resultSet();
    }

    public function countNewTicketNotificationsSince($sinceTimestamp = null) {
        $safeSince = trim((string) $sinceTimestamp) !== '' ? trim((string) $sinceTimestamp) : '1970-01-01 00:00:00';
        $this->db->query(
            'SELECT COUNT(*) AS total
             FROM contacts
             WHERE created_at > :since_timestamp'
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }
}