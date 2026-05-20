<?php
$s = $data['settings'];
$errors = $data['errors'] ?? [];
?>

<form action="<?php echo URLROOT; ?>/admin/settings/profile" method="POST" novalidate data-admin-settings-form="true">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
    <input type="hidden" name="settings_section" value="profile">

    <div class="settings-section-header">
        <h3>Trang hồ sơ thành viên</h3>
        <p>Nội dung hiển thị tại <code><?php echo htmlspecialchars(URLROOT); ?>/users/profile</code> (tiêu đề, mô tả, nhãn các khối và nút).</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_page_title" class="col-form-label">Tiêu đề trang (H1)</label>
                <input class="form-control <?php echo !empty($errors['profile_page_title']) ? 'is-invalid' : ''; ?>" type="text" id="profile_page_title" name="profile_page_title" value="<?php echo htmlspecialchars($s['profile_page_title'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['profile_page_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_page_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_page_intro" class="col-form-label">Mô tả dưới tiêu đề</label>
                <input class="form-control <?php echo !empty($errors['profile_page_intro']) ? 'is-invalid' : ''; ?>" type="text" id="profile_page_intro" name="profile_page_intro" value="<?php echo htmlspecialchars($s['profile_page_intro'] ?? ''); ?>" maxlength="240">
                <?php if (!empty($errors['profile_page_intro'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_page_intro']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Khối ảnh đại diện</h3>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_section_avatar_title" class="col-form-label">Tiêu đề khối</label>
                <input class="form-control <?php echo !empty($errors['profile_section_avatar_title']) ? 'is-invalid' : ''; ?>" type="text" id="profile_section_avatar_title" name="profile_section_avatar_title" value="<?php echo htmlspecialchars($s['profile_section_avatar_title'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_section_avatar_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_section_avatar_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_avatar_upload_label" class="col-form-label">Nhãn nút tải ảnh</label>
                <input class="form-control <?php echo !empty($errors['profile_avatar_upload_label']) ? 'is-invalid' : ''; ?>" type="text" id="profile_avatar_upload_label" name="profile_avatar_upload_label" value="<?php echo htmlspecialchars($s['profile_avatar_upload_label'] ?? ''); ?>" maxlength="60">
                <?php if (!empty($errors['profile_avatar_upload_label'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_avatar_upload_label']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_avatar_hint" class="col-form-label">Ghi chú định dạng / dung lượng</label>
                <input class="form-control <?php echo !empty($errors['profile_avatar_hint']) ? 'is-invalid' : ''; ?>" type="text" id="profile_avatar_hint" name="profile_avatar_hint" value="<?php echo htmlspecialchars($s['profile_avatar_hint'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['profile_avatar_hint'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_avatar_hint']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Khối thông tin &amp; mật khẩu</h3>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_section_personal_title" class="col-form-label">Tiêu đề khối thông tin cá nhân</label>
                <input class="form-control <?php echo !empty($errors['profile_section_personal_title']) ? 'is-invalid' : ''; ?>" type="text" id="profile_section_personal_title" name="profile_section_personal_title" value="<?php echo htmlspecialchars($s['profile_section_personal_title'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_section_personal_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_section_personal_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_section_password_title" class="col-form-label">Tiêu đề khối đổi mật khẩu</label>
                <input class="form-control <?php echo !empty($errors['profile_section_password_title']) ? 'is-invalid' : ''; ?>" type="text" id="profile_section_password_title" name="profile_section_password_title" value="<?php echo htmlspecialchars($s['profile_section_password_title'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_section_password_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_section_password_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_label_display_name" class="col-form-label">Nhãn ô họ tên</label>
                <input class="form-control <?php echo !empty($errors['profile_label_display_name']) ? 'is-invalid' : ''; ?>" type="text" id="profile_label_display_name" name="profile_label_display_name" value="<?php echo htmlspecialchars($s['profile_label_display_name'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_label_display_name'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_label_display_name']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_label_email" class="col-form-label">Nhãn ô email</label>
                <input class="form-control <?php echo !empty($errors['profile_label_email']) ? 'is-invalid' : ''; ?>" type="text" id="profile_label_email" name="profile_label_email" value="<?php echo htmlspecialchars($s['profile_label_email'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_label_email'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_label_email']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_label_current_password" class="col-form-label">Nhãn mật khẩu hiện tại</label>
                <input class="form-control <?php echo !empty($errors['profile_label_current_password']) ? 'is-invalid' : ''; ?>" type="text" id="profile_label_current_password" name="profile_label_current_password" value="<?php echo htmlspecialchars($s['profile_label_current_password'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_label_current_password'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_label_current_password']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_label_new_password" class="col-form-label">Nhãn mật khẩu mới</label>
                <input class="form-control <?php echo !empty($errors['profile_label_new_password']) ? 'is-invalid' : ''; ?>" type="text" id="profile_label_new_password" name="profile_label_new_password" value="<?php echo htmlspecialchars($s['profile_label_new_password'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_label_new_password'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_label_new_password']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="profile_label_confirm_password" class="col-form-label">Nhãn xác nhận mật khẩu</label>
                <input class="form-control <?php echo !empty($errors['profile_label_confirm_password']) ? 'is-invalid' : ''; ?>" type="text" id="profile_label_confirm_password" name="profile_label_confirm_password" value="<?php echo htmlspecialchars($s['profile_label_confirm_password'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['profile_label_confirm_password'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_label_confirm_password']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_btn_save" class="col-form-label">Nhãn nút lưu thông tin</label>
                <input class="form-control <?php echo !empty($errors['profile_btn_save']) ? 'is-invalid' : ''; ?>" type="text" id="profile_btn_save" name="profile_btn_save" value="<?php echo htmlspecialchars($s['profile_btn_save'] ?? ''); ?>" maxlength="60">
                <?php if (!empty($errors['profile_btn_save'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_btn_save']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="profile_btn_update_password" class="col-form-label">Nhãn nút cập nhật mật khẩu</label>
                <input class="form-control <?php echo !empty($errors['profile_btn_update_password']) ? 'is-invalid' : ''; ?>" type="text" id="profile_btn_update_password" name="profile_btn_update_password" value="<?php echo htmlspecialchars($s['profile_btn_update_password'] ?? ''); ?>" maxlength="60">
                <?php if (!empty($errors['profile_btn_update_password'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['profile_btn_update_password']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="settings-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="<?php echo URLROOT; ?>/admin/settings/profile" class="btn btn-outline-light settings-actions-cancel">Hủy</a>
    </div>
</form>