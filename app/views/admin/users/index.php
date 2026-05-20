<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$users = $data['users'] ?? [];
$filters = $data['filters'] ?? ['keyword' => '', 'status' => '', 'role' => ''];
$pagination = $data['pagination'] ?? ['page' => 1, 'last_page' => 1, 'total' => 0];
$summary = $data['summary'] ?? ['total' => 0, 'active' => 0, 'banned' => 0];
$statusLabels = [
    'active' => 'Hoạt động',
    'banned' => 'Đã khóa'
];
$resolveAvatarUrl = function ($avatarValue) {
    $raw = trim((string) $avatarValue);
    if ($raw === '') {
        return '';
    }
    if (strpos($raw, 'http://') === 0 || strpos($raw, 'https://') === 0) {
        return $raw;
    }
    if (strpos($raw, '/uploads/') === 0) {
        return URLROOT . $raw;
    }
    if (strpos($raw, 'uploads/') === 0) {
        return URLROOT . '/' . ltrim($raw, '/');
    }
    return URLROOT . '/uploads/avatars/' . ltrim($raw, '/');
};
?>

<?php if (!empty($data['flash'])): ?>
    <div class="alert alert-<?php echo $data['flash']['type'] === 'success' ? 'success' : 'danger'; ?>">
        <?php echo htmlspecialchars($data['flash']['message']); ?>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <article class="user-summary-card user-summary-total">
            <p>Tổng thành viên</p>
            <h3><?php echo (int) $summary['total']; ?></h3>
        </article>
    </div>
    <div class="col-md-4">
        <article class="user-summary-card user-summary-active">
            <p>Đang hoạt động</p>
            <h3><?php echo (int) $summary['active']; ?></h3>
        </article>
    </div>
    <div class="col-md-4">
        <article class="user-summary-card user-summary-banned">
            <p>Đã khóa</p>
            <h3><?php echo (int) $summary['banned']; ?></h3>
        </article>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header">
                    <h2 class="panel-title">Tìm kiếm & bộ lọc</h2>
                </div>
                <form method="GET" action="<?php echo URLROOT; ?>/admin/users" class="row g-2 align-items-end user-filter-row">
                    <div class="col-lg-4">
                        <label for="keyword" class="form-label">Từ khóa</label>
                        <input type="text" id="keyword" name="keyword" class="form-control" placeholder="Tên, username hoặc email" value="<?php echo htmlspecialchars($filters['keyword']); ?>">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select id="status" name="status" class="form-select" data-admin-custom-select="true">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="banned" <?php echo $filters['status'] === 'banned' ? 'selected' : ''; ?>>Đã khóa</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="role" class="form-label">Vai trò</label>
                        <select id="role" name="role" class="form-select" data-admin-custom-select="true">
                            <option value="">Tất cả vai trò</option>
                            <option value="member" <?php echo $filters['role'] === 'member' ? 'selected' : ''; ?>>Thành viên</option>
                            <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Áp dụng</button>
                    </div>
                    <div class="col-lg-2 col-md-2 d-grid">
                        <a href="<?php echo URLROOT; ?>/admin/users" class="btn btn-outline-light">Đặt lại</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header">
                    <h2 class="panel-title">Danh sách người dùng</h2>
                    <span class="badge badge-soft-primary">Tổng: <?php echo (int) $pagination['total']; ?></span>
                </div>
                <div class="table-responsive users-table-wrap">
                    <table class="table admin-table users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tham gia</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không tìm thấy người dùng phù hợp.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $displayName = $user->full_name ?: $user->username;
                                    $words = preg_split('/\s+/', trim($displayName));
                                    $initials = '';
                                    foreach ($words as $word) {
                                        if ($word !== '') {
                                            $initials .= strtoupper(substr($word, 0, 1));
                                        }
                                        if (strlen($initials) >= 2) {
                                            break;
                                        }
                                    }
                                    if ($initials === '') {
                                        $initials = strtoupper(substr($user->username, 0, 1));
                                    }
                                    $avatarUrl = $resolveAvatarUrl($user->avatar ?? '');
                                    ?>
                                    <tr>
                                        <td>#<?php echo (int) $user->id; ?></td>
                                        <td>
                                            <div class="user-cell">
                                                <?php if ($avatarUrl !== ''): ?>
                                                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar người dùng" class="user-avatar-image">
                                                <?php else: ?>
                                                    <span class="user-avatar-placeholder"><?php echo htmlspecialchars($initials); ?></span>
                                                <?php endif; ?>
                                                <div class="user-meta">
                                                    <strong><?php echo htmlspecialchars($displayName); ?></strong>
                                                    <small class="user-handle">@<?php echo htmlspecialchars($user->username); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user->email); ?></td>
                                        <td>
                                            <form
                                                action="<?php echo URLROOT; ?>/admin/updateUserRole/<?php echo (int) $user->id; ?>"
                                                method="POST"
                                                class="d-flex"
                                                data-admin-autosave="true"
                                                data-toast-success="Vai trò đã cập nhật (tự động)."
                                            >
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
                                                <select name="role" class="form-select form-select-sm" data-admin-autosave-input="true" data-admin-custom-select="true">
                                                    <option value="member" <?php echo $user->role === 'member' ? 'selected' : ''; ?>>Thành viên</option>
                                                    <option value="admin" <?php echo $user->role === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($user->created_at))); ?></td>
                                        <td>
                                            <span class="pill-badge <?php echo $user->status === 'active' ? 'pill-status-replied' : 'pill-status-banned'; ?>">
                                                <?php echo htmlspecialchars($statusLabels[$user->status] ?? $user->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown d-inline-block">
                                                <button
                                                    class="btn btn-soft btn-sm btn-icon user-actions-toggle"
                                                    type="button"
                                                    aria-expanded="false"
                                                    aria-label="Thêm thao tác"
                                                >
                                                    <i class="ti-more-alt"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu user-actions-menu">
                                                    <li>
                                                        <a class="dropdown-item admin-dropdown-link" href="<?php echo URLROOT; ?>/admin/editUser/<?php echo (int) $user->id; ?>">
                                                            <i class="ti-pencil"></i>
                                                            <span>Chỉnh sửa hồ sơ</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item admin-dropdown-link"
                                                            data-reset-user-id="<?php echo (int) $user->id; ?>"
                                                            data-reset-user-name="<?php echo htmlspecialchars($displayName); ?>">
                                                            <i class="ti-reload"></i>
                                                            <span>Reset mật khẩu</span>
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="<?php echo URLROOT; ?>/admin/toggleUserStatus/<?php echo (int) $user->id; ?>" method="POST" class="m-0">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
                                                            <input type="hidden" name="target_status" value="<?php echo $user->status === 'active' ? 'banned' : 'active'; ?>">
                                                            <button type="submit" class="dropdown-item admin-dropdown-link <?php echo $user->status === 'active' ? 'text-danger' : 'text-success'; ?>">
                                                                <i class="<?php echo $user->status === 'active' ? 'ti-na' : 'ti-check'; ?>"></i>
                                                                <span><?php echo $user->status === 'active' ? 'Khóa hồ sơ' : 'Mở khóa hồ sơ'; ?></span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="<?php echo URLROOT; ?>/admin/deleteUser/<?php echo (int) $user->id; ?>" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
                                                            <button type="submit" class="dropdown-item admin-dropdown-link text-danger">
                                                                <i class="ti-trash"></i>
                                                                <span>Xóa thành viên</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ((int) $pagination['last_page'] > 1): ?>
                    <nav aria-label="Phân trang người dùng" class="mt-3">
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= (int) $pagination['last_page']; $p++): ?>
                                <?php
                                $query = http_build_query([
                                    'keyword' => $filters['keyword'],
                                    'status' => $filters['status'],
                                    'role' => $filters['role'],
                                    'page' => $p
                                ]);
                                ?>
                                <li class="page-item <?php echo (int) $pagination['page'] === $p ? 'active' : ''; ?>">
                                    <a class="page-link bg-transparent border-secondary text-light" href="<?php echo URLROOT; ?>/admin/users?<?php echo $query; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div id="resetPasswordModal" class="admin-confirm-overlay" aria-hidden="true">
    <div class="admin-confirm-box">
        <p class="admin-confirm-message">Bạn có chắc muốn <strong>RESET mật khẩu</strong> cho người dùng này?</p>
        <p class="admin-confirm-subtext" id="resetPasswordUserName"></p>
        <div class="admin-confirm-actions">
            <button type="button" id="resetPasswordCancel" class="btn btn-outline-light btn-sm">Hủy bỏ</button>
            <button type="button" id="resetPasswordConfirm" class="btn btn-danger btn-sm">Xác nhận</button>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
