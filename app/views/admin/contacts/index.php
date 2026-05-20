<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$filters = $data['filters'] ?? ['status' => '', 'priority' => '', 'ticket_category' => '', 'keyword' => ''];
$pagination = $data['pagination'] ?? ['page' => 1, 'last_page' => 1, 'total' => 0];
$selectedContact = $data['selected_contact'] ?? null;
$queryString = $data['query_string'] ?? '';
$statuses = $data['statuses'] ?? [];
$priorities = $data['priorities'] ?? [];
$ticketCatLabels = $data['ticket_category_labels'] ?? [];
$baseParams = $_GET;
unset($baseParams['user_id'], $baseParams['contact_id'], $baseParams['page']);

$ticketTypeFromSubject = static function ($subject) use ($ticketCatLabels) {
    $s = (string) $subject;
    if ($s !== '' && preg_match('/^CA:([^|]+)\|/', $s, $m)) {
        $slug = $m[1];
        return isset($ticketCatLabels[$slug]) ? $ticketCatLabels[$slug] : $slug;
    }
    return '—';
};

$statusClasses = [
    'unread' => 'pill-status-unread',
    'read' => 'pill-status-read',
    'replied' => 'pill-status-replied'
];

$priorityClasses = [
    'low' => 'pill-priority-low',
    'normal' => 'pill-priority-normal',
    'high' => 'pill-priority-high',
    'urgent' => 'pill-priority-urgent'
];

$statusLabels = [
    'unread' => 'Chưa đọc',
    'read' => 'Đã đọc',
    'replied' => 'Đã phản hồi'
];

