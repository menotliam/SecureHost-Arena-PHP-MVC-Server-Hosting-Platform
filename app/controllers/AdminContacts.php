<?php
class AdminContacts extends Controller {
    private $contactModel;
    private $userModel;
    private $allowedStatuses = ['unread', 'read', 'replied'];
    private $allowedPriorities = ['low', 'normal', 'high', 'urgent'];

    public function __construct() {
        $this->requireAdmin();
        $this->contactModel = $this->model('Contact');
        $this->userModel = $this->model('User');
    }

    private function requireAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
    }

    private function getRedirectUrl($clearTicketSelection = false) {
        $redirectQuery = trim($_POST['redirect_query'] ?? '');
        if ($redirectQuery === '') {
            return URLROOT . '/admincontacts';
        }

        $queryParams = [];
        parse_str(ltrim($redirectQuery, '?'), $queryParams);
        $queryParams = $this->sanitizeTicketQueryParams($queryParams);
        if ($clearTicketSelection) {
            unset($queryParams['user_id'], $queryParams['contact_id']);
        }
        if (empty($queryParams)) {
            return URLROOT . '/admincontacts';
        }
        return URLROOT . '/admincontacts?' . http_build_query($queryParams);
    }

    private function setFlash($type, $message) {
        $_SESSION['admin_contact_flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    private function isAjaxRequest() {
        $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        $ajaxFlag = (string) ($_GET['ajax'] ?? $_POST['ajax'] ?? '');
        return $requestedWith === 'xmlhttprequest' || strpos($accept, 'application/json') !== false || $ajaxFlag === '1';
    }

    private function respondJson($payload, $statusCode = 200) {
        http_response_code((int) $statusCode);
        header('Content-Type: application/json; charset=utf-8');
        $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $jsonOptions |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        $encoded = json_encode($payload, $jsonOptions);
        if ($encoded === false) {
            $encoded = json_encode([
                'success' => false,
                'message' => 'Không thể xử lý dữ liệu JSON.',
                'json_error' => function_exists('json_last_error_msg') ? json_last_error_msg() : 'unknown'
            ]);
        }
        echo $encoded;
        exit();
    }

    private function sanitizeTicketQueryParams($queryParams) {
        if (!is_array($queryParams)) {
            return [];
        }
        unset($queryParams['url'], $queryParams['ajax'], $queryParams['_ts']);
        return $queryParams;
    }

    private function buildTicketQueryString($userId, $contactId) {
        $queryParams = $this->sanitizeTicketQueryParams($_GET);
        $queryParams['user_id'] = (int) $userId;
        $queryParams['contact_id'] = (int) $contactId;
        return http_build_query($queryParams);
    }

    private function renderTicketDetailHtml($selectedContact, $queryString) {
        $priorities = $this->allowedPriorities;
        $csrfToken = $this->getCsrfToken('csrf_admin');
        $ticketCategoryLabels = $this->contactModel->getSupportTicketCategories();
        $purchaseComplaintOrderDisplay = '';
        $bannedTicketUsernameDisplay = '';
        if ($selectedContact) {
            $complaintOid = Contact::purchaseComplaintOrderIdFromSubject((string) ($selectedContact->subject ?? ''));
            if ($complaintOid > 0) {
                $orderModel = $this->model('Order');
                $orderRow = $orderModel->getOrderTicketSummaryByIdForUser($complaintOid, (int) $selectedContact->user_id);
                if ($orderRow) {
                    $purchaseComplaintOrderDisplay = '#' . (int) $orderRow->id
                        . ' — ' . date('d/m/Y', strtotime((string) $orderRow->created_at))
                        . ' — ' . number_format((float) $orderRow->total_amount, 0, ',', '.') . '₫';
                    $itemsLbl = trim((string) ($orderRow->items_label ?? ''));
                    if ($itemsLbl !== '') {
                        $purchaseComplaintOrderDisplay .= ' — ' . $itemsLbl;
                    }
                } else {
                    $purchaseComplaintOrderDisplay = 'Đơn #' . $complaintOid
                        . ' — không tìm thấy đơn khớp khách hàng này trong CSDL.';
                }
            }
            $bannedTicketUsernameDisplay = Contact::bannedTicketUsernameFromSubject((string) ($selectedContact->subject ?? ''));
        }
        ob_start();
        require APPROOT . '/views/admin/contacts/partials/ticket_detail.php';
        return ob_get_clean();
    }

    private function rejectTicketMutation($message, $statusCode = 422) {
        if ($this->isAjaxRequest()) {
            $this->respondJson([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }

        $this->setFlash('danger', $message);
        header('Location: ' . $this->getRedirectUrl());
        exit();
    }

    private function getNavBadges() {
        return $this->getAdminNavBadges();
    }

    public function index() {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 10;
        $filters = [
            'status' => trim($_GET['status'] ?? ''),
            'priority' => trim($_GET['priority'] ?? ''),
            'ticket_category' => trim($_GET['ticket_category'] ?? ''),
            'keyword' => trim($_GET['keyword'] ?? '')
        ];

        if (!in_array($filters['status'], $this->allowedStatuses, true)) {
            $filters['status'] = '';
        }
        if (!in_array($filters['priority'], $this->allowedPriorities, true)) {
            $filters['priority'] = '';
        }
        $allowedTicketCats = array_keys($this->contactModel->getSupportTicketCategories());
        if ($filters['ticket_category'] !== '' && !in_array($filters['ticket_category'], $allowedTicketCats, true)) {
            $filters['ticket_category'] = '';
        }

        $total = $this->contactModel->countContacts($filters);
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $contacts = $this->contactModel->getContacts($filters, $page, $perPage);
        $selectedUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        $selectedContactId = isset($_GET['contact_id']) ? (int) $_GET['contact_id'] : 0;
        $selectedContact = null;
        $missingTicketNotice = '';

        if ($selectedUserId > 0 && $selectedContactId > 0) {
            $selectedContact = $this->contactModel->getContactByKey($selectedUserId, $selectedContactId);
            if (!$selectedContact) {
                $missingTicketNotice = 'Không tìm thấy ticket: Ticket đã bị xóa.';
            }
        }

        if ($selectedContact && ($selectedContact->status ?? '') === 'unread') {
            if ($this->contactModel->updateStatus((int) $selectedContact->user_id, (int) $selectedContact->contact_id, 'read')) {
                $selectedContact->status = 'read';
                foreach ($contacts as $row) {
                    if ((int) $row->user_id === (int) $selectedContact->user_id
                        && (int) $row->contact_id === (int) $selectedContact->contact_id) {
                        $row->status = 'read';
                        break;
                    }
                }
            }
        }

        $flash = $_SESSION['admin_contact_flash'] ?? null;
        unset($_SESSION['admin_contact_flash']);

        $queryParams = $this->sanitizeTicketQueryParams($_GET);
        if ($selectedContact) {
            $queryParams['user_id'] = (int) $selectedContact->user_id;
            $queryParams['contact_id'] = (int) $selectedContact->contact_id;
        }
        $queryString = http_build_query($queryParams);

        $data = [
            'title' => 'Quản lý liên hệ',
            'subtitle' => '',
            'contacts' => $contacts,
            'selected_contact' => $selectedContact,
            'filters' => $filters,
            'statuses' => $this->allowedStatuses,
            'priorities' => $this->allowedPriorities,
            'ticket_category_labels' => $this->contactModel->getSupportTicketCategories(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage
            ],
            'query_string' => $queryString,
            'flash' => $flash,
            'missing_ticket_notice' => $missingTicketNotice,
            'nav_badges' => $this->getNavBadges()
        ];

        $this->view('admin/contacts/index', $data);
    }

    public function detail($userId = 0, $contactId = 0) {
        if (!$this->isAjaxRequest()) {
            header('Location: ' . URLROOT . '/admincontacts');
            exit();
        }

        $ticketUserId = (int) $userId;
        $ticketContactId = (int) $contactId;
        if ($ticketUserId <= 0 || $ticketContactId <= 0) {
            $this->respondJson(['success' => false, 'message' => 'Ticket không hợp lệ.'], 422);
        }

        $selectedContact = $this->contactModel->getContactByKey($ticketUserId, $ticketContactId);
        if (!$selectedContact) {
            $this->respondJson(['success' => false, 'message' => 'Không tìm thấy ticket: Ticket đã bị xóa.'], 404);
        }

        $statusUpdated = false;
        if (($selectedContact->status ?? '') === 'unread') {
            if ($this->contactModel->updateStatus($ticketUserId, $ticketContactId, 'read')) {
                $selectedContact->status = 'read';
                $statusUpdated = true;
            }
        }

        $queryString = $this->buildTicketQueryString($ticketUserId, $ticketContactId);
        $html = $this->renderTicketDetailHtml($selectedContact, $queryString);
        $this->respondJson([
            'success' => true,
            'html' => $html,
            'query_string' => $queryString,
            'status_updated' => $statusUpdated,
            'ticket_user_id' => $ticketUserId,
            'ticket_contact_id' => $ticketContactId,
            'new_status' => (string) ($selectedContact->status ?? '')
        ]);
    }

    public function updateStatus($userId = 0, $contactId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admincontacts');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->rejectTicketMutation('Yêu cầu không hợp lệ.', 403);
        }

        $contact = $this->contactModel->getContactByKey((int) $userId, (int) $contactId);
        if (!$contact) {
            $this->rejectTicketMutation('Không tìm thấy ticket.', 404);
        }
        if (($contact->status ?? '') === 'replied') {
            $this->rejectTicketMutation('Ticket đã phản hồi nên chỉ có thể xóa, không thể chỉnh sửa.');
        }

        $status = trim($_POST['status'] ?? '');
        if (!in_array($status, $this->allowedStatuses, true)) {
            if ($this->isAjaxRequest()) {
                $this->respondJson(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 422);
            }
            $this->setFlash('danger', 'Trạng thái không hợp lệ.');
            header('Location: ' . $this->getRedirectUrl());
            exit();
        }

        $updated = $this->contactModel->updateStatus((int) $userId, (int) $contactId, $status);
        if ($this->isAjaxRequest()) {
            $this->respondJson([
                'success' => (bool) $updated,
                'message' => $updated ? 'Trạng thái đã cập nhật (tự động).' : 'Không thể cập nhật trạng thái.'
            ], $updated ? 200 : 500);
        }
        $this->setFlash($updated ? 'success' : 'danger', $updated ? 'Cập nhật trạng thái thành công.' : 'Không thể cập nhật trạng thái.');

        header('Location: ' . $this->getRedirectUrl());
        exit();
    }

    public function updatePriority($userId = 0, $contactId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admincontacts');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->rejectTicketMutation('Yêu cầu không hợp lệ.', 403);
        }

        $contact = $this->contactModel->getContactByKey((int) $userId, (int) $contactId);
        if (!$contact) {
            $this->rejectTicketMutation('Không tìm thấy ticket.', 404);
        }
        if (($contact->status ?? '') === 'replied') {
            $this->rejectTicketMutation('Ticket đã phản hồi nên chỉ có thể xóa, không thể chỉnh sửa.');
        }

        $priority = trim($_POST['priority'] ?? '');
        if (!in_array($priority, $this->allowedPriorities, true)) {
            if ($this->isAjaxRequest()) {
                $this->respondJson(['success' => false, 'message' => 'Mức ưu tiên không hợp lệ.'], 422);
            }
            $this->setFlash('danger', 'Mức ưu tiên không hợp lệ.');
            header('Location: ' . $this->getRedirectUrl());
            exit();
        }

        $updated = $this->contactModel->updatePriority((int) $userId, (int) $contactId, $priority);
        if ($this->isAjaxRequest()) {
            $payload = [
                'success' => (bool) $updated,
                'message' => $updated ? 'Ưu tiên đã cập nhật (tự động).' : 'Không thể cập nhật mức ưu tiên.'
            ];
            if ($updated) {
                $payload['priority'] = $priority;
            }
            $this->respondJson($payload, $updated ? 200 : 500);
        }
        $this->setFlash($updated ? 'success' : 'danger', $updated ? 'Cập nhật mức ưu tiên thành công.' : 'Không thể cập nhật mức ưu tiên.');

        header('Location: ' . $this->getRedirectUrl());
        exit();
    }

    public function reply($userId = 0, $contactId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admincontacts');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->setFlash('danger', 'Yêu cầu không hợp lệ.');
            header('Location: ' . $this->getRedirectUrl());
            exit();
        }

        $contact = $this->contactModel->getContactByKey((int) $userId, (int) $contactId);
        if (!$contact) {
            $this->rejectTicketMutation('Không tìm thấy ticket.', 404);
        }
        if (($contact->status ?? '') === 'replied') {
            $this->rejectTicketMutation('Ticket đã phản hồi nên chỉ có thể xóa, không thể chỉnh sửa.');
        }

        $replyMessage = trim($_POST['reply_message'] ?? '');
        if ($replyMessage === '') {
            $this->setFlash('danger', 'Nội dung phản hồi không được để trống.');
            header('Location: ' . $this->getRedirectUrl());
            exit();
        }

        $saved = $this->contactModel->replyToContact((int) $userId, (int) $contactId, $replyMessage);
        $this->setFlash($saved ? 'success' : 'danger', $saved ? 'Đã lưu phản hồi và cập nhật trạng thái.' : 'Không thể lưu phản hồi.');

        header('Location: ' . $this->getRedirectUrl());
        exit();
    }

    public function delete($userId = 0, $contactId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/admincontacts');
            exit();
        }

        if (!$this->verifyCsrf('csrf_admin')) {
            $this->setFlash('danger', 'Yêu cầu không hợp lệ.');
            header('Location: ' . $this->getRedirectUrl(true));
            exit();
        }

        $deleted = $this->contactModel->deleteContact((int) $userId, (int) $contactId);
        $this->setFlash($deleted ? 'success' : 'danger', $deleted ? 'Đã xóa ticket liên hệ.' : 'Không thể xóa ticket.');

        header('Location: ' . $this->getRedirectUrl(true));
        exit();
    }
}