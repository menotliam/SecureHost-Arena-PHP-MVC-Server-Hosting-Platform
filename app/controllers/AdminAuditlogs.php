<?php
class AdminAuditlogs extends Controller {
    private $auditModel;

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URLROOT . '/users/login');
            exit();
        }
        $this->auditModel = $this->model('AuditLog');
    }

    public function index() {
        $filters = [
            'keyword'    => trim($_GET['keyword'] ?? ''),
            'event_type' => trim($_GET['event_type'] ?? ''),
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to'] ?? '')
        ];

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 15;

        $totalLogs = $this->auditModel->countLogs($filters);
        $lastPage = max(1, (int) ceil($totalLogs / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $logs = $this->auditModel->getLogs($filters, $page, $perPage);
        $eventTypes = $this->auditModel->getDistinctEventTypes();

        $data = [
            'title'       => 'Security Audit Logs',
            'logs'        => $logs,
            'filters'     => $filters,
            'event_types' => $eventTypes,
            'pagination'  => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $totalLogs,
                'last_page' => $lastPage
            ]
        ];

        $this->view('admin/audit_logs/index', $data);
    }
}