$priorityLabels = [
    'low' => 'Thấp',
    'normal' => 'Bình thường',
    'high' => 'Cao',
    'urgent' => 'Khẩn cấp'
];
?>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card ticket-filter-card">
            <div class="card-body">
                <div class="panel-header">
                    <h2 class="panel-title">Bộ lọc ticket</h2>
                </div>

                <?php if (!empty($data['flash'])): ?>
                    <div class="alert alert-<?php echo $data['flash']['type'] === 'success' ? 'success' : 'danger'; ?>">
                        <?php echo htmlspecialchars($data['flash']['message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($data['missing_ticket_notice'])): ?>
                    <div class="alert alert-warning">
                        <?php echo htmlspecialchars($data['missing_ticket_notice']); ?>
                    </div>
                <?php endif; ?>

                <form method="GET" action="<?php echo URLROOT; ?>/admincontacts">
                    <div class="row g-2 align-items-end ticket-filter-row">
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select name="status" id="status" class="form-select" data-admin-custom-select="true">
                                <option value="">Tất cả</option>
                                <?php foreach ($data['statuses'] as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($statusLabels[$status] ?? ucfirst($status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label for="priority" class="form-label">Độ ưu tiên</label>
                            <select name="priority" id="priority" class="form-select" data-admin-custom-select="true">
                                <option value="">Tất cả</option>
                                <?php foreach ($data['priorities'] as $priority): ?>
                                    <option value="<?php echo htmlspecialchars($priority); ?>" <?php echo $filters['priority'] === $priority ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($priorityLabels[$priority] ?? ucfirst($priority)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label for="ticket_category" class="form-label">Loại ticket</label>
                            <select name="ticket_category" id="ticket_category" class="form-select" data-admin-custom-select="true">
                                <option value="">Tất cả</option>
                                <?php foreach ($ticketCatLabels as $slug => $lab): ?>
                                    <option value="<?php echo htmlspecialchars($slug); ?>" <?php echo ($filters['ticket_category'] ?? '') === $slug ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lab); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-xl-4 col-lg-8 col-md-12">
                            <label for="keyword" class="form-label">Từ khóa</label>
                            <input
                                type="text"
                                id="keyword"
                                name="keyword"
                                class="form-control"
                                value="<?php echo htmlspecialchars($filters['keyword']); ?>"
                                placeholder="Tên, email, nội dung..."
                            >
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">Lọc</button>
                            <a href="<?php echo URLROOT; ?>/admincontacts" class="btn btn-outline-light w-100">Đặt lại</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <section class="card panel-card ticket-list-panel">
            <div class="card-body">
                <div class="panel-header">
                    <h2 class="panel-title">Danh sách ticket</h2>
                    <span class="badge badge-soft-primary">Tổng: <?php echo (int) $pagination['total']; ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table ticket-list-table">
                        <thead>
                            <tr>
                                <th>Người gửi</th>
                                <th>Loại ticket</th>
                                <th>Trạng thái</th>
                                <th>Ưu tiên</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['contacts'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center ticket-list-empty">Chưa có ticket phù hợp.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['contacts'] as $ticket): ?>
                                    <?php
                                    $rowParams = $baseParams;
                                    $rowParams['user_id'] = (int) $ticket->user_id;
                                    $rowParams['contact_id'] = (int) $ticket->contact_id;
                                    $rowUrl = URLROOT . '/admincontacts?' . http_build_query($rowParams);
                                    $isSelected = $selectedContact && (int) $selectedContact->user_id === (int) $ticket->user_id && (int) $selectedContact->contact_id === (int) $ticket->contact_id;
                                    ?>
                                    <tr
                                        class="ticket-row <?php echo $isSelected ? 'ticket-row-active' : ''; ?> <?php echo $ticket->status === 'replied' ? 'ticket-row-replied' : ''; ?>"
                                        data-ticket-user-id="<?php echo (int) $ticket->user_id; ?>"
                                        data-ticket-contact-id="<?php echo (int) $ticket->contact_id; ?>"
                                    >
                                        <td>
                                            <a
                                                href="<?php echo $rowUrl; ?>"
                                                class="text-decoration-none ticket-row-link"
                                                data-ticket-select="true"
                                                data-ticket-user-id="<?php echo (int) $ticket->user_id; ?>"
                                                data-ticket-contact-id="<?php echo (int) $ticket->contact_id; ?>"
                                            >
                                                <?php echo htmlspecialchars($ticket->name); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($ticket->email); ?></small>
                                            </a>
                                        </td>
                                        <td>
                                            <a
                                                href="<?php echo $rowUrl; ?>"
                                                class="text-decoration-none ticket-row-link ticket-type-cell"
                                                data-ticket-select="true"
                                                data-ticket-user-id="<?php echo (int) $ticket->user_id; ?>"
                                                data-ticket-contact-id="<?php echo (int) $ticket->contact_id; ?>"
                                            >
                                                <?php echo htmlspecialchars($ticketTypeFromSubject($ticket->subject ?? '')); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="pill-badge <?php echo $statusClasses[$ticket->status] ?? 'pill-status-read'; ?>">
                                                <?php echo htmlspecialchars($statusLabels[$ticket->status] ?? ucfirst($ticket->status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="pill-badge <?php echo $priorityClasses[$ticket->priority] ?? 'pill-priority-normal'; ?>">
                                                <?php echo htmlspecialchars($priorityLabels[$ticket->priority] ?? ucfirst($ticket->priority)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket->created_at))); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ((int) $pagination['last_page'] > 1): ?>
                    <nav aria-label="Phân trang liên hệ" class="mt-3">
                        <ul class="pagination mb-0">
                            <?php for ($p = 1; $p <= (int) $pagination['last_page']; $p++): ?>
                                <?php
                                $pageParams = $baseParams;
                                $pageParams['page'] = $p;
                                $pageUrl = URLROOT . '/admincontacts?' . http_build_query($pageParams);
                                ?>
                                <li class="page-item <?php echo (int) $pagination['page'] === $p ? 'active' : ''; ?>">
                                    <a class="page-link bg-transparent border-secondary text-light" href="<?php echo $pageUrl; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="card panel-card ticket-detail-card">
            <div class="card-body" id="ticketDetailContainer">
                <?php
                $ticketCategoryLabels = $data['ticket_category_labels'] ?? [];
                require APPROOT . '/views/admin/contacts/partials/ticket_detail.php';
                ?>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>