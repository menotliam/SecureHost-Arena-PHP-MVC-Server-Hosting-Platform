<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$stats = $data['stats'] ?? [];
$recentUsers = $data['recent_users'] ?? [];
$systemUsage = $data['system_usage'] ?? ['cpu' => 0, 'memory' => 0, 'disk' => 0];
$revenueSeries = $data['revenue_series'] ?? [];

$formatRelativeTime = function ($datetimeValue) {
    if (empty($datetimeValue)) {
        return 'Vừa xong';
    }
    $timestamp = is_numeric($datetimeValue) ? (int) $datetimeValue : strtotime($datetimeValue);
    if (!$timestamp) {
        return 'Vừa xong';
    }
    $diff = max(0, time() - $timestamp);
    if ($diff < 60) {
        return 'Vừa xong';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' phút trước';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' giờ trước';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . ' ngày trước';
    }
    return date('d/m/Y', $timestamp);
};

$safePercent = function ($value) {
    $number = (int) $value;
    if ($number < 0) {
        return 0;
    }
    if ($number > 100) {
        return 100;
    }
    return $number;
};

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

$cpuUsage = $safePercent($systemUsage['cpu'] ?? 0);
$memoryUsage = $safePercent($systemUsage['memory'] ?? 0);
$diskUsage = $safePercent($systemUsage['disk'] ?? 0);
$usageTotal = max(1, $cpuUsage + $memoryUsage + $diskUsage);
$cpuDegrees = (int) round(($cpuUsage / $usageTotal) * 360);
$memoryDegrees = (int) round(($memoryUsage / $usageTotal) * 360);
$diskDegrees = max(0, 360 - $cpuDegrees - $memoryDegrees);

$chartSource = [];
foreach ($revenueSeries as $item) {
    $chartSource[] = [
        'label' => (string) ($item['label'] ?? ''),
        'revenue' => (float) ($item['revenue'] ?? 0)
    ];
}

