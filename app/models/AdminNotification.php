<?php
require_once __DIR__ . '/Contact.php';

class AdminNotification {
    private $db;
    private $allowedTypes = ['ticket', 'revenue'];

    public function __construct() {
        $this->db = new Database;
    }

    public function createNotification($type, $sourceKey, $title, $message, $url = '', $createdAt = null, $payload = []) {
        $safeType = in_array($type, $this->allowedTypes, true) ? $type : 'ticket';
        $safeSourceKey = trim((string) $sourceKey);
        if ($safeSourceKey === '') {
            return false;
        }

        $this->db->query(
            'SELECT id
             FROM admin_notifications
             WHERE source_key = :source_key
             LIMIT 1'
        );
        $this->db->bind(':source_key', $safeSourceKey);
        $existing = $this->db->single();
        if ($existing) {
            return true;
        }

        $createdAtValue = trim((string) $createdAt);
        if ($createdAtValue === '') {
            $createdAtValue = date('Y-m-d H:i:s');
        }

        $encodedPayload = json_encode(is_array($payload) ? $payload : []);
        if ($encodedPayload === false) {
            $encodedPayload = '{}';
        }

        $this->db->query(
            'INSERT INTO admin_notifications (type, source_key, title, message, url, payload, created_at)
             VALUES (:type, :source_key, :title, :message, :url, :payload, :created_at)'
        );
        $this->db->bind(':type', $safeType);
        $this->db->bind(':source_key', $safeSourceKey);
        $this->db->bind(':title', trim((string) $title));
        $this->db->bind(':message', trim((string) $message));
        $this->db->bind(':url', trim((string) $url));
        $this->db->bind(':payload', $encodedPayload);
        $this->db->bind(':created_at', $createdAtValue);
        return $this->db->execute();
    }

    public function createTicketCreatedNotification($ticketData) {
        if (!is_array($ticketData)) {
            return false;
        }

        $userId = (int) ($ticketData['user_id'] ?? 0);
        $contactId = (int) ($ticketData['contact_id'] ?? 0);
        if ($userId <= 0 || $contactId <= 0) {
            return false;
        }

        $name = trim((string) ($ticketData['name'] ?? 'Khách hàng'));
        $subject = trim((string) ($ticketData['subject'] ?? ''));
        $email = trim((string) ($ticketData['email'] ?? ''));
        $typeLabel = trim((string) ($ticketData['category_label'] ?? ''));
        if ($typeLabel === '' && $subject !== '') {
            $typeLabel = Contact::ticketTypeLabelFromSubject($subject);
        }
        $createdAt = trim((string) ($ticketData['created_at'] ?? ''));
        if ($createdAt === '') {
            $createdAt = date('Y-m-d H:i:s');
        }
        $createdToken = date('YmdHis', strtotime($createdAt));
        if ($createdToken === '19700101000000') {
            $createdToken = date('YmdHis');
        }
        $sourceKey = 'ticket_created:' . $userId . ':' . $contactId . ':' . $createdToken;

        $summary = $typeLabel !== '' ? $typeLabel : 'Ticket mới';
        if ($email !== '') {
            $summary .= ' · ' . $email;
        }

        return $this->createNotification(
            'ticket',
            $sourceKey,
            'Ticket mới từ ' . ($name !== '' ? $name : 'Khách hàng'),
            $summary,
            '/admincontacts?user_id=' . $userId . '&contact_id=' . $contactId,
            $createdAt,
            [
                'user_id' => $userId,
                'contact_id' => $contactId,
                'created_at' => $createdAt
            ]
        );
    }

