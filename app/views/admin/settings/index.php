<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$section = strtolower(trim($data['settings_section'] ?? 'homepage'));
if (!in_array($section, ['homepage', 'contact', 'profile'], true)) {
    $section = 'homepage';
}
?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2">
                    <h2 class="panel-title mb-0">Cài đặt giao diện công khai</h2>
                    <span class="badge badge-soft-primary">Theo từng trang</span>
                </div>

                <div class="settings-page-tabs mb-4">
                    <a class="settings-page-tab <?php echo $section === 'homepage' ? 'is-active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/homepage">Trang chủ</a>
                    <a class="settings-page-tab <?php echo $section === 'profile' ? 'is-active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/profile">Hồ sơ</a>
                    <a class="settings-page-tab <?php echo $section === 'contact' ? 'is-active' : ''; ?>" href="<?php echo URLROOT; ?>/admin/settings/contact">Liên hệ</a>
                </div>

                <?php if (!empty($data['flash'])): ?>
                    <div class="alert alert-<?php echo $data['flash']['type'] === 'success' ? 'success' : 'danger'; ?>">
                        <?php echo htmlspecialchars($data['flash']['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($section === 'homepage'): ?>
                    <?php require APPROOT . '/views/admin/settings/partials/homepage.php'; ?>
                <?php elseif ($section === 'profile'): ?>
                    <?php require APPROOT . '/views/admin/settings/partials/profile.php'; ?>
                <?php else: ?>
                    <?php require APPROOT . '/views/admin/settings/partials/contact.php'; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