$currentRevenue = (float) ($stats['monthly_revenue'] ?? 0);
$previousRevenue = 0.0;
if (count($revenueSeries) >= 2) {
    $previousRevenue = (float) ($revenueSeries[count($revenueSeries) - 2]['revenue'] ?? 0);
}
$revenueBadgeClass = 'badge-soft-warning';
$revenueBadgeText = 'Tăng 0%';
if ($previousRevenue > 0) {
    $deltaPercent = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
    if ($deltaPercent >= 0) {
        $revenueBadgeClass = 'badge-soft-success';
        $revenueBadgeText = 'Tăng ' . number_format(abs($deltaPercent), 1) . '%';
    } else {
        $revenueBadgeClass = 'badge-soft-danger';
        $revenueBadgeText = 'Giảm ' . number_format(abs($deltaPercent), 1) . '%';
    }
} else {
    // Tránh chia cho 0 khi tháng trước chưa có doanh thu
    if ($currentRevenue > 0) {
        $revenueBadgeClass = 'badge-soft-success';
        $revenueBadgeText = 'Tăng mới';
    }
}
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-card-primary h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Doanh thu tháng</p>
                    <h2 class="stat-value">$<?php echo number_format((float) ($stats['monthly_revenue'] ?? 0), 2); ?></h2>
                </div>
                <span class="badge <?php echo $revenueBadgeClass; ?>">
                    <?php echo $revenueBadgeText; ?>
                </span>
            </div>
            <p class="stat-note">Doanh thu trong tháng hiện tại.</p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-card-teal h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Người dùng hoạt động</p>
                    <h2 class="stat-value"><?php echo number_format((int) ($stats['active_users'] ?? $stats['new_users'] ?? 0)); ?></h2>
                </div>
                <span class="badge badge-soft-success">+<?php echo (int) ($stats['new_users'] ?? 0); ?> mới</span>
            </div>
            <p class="stat-note">Số người dùng đang hoạt động trên nền tảng.</p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-card-success h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Uptime hệ thống</p>
                    <h2 class="stat-value"><?php echo htmlspecialchars($stats['system_uptime'] ?? '99.9%'); ?></h2>
                </div>
                <span class="badge badge-soft-success">Ổn định</span>
            </div>
            <p class="stat-note">Tình trạng vận hành trong giai đoạn hiện tại.</p>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="stat-card stat-card-azure h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Tổng người dùng</p>
                    <h2 class="stat-value"><?php echo number_format((int) ($stats['total_users'] ?? 0)); ?></h2>
                </div>
                <span class="badge badge-soft-primary">Mọi vai trò</span>
            </div>
            <p class="stat-note">Tổng tài khoản đã đăng ký trong hệ thống.</p>
        </article>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <section class="card panel-card h-100">
            <div class="card-body">
                <div class="panel-header mb-3">
                    <div>
                        <h2 class="panel-title">Tổng quan doanh thu</h2>
                        <p class="panel-muted">Biểu đồ xu hướng doanh thu theo tháng.</p>
                    </div>
                    <label class="d-flex align-items-center gap-2 m-0">
                        <select id="dashboardRevenueFilter" class="form-select form-select-sm" data-admin-custom-select="true">
                            <option value="3">3 tháng gần nhất</option>
                            <option value="5" selected>5 tháng gần nhất</option>
                        </select>
                    </label>
                </div>
                <?php if (empty($revenueSeries)): ?>
                    <p class="panel-muted">Chưa có dữ liệu doanh thu.</p>
                <?php else: ?>
                    <div class="revenue-area-wrap">
                        <svg id="revenueAreaChart" viewBox="0 0 640 280" preserveAspectRatio="none" aria-label="Revenue area chart">
                            <defs>
                                <linearGradient id="revenueFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.45"></stop>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.03"></stop>
                                </linearGradient>
                            </defs>
                            <g class="area-grid">
                                <line x1="50" y1="60" x2="610" y2="60"></line>
                                <line x1="50" y1="120" x2="610" y2="120"></line>
                                <line x1="50" y1="180" x2="610" y2="180"></line>
                                <line x1="50" y1="240" x2="610" y2="240"></line>
                            </g>
                            <g id="revenueAnimatedLayer">
                                <path id="revenueAreaPath" fill="url(#revenueFill)"></path>
                                <path id="revenueLinePath" fill="none" stroke="#3b82f6" stroke-width="3"></path>
                                <g id="revenuePoints"></g>
                            </g>
                        </svg>
                        <div id="revenueAxisLabels" class="revenue-axis-labels"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="panel-muted mb-0">Xu hướng ước tính từ các đơn hoàn tất.</p>
                        <span id="revenueTotalLabel" class="badge badge-soft-primary"></span>
                    </div>
                    <script>
                        window.adminRevenueSeries = <?php echo json_encode($chartSource, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    </script>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-lg-5">
        <section class="card panel-card h-100">
            <div class="card-body">
                <div class="panel-header mb-3">
                    <div>
                        <h2 class="panel-title">Mức sử dụng hệ thống</h2>
                        <p class="panel-muted">Biểu đồ donut cho CPU, RAM và ổ đĩa.</p>
                    </div>
                    <span class="badge badge-soft-success">Trực tiếp</span>
                </div>
                <div class="usage-donut-wrap">
                    <div
                        class="usage-donut"
                        style="background: conic-gradient(#3b82f6 0deg <?php echo $cpuDegrees; ?>deg, #14b8a6 <?php echo $cpuDegrees; ?>deg <?php echo ($cpuDegrees + $memoryDegrees); ?>deg, #22c55e <?php echo ($cpuDegrees + $memoryDegrees); ?>deg <?php echo ($cpuDegrees + $memoryDegrees + $diskDegrees); ?>deg);"
                    >
                        <div class="usage-donut-center">
                            <strong><?php echo (int) round(($cpuUsage + $memoryUsage + $diskUsage) / 3); ?>%</strong>
                            <span>Tải trung bình</span>
                        </div>
                    </div>
                    <div class="usage-legend-list">
                        <div class="usage-legend-item">
                            <div class="usage-legend-head">
                                <span class="usage-dot usage-dot-cpu"></span>
                                <span>CPU</span>
                                <strong><?php echo $cpuUsage; ?>%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $cpuUsage; ?>%"></div>
                            </div>
                        </div>
                        <div class="usage-legend-item">
                            <div class="usage-legend-head">
                                <span class="usage-dot usage-dot-memory"></span>
                                <span>RAM</span>
                                <strong><?php echo $memoryUsage; ?>%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $memoryUsage; ?>%; background-color: #14b8a6;"></div>
                            </div>
                        </div>
                        <div class="usage-legend-item">
                            <div class="usage-legend-head">
                                <span class="usage-dot usage-dot-disk"></span>
                                <span>Disk</span>
                                <strong><?php echo $diskUsage; ?>%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $diskUsage; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Thành viên đăng ký gần đây</h2>
                        <p class="panel-muted">Các tài khoản mới được kích hoạt gần nhất.</p>
                    </div>
                </div>
                <div class="recent-signups-list">
                    <?php if (empty($recentUsers)): ?>
                        <p class="panel-muted mb-0">Chưa có thành viên mới.</p>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <?php
                            $displayName = $user->full_name ?: $user->username;
                            $nameInitial = strtoupper(substr($displayName, 0, 1));
                            $isOnline = ($user->status ?? 'active') === 'active';
                            $packageLabel = $user->role === 'admin' ? 'Quản trị viên' : 'Thành viên';
                            $avatarUrl = $resolveAvatarUrl($user->avatar ?? '');
                            ?>
                            <article class="signup-item">
                                <div class="signup-avatar-wrap">
                                    <?php if ($avatarUrl !== ''): ?>
                                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar thành viên" class="signup-avatar signup-avatar-image">
                                    <?php else: ?>
                                        <div class="signup-avatar"><?php echo htmlspecialchars($nameInitial); ?></div>
                                    <?php endif; ?>
                                    <span class="signup-status-dot <?php echo $isOnline ? 'online' : 'idle'; ?>"></span>
                                </div>
                                <div class="signup-user-meta">
                                    <h3><?php echo htmlspecialchars($displayName); ?></h3>
                                    <p><?php echo htmlspecialchars($user->email); ?></p>
                                </div>
                                <div class="signup-user-badge">
                                    <span class="badge badge-soft-primary"><?php echo htmlspecialchars($packageLabel); ?></span>
                                    <small><?php echo htmlspecialchars($formatRelativeTime($user->created_at)); ?></small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
