<?php
class Controller {
    private static $publicSettingsCache = null;
    private static $adminNavBadgesCache = null;

    protected function getCsrfToken($key) {
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$key];
    }

    protected function verifyCsrf($key) {
        $expected = trim((string) ($_SESSION[$key] ?? ''));
        $fromPost = trim((string) ($_POST['csrf_token'] ?? ''));
        $fromHeader = trim((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        $submitted = $fromPost !== '' ? $fromPost : $fromHeader;
        if ($expected === '' || $submitted === '') {
            return false;
        }
        return hash_equals($expected, $submitted);
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    /**
     * Ghi nhận audit log có cấu trúc vào database và đồng bộ file log
     */
    protected function logAudit($eventType, $targetType = null, $targetId = null, $metadata = [], $actorUserId = null) {
        if ($actorUserId === null) {
            $actorUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        try {
            $auditModel = $this->model('AuditLog');
            $auditModel->recordEvent($eventType, $actorUserId, $targetType, $targetId, $ip, $metadata);
        } catch (Throwable $e) {
            error_log('[Controller::logAudit] Error recording audit log: ' . $e->getMessage());
        }

        if (function_exists('app_log')) {
            $logLevel = (strpos($eventType, 'failed') !== false || strpos($eventType, 'blocked') !== false) ? 'warning' : 'info';
            app_log($logLevel, $eventType, array_merge([
                'actor_user_id' => $actorUserId,
                'target_type'   => $targetType,
                'target_id'     => $targetId
            ], $metadata));
        }
    }

    protected function getPublicSettings() {
        if (self::$publicSettingsCache !== null) {
            return self::$publicSettingsCache;
        }

        try {
            $settingModel = $this->model('Setting');
            self::$publicSettingsCache = $settingModel->getPublicSettings();
        } catch (Throwable $error) {
            self::$publicSettingsCache = [];
        }

        return self::$publicSettingsCache;
    }

    protected function getAdminNavBadges() {
        if (self::$adminNavBadgesCache !== null) {
            return self::$adminNavBadgesCache;
        }

        $default = [
            'tickets' => 0,
            'users' => 0,
            'news' => 0,
            'notifications' => 0
        ];

        $adminUserId = (int) ($_SESSION['user_id'] ?? 0);
        if ($adminUserId <= 0 || (string) ($_SESSION['user_role'] ?? '') !== 'admin') {
            self::$adminNavBadgesCache = $default;
            return self::$adminNavBadgesCache;
        }

        try {
            $contactModel = $this->model('Contact');
            $userModel = $this->model('User');
            $adminNotificationModel = $this->model('AdminNotification');
            $settingModel = $this->model('Setting');

            $ticketCount = (int) $contactModel->countContacts(['status' => 'unread']);
            $newUsers = (int) $userModel->countNewUsersSince(30);
            $adminNotificationModel->syncRecentTicketNotifications(200);
            $adminNotificationModel->syncCompletedOrderNotifications(200);

            $stateRaw = $settingModel->getValueByKey('admin_notification_state_' . $adminUserId, '');
            $state = json_decode((string) $stateRaw, true);
            $lastOpenedId = max(0, (int) ($state['last_opened_id'] ?? 0));
            $lastOpenedAt = trim((string) ($state['last_opened_at'] ?? ''));
            if ($lastOpenedAt === '') {
                $legacyTicketSeenAt = trim((string) ($state['ticket_last_seen_at'] ?? ''));
                $legacyOrderSeenAt = trim((string) ($state['order_last_seen_at'] ?? ''));
                $lastOpenedAt = max($legacyTicketSeenAt, $legacyOrderSeenAt);
            }
            if ($lastOpenedAt === '') {
                $lastOpenedAt = '1970-01-01 00:00:00';
            }
            if ($lastOpenedId <= 0 && $lastOpenedAt !== '1970-01-01 00:00:00') {
                try {
                    $lastOpenedId = (int) $adminNotificationModel->getLatestNotificationIdByCreatedAt($lastOpenedAt);
                } catch (Throwable $error) {
                    $lastOpenedId = 0;
                }
            }

            $notificationCount = $lastOpenedId > 0
                ? (int) $adminNotificationModel->countUnreadSinceId($lastOpenedId)
                : (int) $adminNotificationModel->countUnreadSince($lastOpenedAt);

            self::$adminNavBadgesCache = [
                'tickets' => $ticketCount,
                'users' => $newUsers,
                'news' => 0,
                'notifications' => $notificationCount
            ];
        } catch (Throwable $error) {
            self::$adminNavBadgesCache = $default;
        }

        return self::$adminNavBadgesCache;
    }

    protected function getAdminNotificationToast() {
        $default = [
            'show' => false,
            'count' => 0
        ];

        $adminUserId = (int) ($_SESSION['user_id'] ?? 0);
        if ($adminUserId <= 0 || (string) ($_SESSION['user_role'] ?? '') !== 'admin') {
            return $default;
        }

        $sessionKey = 'admin_notification_toast_shown_' . $adminUserId;

        $badges = $this->getAdminNavBadges();
        $notifCount = max(0, (int) ($badges['notifications'] ?? 0));

        if ($notifCount <= 0) {
            unset($_SESSION[$sessionKey]);
            return $default;
        }

        $alreadyShown = !empty($_SESSION[$sessionKey]);
        if ($alreadyShown) {
            return $default;
        }

        $_SESSION[$sessionKey] = 1;
        return [
            'show' => true,
            'count' => $notifCount
        ];
    }

    public function view($view, $data = []) {
        if (!isset($data['public_settings']) && (strpos($view, 'client/') === 0 || strpos($view, 'admin/') === 0)) {
            $data['public_settings'] = $this->getPublicSettings();
        }
        if (!isset($data['csrf_admin']) && strpos($view, 'admin/') === 0) {
            $data['csrf_admin'] = $this->getCsrfToken('csrf_admin');
        }
        if (!isset($data['nav_badges']) && strpos($view, 'admin/') === 0) {
            $data['nav_badges'] = $this->getAdminNavBadges();
        }
        if (!isset($data['admin_notification_toast']) && strpos($view, 'admin/') === 0) {
            $data['admin_notification_toast'] = $this->getAdminNotificationToast();
        }

        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }
}