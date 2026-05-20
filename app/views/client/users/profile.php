<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<?php
$user = $data['user'] ?? null;
$errors = $data['errors'] ?? [];
$ps = $data['public_settings'] ?? [];

$pageTitle = trim((string) ($ps['profile_page_title'] ?? ''));
$pageIntro = trim((string) ($ps['profile_page_intro'] ?? ''));
$secAvatar = trim((string) ($ps['profile_section_avatar_title'] ?? ''));
$lblUpload = trim((string) ($ps['profile_avatar_upload_label'] ?? ''));
$hintAvatar = trim((string) ($ps['profile_avatar_hint'] ?? ''));
$secPersonal = trim((string) ($ps['profile_section_personal_title'] ?? ''));
$secPassword = trim((string) ($ps['profile_section_password_title'] ?? ''));
$lblName = trim((string) ($ps['profile_label_display_name'] ?? ''));
$lblEmail = trim((string) ($ps['profile_label_email'] ?? ''));
$lblCur = trim((string) ($ps['profile_label_current_password'] ?? ''));
$lblNew = trim((string) ($ps['profile_label_new_password'] ?? ''));
$lblCf = trim((string) ($ps['profile_label_confirm_password'] ?? ''));
$btnSave = trim((string) ($ps['profile_btn_save'] ?? ''));
$btnPw = trim((string) ($ps['profile_btn_update_password'] ?? ''));

$avatarRaw = trim((string) ($user->avatar ?? ''));
$avatarUrl = '';
if ($avatarRaw !== '') {
    if (strpos($avatarRaw, 'http://') === 0 || strpos($avatarRaw, 'https://') === 0) {
        $avatarUrl = $avatarRaw;
    } elseif (strpos($avatarRaw, '/uploads/') === 0) {
        $avatarUrl = URLROOT . $avatarRaw;
    } elseif (strpos($avatarRaw, 'uploads/') === 0) {
        $avatarUrl = URLROOT . '/' . ltrim($avatarRaw, '/');
    } else {
        $avatarUrl = URLROOT . '/uploads/avatars/' . ltrim($avatarRaw, '/');
    }
}
$displayName = $user ? ($user->full_name ?: $user->username) : '';
?>

<div class="bg-gray-950 min-h-[80vh] py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-gray-400 mt-2"><?php echo htmlspecialchars($pageIntro); ?></p>
        </div>

        <?php if (!empty($data['success_message'])): ?>
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 text-green-300 rounded-xl">
                <?php echo htmlspecialchars($data['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-5"><?php echo htmlspecialchars($secAvatar); ?></h2>
                    <div class="flex flex-col items-center">
                        <?php if ($avatarUrl !== ''): ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="w-32 h-32 rounded-full object-cover border border-gray-700 mb-4">
                        <?php else: ?>
                            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold mb-4">
                                <?php echo strtoupper(substr($displayName, 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo URLROOT; ?>/users/profile" method="POST" enctype="multipart/form-data" class="w-full text-center">
                            <input type="hidden" name="action" value="upload_avatar">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                            <label class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg cursor-pointer transition">
                                <i class="fa-solid fa-upload"></i>
                                <?php echo htmlspecialchars($lblUpload); ?>
                                <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                            </label>
                            <p class="text-xs text-gray-500 mt-2"><?php echo htmlspecialchars($hintAvatar); ?></p>
                            <?php if (!empty($errors['avatar'])): ?>
                                <p class="text-xs text-red-400 mt-2"><?php echo htmlspecialchars($errors['avatar']); ?></p>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-5"><?php echo htmlspecialchars($secPersonal); ?></h2>
                    <form id="profileInfoForm" action="<?php echo URLROOT; ?>/users/profile" method="POST" class="space-y-4" novalidate>
                        <input type="hidden" name="action" value="profile_info">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($lblName); ?></label>
                            <input id="prof-full-name" type="text" name="full_name" value="<?php echo htmlspecialchars($user->full_name ?? ''); ?>" class="w-full px-4 py-3 bg-gray-800 border <?php echo !empty($errors['full_name']) ? 'border-red-500' : 'border-gray-700'; ?> rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                            <span id="prof-full-name-err" class="text-xs text-red-400 mt-1 block"><?php echo htmlspecialchars($errors['full_name'] ?? ''); ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($lblEmail); ?></label>
                            <input id="prof-email" type="email" name="email" value="<?php echo htmlspecialchars($user->email ?? ''); ?>" class="w-full px-4 py-3 bg-gray-800 border <?php echo !empty($errors['email']) ? 'border-red-500' : 'border-gray-700'; ?> rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                            <span id="prof-email-err" class="text-xs text-red-400 mt-1 block"><?php echo htmlspecialchars($errors['email'] ?? ''); ?></span>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl transition"><?php echo htmlspecialchars($btnSave); ?></button>
                    </form>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-xl font-semibold text-white mb-5"><?php echo htmlspecialchars($secPassword); ?></h2>
                    <form id="changePasswordForm" action="<?php echo URLROOT; ?>/users/profile" method="POST" class="space-y-4" novalidate>
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($lblCur); ?></label>
                            <input id="prof-current-password" type="password" name="current_password" class="w-full px-4 py-3 bg-gray-800 border <?php echo !empty($errors['current_password']) ? 'border-red-500' : 'border-gray-700'; ?> rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                            <span id="prof-current-password-err" class="text-xs text-red-400 mt-1 block"><?php echo htmlspecialchars($errors['current_password'] ?? ''); ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($lblNew); ?></label>
                            <input id="prof-new-password" type="password" name="new_password" class="w-full px-4 py-3 bg-gray-800 border <?php echo !empty($errors['new_password']) ? 'border-red-500' : 'border-gray-700'; ?> rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                            <span id="prof-new-password-err" class="text-xs text-red-400 mt-1 block"><?php echo htmlspecialchars($errors['new_password'] ?? ''); ?></span>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($lblCf); ?></label>
                            <input id="prof-confirm-password" type="password" name="confirm_password" class="w-full px-4 py-3 bg-gray-800 border <?php echo !empty($errors['confirm_password']) ? 'border-red-500' : 'border-gray-700'; ?> rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50">
                            <span id="prof-confirm-password-err" class="text-xs text-red-400 mt-1 block"><?php echo htmlspecialchars($errors['confirm_password'] ?? ''); ?></span>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition"><?php echo htmlspecialchars($btnPw); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
