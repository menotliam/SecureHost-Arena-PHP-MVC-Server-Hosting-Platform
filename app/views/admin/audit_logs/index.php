<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$logs = $data['logs'] ?? [];
$filters = $data['filters'] ?? ['keyword' => '', 'event_type' => '', 'date_from' => '', 'date_to' => ''];
$pagination = $data['pagination'] ?? ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1];
$eventTypes = $data['event_types'] ?? [];

$getBadgeClass = function ($eventType) {
    switch ($eventType) {
        case 'login_success':
        case 'password_changed':
            return 'bg-success text-white';
        case 'login_failed':
        case 'login_blocked_lockout':
            return 'bg-danger text-white';
        case 'login_blocked_banned':
        case 'user_status_changed':
        case 'user_role_changed':
            return 'bg-warning text-dark';
        case 'ticket_status_changed':
        case 'ticket_replied':
            return 'bg-info text-white';
        case 'profile_updated':
        case 'avatar_updated':
            return 'bg-primary text-white';
        case 'user_deleted':
        case 'ticket_deleted':
            return 'bg-dark text-white';
        default:
            return 'bg-secondary text-white';
    }
};

$formatEventLabel = function ($eventType) {
    $map = [
        'login_success'         => 'Đăng nhập thành công',
        'login_failed'          => 'Đăng nhập thất bại',
        'login_blocked_banned'  => 'Chặn: TK bị khóa',
        'login_blocked_lockout' => 'Chặn: Brute-force Lockout',
        'logout'                => 'Đăng xuất',
        'profile_updated'       => 'Cập nhật hồ sơ',
        'password_changed'      => 'Đổi mật khẩu',
        'avatar_updated'        => 'Đổi avatar',
        'user_role_changed'     => 'Đổi quyền thành viên',
        'user_status_changed'   => 'Đổi trạng thái tài khoản',
        'user_deleted'          => 'Xóa tài khoản',
        'admin_reset_password'  => 'Admin reset mật khẩu',
        'ticket_status_changed' => 'Đổi trạng thái ticket',
        'ticket_replied'        => 'Phản hồi ticket',
        'ticket_deleted'        => 'Xóa ticket'
    ];
    return $map[$eventType] ?? $eventType;
};
?>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title"><i class="ti-shield text-primary me-2"></i>Nhật ký Kiểm toán & Bảo mật</h2>
                        <p class="panel-muted small mt-1 mb-0">Theo dõi toàn bộ sự kiện xác thực, chống brute-force và thao tác thay đổi dữ liệu của Quản trị viên.</p>
                    </div>
                    <span class="badge badge-soft-primary">
                        Tổng số log: <?php echo number_format((int) $pagination['total']); ?>
                    </span>
                </div>

                <!-- Form lọc -->
                <form method="GET" action="<?php echo URLROOT; ?>/admin/auditlogs" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Tìm IP, Username, Metadata..."
                            value="<?php echo htmlspecialchars($filters['keyword']); ?>"
                        >
                    </div>
                    <div class="col-md-3">
                        <select name="event_type" class="form-select">
                            <option value="">-- Tất cả sự kiện --</option>
                            <?php foreach ($eventTypes as $et): ?>
                                <?php $t = $et->event_type ?? ''; ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $filters['event_type'] === $t ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t . ' (' . $formatEventLabel($t) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            placeholder="Từ ngày"
                            value="<?php echo htmlspecialchars($filters['date_from']); ?>"
                            title="Từ ngày"
                        >
                    </div>
                    <div class="col-md-2">
                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            placeholder="Đến ngày"
                            value="<?php echo htmlspecialchars($filters['date_to']); ?>"
                            title="Đến ngày"
                        >
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ti-search"></i> Lọc
                        </button>
                        <a href="<?php echo URLROOT; ?>/admin/auditlogs" class="btn btn-outline-light" title="Đặt lại bộ lọc">
                            <i class="ti-reload"></i>
                        </a>
                    </div>
                </form>

                <!-- Bảng danh sách log -->
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th style="width: 160px;">Thời gian</th>
                                <th style="width: 200px;">Sự kiện</th>
                                <th style="width: 180px;">Tác nhân (Actor)</th>
                                <th style="width: 140px;">Địa chỉ IP</th>
                                <th style="width: 140px;">Đối tượng</th>
                                <th>Chi tiết (Metadata)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="ti-info-alt fs-4 d-block mb-2"></i>
                                        Không tìm thấy bản ghi nhật ký kiểm toán nào phù hợp.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                    $metaObj = !empty($log->metadata) ? json_decode($log->metadata, true) : null;
                                    $hasMeta = is_array($metaObj) && !empty($metaObj);
                                    ?>
                                    <tr>
                                        <td><span class="text-muted small">#<?php echo (int) $log->id; ?></span></td>
                                        <td>
                                            <span class="small font-monospace">
                                                <?php echo date('d/m/Y H:i:s', strtotime($log->created_at)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $getBadgeClass($log->event_type); ?> px-2 py-1">
                                                <?php echo htmlspecialchars($log->event_type); ?>
                                            </span>
                                            <div class="small text-muted mt-1">
                                                <?php echo htmlspecialchars($formatEventLabel($log->event_type)); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($log->actor_user_id)): ?>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($log->actor_username ?? ('User #' . $log->actor_user_id)); ?>
                                                </div>
                                                <span class="badge badge-soft-primary small">
                                                    <?php echo htmlspecialchars($log->actor_role ?? 'member'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary text-white">Khách vãng lai / Hệ thống</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code class="small font-monospace"><?php echo htmlspecialchars($log->ip_address); ?></code>
                                        </td>
                                        <td>
                                            <?php if (!empty($log->target_type)): ?>
                                                <span class="badge badge-soft-warning">
                                                    <?php echo htmlspecialchars($log->target_type); ?><?php echo !empty($log->target_id) ? ' #' . (int) $log->target_id : ''; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($hasMeta): ?>
                                                <div class="small">
                                                    <?php foreach ($metaObj as $k => $v): ?>
                                                        <span class="badge bg-secondary text-white me-1 mb-1 font-monospace">
                                                            <strong><?php echo htmlspecialchars($k); ?>:</strong>
                                                            <?php echo htmlspecialchars(is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($pagination['last_page'] > 1): ?>
                    <nav class="mt-3" aria-label="Phân trang nhật ký">
                        <ul class="pagination justify-content-center mb-0">
                            <?php
                            $queryParams = $filters;
                            $buildPageUrl = function ($p) use ($queryParams) {
                                $queryParams['page'] = $p;
                                return URLROOT . '/admin/auditlogs?' . http_build_query($queryParams);
                            };
                            $currentPage = (int) $pagination['page'];
                            $lastPage = (int) $pagination['last_page'];
                            ?>

                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $buildPageUrl($currentPage - 1); ?>" tabindex="-1">Trước</a>
                            </li>

                            <?php for ($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++): ?>
                                <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $buildPageUrl($p); ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo $currentPage >= $lastPage ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo $buildPageUrl($currentPage + 1); ?>">Sau</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