    public function syncCompletedOrderNotifications($limit = 200) {
        $safeLimit = max(1, (int) $limit);
        $this->db->query(
            "SELECT id, user_id, total_amount, created_at
             FROM orders
             WHERE status = 'completed'
             ORDER BY created_at DESC
             LIMIT :limit_rows"
        );
        $this->db->bind(':limit_rows', $safeLimit);
        $orders = $this->db->resultSet();

        foreach ($orders as $order) {
            $orderId = (int) ($order->id ?? 0);
            if ($orderId <= 0) {
                continue;
            }
            $this->createNotification(
                'revenue',
                'order_completed:' . $orderId,
                'Đơn hàng #' . $orderId . ' đã hoàn tất',
                'Khách #' . (int) ($order->user_id ?? 0) . ' thanh toán ' . number_format((float) ($order->total_amount ?? 0), 0, ',', '.') . 'đ.',
                '/admin',
                (string) ($order->created_at ?? ''),
                [
                    'order_id' => $orderId,
                    'user_id' => (int) ($order->user_id ?? 0),
                    'total_amount' => (float) ($order->total_amount ?? 0)
                ]
            );
        }
    }

    public function syncRecentTicketNotifications($limit = 200) {
        $safeLimit = max(1, (int) $limit);
        $this->db->query(
            "SELECT user_id, contact_id, name, email, subject, created_at
             FROM contacts
             ORDER BY created_at DESC
             LIMIT :limit_rows"
        );
        $this->db->bind(':limit_rows', $safeLimit);
        $tickets = $this->db->resultSet();

        foreach ($tickets as $ticket) {
            $this->createTicketCreatedNotification([
                'user_id' => (int) ($ticket->user_id ?? 0),
                'contact_id' => (int) ($ticket->contact_id ?? 0),
                'name' => (string) ($ticket->name ?? ''),
                'email' => (string) ($ticket->email ?? ''),
                'subject' => (string) ($ticket->subject ?? ''),
                'created_at' => (string) ($ticket->created_at ?? '')
            ]);
        }
    }

    public function getRecentNotifications($limit = 40) {
        $safeLimit = max(1, (int) $limit);
        $this->db->query(
            'SELECT id, type, source_key, title, message, url, payload, created_at
             FROM admin_notifications
             ORDER BY created_at DESC, id DESC
             LIMIT :limit_rows'
        );
        $this->db->bind(':limit_rows', $safeLimit);
        $rows = $this->db->resultSet();
        foreach ($rows as $row) {
            $decoded = json_decode((string) ($row->payload ?? ''), true);
            $row->payload = is_array($decoded) ? $decoded : [];
        }
        return $rows;
    }

    public function countUnreadSince($sinceTimestamp = null) {
        $safeSince = trim((string) $sinceTimestamp) !== '' ? trim((string) $sinceTimestamp) : '1970-01-01 00:00:00';
        $this->db->query(
            'SELECT COUNT(*) AS total
             FROM admin_notifications
             WHERE created_at > :since_timestamp'
        );
        $this->db->bind(':since_timestamp', $safeSince);
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    public function countUnreadSinceId($lastOpenedId = 0) {
        $safeLastId = max(0, (int) $lastOpenedId);
        $this->db->query(
            'SELECT COUNT(*) AS total
             FROM admin_notifications
             WHERE id > :last_opened_id'
        );
        $this->db->bind(':last_opened_id', $safeLastId);
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    public function getLatestNotificationCreatedAt() {
        $this->db->query(
            'SELECT MAX(created_at) AS latest_created_at
             FROM admin_notifications'
        );
        $row = $this->db->single();
        if (!$row || empty($row->latest_created_at)) {
            return null;
        }
        return (string) $row->latest_created_at;
    }

    public function getLatestNotificationId() {
        $this->db->query(
            'SELECT MAX(id) AS latest_id
             FROM admin_notifications'
        );
        $row = $this->db->single();
        return $row ? (int) ($row->latest_id ?? 0) : 0;
    }

    public function getLatestNotificationIdByCreatedAt($timestamp) {
        $safeTimestamp = trim((string) $timestamp);
        if ($safeTimestamp === '') {
            return 0;
        }

        $this->db->query(
            'SELECT COALESCE(MAX(id), 0) AS latest_id
             FROM admin_notifications
             WHERE created_at <= :created_at'
        );
        $this->db->bind(':created_at', $safeTimestamp);
        $row = $this->db->single();
        return $row ? (int) ($row->latest_id ?? 0) : 0;
    }
}