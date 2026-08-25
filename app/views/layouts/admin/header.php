<?php
$publicSettings = $data['public_settings'] ?? [];
$siteLogoImageFile = basename((string) ($publicSettings['site_logo_image'] ?? ''));
$adminBrandLogoUrl = $siteLogoImageFile !== ''
    ? URLROOT . '/uploads/branding/' . rawurlencode($siteLogoImageFile)
    : URLROOT . '/admin_assets/images/icon/logo-v2.png';
$adminBrandLogoAlt = trim((string) ($publicSettings['site_logo_text'] ?? ''));
if ($adminBrandLogoAlt === '') {
    $adminBrandLogoAlt = SITENAME;
}
$defaultFavicon = URLROOT . '/admin_assets/images/icon/logo-v2.png';
$faviconExt = strtolower((string) pathinfo($siteLogoImageFile, PATHINFO_EXTENSION));
$useBrandingFavicon = $siteLogoImageFile !== '' && in_array($faviconExt, ['png', 'ico', 'gif', 'webp', 'jpg', 'jpeg'], true);
$adminFaviconHref = $useBrandingFavicon ? URLROOT . '/uploads/branding/' . rawurlencode($siteLogoImageFile) : $defaultFavicon;
$faviconMimeTypes = ['png' => 'image/png', 'ico' => 'image/x-icon', 'gif' => 'image/gif', 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'];
$adminFaviconMime = $useBrandingFavicon ? ($faviconMimeTypes[$faviconExt] ?? 'image/png') : 'image/png';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($data['title']) ? htmlspecialchars($data['title']) . ' - ' . SITENAME : 'Quản trị - ' . SITENAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="<?php echo htmlspecialchars($adminFaviconMime, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo htmlspecialchars($adminFaviconHref, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/themify-icons.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/typography.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/default-css.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/admin-modern.css?v=<?php echo filemtime(APPROOT . '/../public/admin_assets/css/admin-modern.css'); ?>">
    <script>window.adminCsrfToken = '<?php echo htmlspecialchars($data['csrf_admin'] ?? '', ENT_QUOTES); ?>';</script>
</head>
<body class="body-bg admin-modern">
    <?php
    $currentUrl = trim($_GET['url'] ?? 'admin', '/');
    $activeSection = 'dashboard';
    $settingsNav = '';
    if ($currentUrl === 'admin' || strpos($currentUrl, 'admin/index') === 0) {
        $activeSection = 'dashboard';
    } elseif (strpos($currentUrl, 'admin/products') === 0 || strpos($currentUrl, 'admin/services') === 0) {
        $activeSection = 'services';
    } elseif (strpos($currentUrl, 'admin/orders') === 0) {
        $activeSection = 'orders';
    } elseif (strpos($currentUrl, 'admin/reviews') === 0) {
        $activeSection = 'reviews';
    } elseif (
        strpos($currentUrl, 'admin/contacts') === 0
        || strpos($currentUrl, 'admin/tickets') === 0
        || strpos($currentUrl, 'admincontacts') === 0
    ) {
        $activeSection = 'tickets';
    } elseif (strpos($currentUrl, 'admin/ads') === 0) {
        $activeSection = 'ads';
    } elseif (strpos($currentUrl, 'admin/posts') === 0) {
        $activeSection = 'news';
    } elseif (strpos($currentUrl, 'admin/about') === 0) {
        $activeSection = 'about';
    } elseif (strpos($currentUrl, 'admin/faqs') === 0) {
        $activeSection = 'faqs';
    } elseif (strpos($currentUrl, 'admin/users') === 0) {
        $activeSection = 'users';
    } elseif (strpos($currentUrl, 'admin/auditlogs') === 0 || strpos($currentUrl, 'adminauditlogs') === 0) {
        $activeSection = 'auditlogs';
    } elseif (strpos($currentUrl, 'admin/settings') === 0) {
        $activeSection = 'settings';
        $parts = explode('/', $currentUrl);
        $settingsNav = isset($parts[2]) ? strtolower((string) $parts[2]) : 'homepage';
        if (!in_array($settingsNav, ['homepage', 'contact', 'profile'], true)) {
            $settingsNav = 'homepage';
        }
    }
    $pageTitle = $data['title'] ?? 'Bảng điều khiển';
    $navBadges = $data['nav_badges'] ?? [];
    $ticketBadge = max(0, (int) ($navBadges['tickets'] ?? 0));
    $newsBadge = max(0, (int) ($navBadges['news'] ?? 0));
    $usersBadge = max(0, (int) ($navBadges['users'] ?? 0));
    $notificationCount = max(0, (int) ($navBadges['notifications'] ?? ($ticketBadge + $usersBadge)));
    $toastState = is_array($data['admin_notification_toast'] ?? null) ? $data['admin_notification_toast'] : [];
    $showLoginToast = !empty($toastState['show']);
    $loginToastCount = max(0, (int) ($toastState['count'] ?? 0));
    ?>
    <div
        id="adminLoginNotificationState"
        class="d-none"
        data-show="<?php echo $showLoginToast ? '1' : '0'; ?>"
        data-count="<?php echo $loginToastCount; ?>"
        aria-hidden="true"
    ></div>
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Thanh điều hướng quản trị">
            <div class="admin-brand">
                <a href="<?php echo URLROOT; ?>/admin" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="<?php echo htmlspecialchars($adminBrandLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($adminBrandLogoAlt, ENT_QUOTES, 'UTF-8'); ?>" class="admin-brand-logo" style="max-width:40px;max-height:40px;object-fit:contain;">
                        <div>
                            <div class="admin-brand-title">Admin Zone</div>
                            
                        </div>
                </a>
            </div>
            <nav class="admin-nav">
                <div class="admin-nav-heading">Chính</div>
                <a class="admin-nav-link <?php echo $activeSection === 'dashboard' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin">
                    <i class="ti-dashboard"></i>
                    <span class="admin-link-text">Bảng điều khiển</span>
                </a>

                <div class="admin-nav-heading">Quản lý</div>
                <a class="admin-nav-link <?php echo $activeSection === 'services' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/products">
                    <i class="ti-package"></i>
                    <span class="admin-link-text">Dịch vụ</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'orders' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/orders">
                    <i class="ti-receipt"></i>
                    <span class="admin-link-text">Đơn hàng</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'reviews' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/reviews">
                    <i class="ti-star"></i>
                    <span class="admin-link-text">Đánh giá</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'tickets' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/contacts">
                    <i class="ti-email"></i>
                    <span class="admin-link-text">Liên hệ</span>
                    <span class="admin-nav-badge"><?php echo $ticketBadge; ?></span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'ads' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/ads">
                    <i class="ti-announcement"></i>
                    <span class="admin-link-text">Quảng cáo</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'about' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/about">
                    <i class="ti-info-alt"></i>
                    <span class="admin-link-text">Giới thiệu</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'faqs' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/faqs">
                    <i class="ti-help"></i>
                    <span class="admin-link-text">FAQ</span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'news' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/posts">
                    <i class="ti-file"></i>
                    <span class="admin-link-text">Tin tức</span>
                    <span class="admin-nav-badge admin-nav-badge-soft"><?php echo $newsBadge; ?></span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'users' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/users">
                    <i class="ti-user"></i>
                    <span class="admin-link-text">Người dùng</span>
                    <span class="admin-nav-badge admin-nav-badge-success"><?php echo $usersBadge; ?></span>
                </a>
                <a class="admin-nav-link <?php echo $activeSection === 'auditlogs' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/auditlogs">
                    <i class="ti-shield"></i>
                    <span class="admin-link-text">Audit Logs</span>
                </a>
                <div class="admin-nav-heading">Quản lý giao diện</div>
                <a class="admin-nav-link <?php echo $activeSection === 'settings' && $settingsNav === 'homepage' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/homepage">
                    <i class="ti-layout-slider-alt"></i>
                    <span class="admin-link-text">Trang chủ</span>
                </a>
                <a class="admin-nav-link admin-nav-sublink <?php echo $activeSection === 'settings' && $settingsNav === 'profile' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/profile">
                    <i class="ti-user"></i>
                    <span class="admin-link-text">Trang hồ sơ</span>
                </a>
                <a class="admin-nav-link admin-nav-sublink <?php echo $activeSection === 'settings' && $settingsNav === 'contact' ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/contact">
                    <i class="ti-map-alt"></i>
                    <span class="admin-link-text">Trang liên hệ</span>
                </a>

                <div class="admin-nav-heading">Hệ thống</div>
                <a class="admin-nav-link" href="<?php echo URLROOT; ?>/pages/index">
                    <i class="ti-home"></i>
                    <span class="admin-link-text">Về trang web</span>
                </a>
                <a class="admin-nav-link" href="<?php echo URLROOT; ?>/users/logout">
                    <i class="ti-power-off"></i>
                    <span class="admin-link-text">Đăng xuất</span>
                </a>
            </nav>
        </aside>

        <div class="admin-overlay" id="adminOverlay" aria-hidden="true"></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3 topbar-primary">
                        <button
                            type="button"
                            class="btn btn-soft btn-icon"
                            id="adminSidebarToggle"
                            aria-controls="adminSidebar"
                            aria-expanded="false"
                            aria-label="Mở hoặc thu gọn thanh bên"
                        >
                            <i class="ti-menu"></i>
                        </button>
                        <div>
                            <h1 class="admin-page-title h5"><?php echo htmlspecialchars($pageTitle); ?></h1>
                        </div>
                        <form
                            class="admin-search d-none d-md-flex"
                            role="search"
                            id="adminGlobalSearchForm"
                            data-active-section="<?php echo htmlspecialchars($activeSection); ?>"
                            action="<?php echo URLROOT; ?>/admin/users"
                            method="GET"
                        >
                            <i class="ti-search" aria-hidden="true"></i>
                            <input
                                type="search"
                                id="adminGlobalSearchInput"
                                name="keyword"
                                placeholder="Tìm module, người dùng, ticket..."
                                aria-label="Tìm kiếm trong trang quản trị"
                            >
                        </form>
                    </div>
                    <div class="admin-topbar-tools">
                        <button type="button" class="btn btn-soft btn-icon" id="adminThemeToggle" title="Đổi giao diện sáng/tối" aria-label="Đổi giao diện sáng hoặc tối">
                            <i class="fa-solid fa-sun"></i>
                        </button>
                        <div class="dropdown">
                            <button
                                type="button"
                                class="btn btn-soft btn-icon position-relative"
                                id="adminNotificationToggle"
                                title="Thông báo"
                                aria-label="Thông báo"
                                aria-expanded="false"
                            >
                                <i class="ti-bell"></i>
                                <span class="admin-notify-count <?php echo $notificationCount > 0 ? '' : 'd-none'; ?>" id="adminNotifyCount"><?php echo $notificationCount; ?></span>
                            </button>
                            <div class="admin-notification-menu" id="adminNotificationMenu" aria-hidden="true">
                                <div class="admin-notification-menu-header">
                                    <strong>Thông báo quản trị</strong>
                                </div>
                                <div class="admin-notification-list" id="adminNotificationList">
                                    <div class="admin-notification-empty">Đang tải thông báo...</div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button
                                class="btn btn-soft btn-icon dropdown-toggle admin-profile-btn"
                                aria-expanded="false"
                                aria-label="Mở menu tài khoản"
                            >
                                <i class="ti-user"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu">
                                <li>
                                    <a class="dropdown-item admin-dropdown-link" href="<?php echo URLROOT; ?>/users/profile">
                                        <i class="ti-user"></i>
                                        <span>Chỉnh sửa thông tin</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item admin-dropdown-link" href="<?php echo URLROOT; ?>/users/logout">
                                        <i class="ti-power-off"></i>
                                        <span>Đăng xuất</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content">