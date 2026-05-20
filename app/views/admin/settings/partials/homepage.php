<?php
$s = $data['settings'];
$errors = $data['errors'] ?? [];
$pickerProducts = is_array($data['picker_products'] ?? null) ? $data['picker_products'] : [];
$pickerReviews = is_array($data['picker_reviews'] ?? null) ? $data['picker_reviews'] : [];

$logoImageFile = basename(trim($s['site_logo_image'] ?? ''));
$logoImageUrl = $logoImageFile !== '' ? URLROOT . '/uploads/branding/' . rawurlencode($logoImageFile) : '';

$heroBgFile = basename(trim($s['home_hero_bg_image'] ?? ''));
$heroBgUrl = $heroBgFile !== '' ? URLROOT . '/uploads/branding/' . rawurlencode($heroBgFile) : '';

$parts = trim((string) ($s['home_product_ids'] ?? '')) === '' ? [] : explode(',', (string) $s['home_product_ids']);
$slotSelected = [];
for ($i = 0; $i < 4; $i++) {
    $slotSelected[$i] = isset($parts[$i]) ? (int) $parts[$i] : 0;
}

$reviewKey = trim((string) ($s['home_review_key'] ?? ''));
?>

<form action="<?php echo URLROOT; ?>/admin/settings/homepage" method="POST" enctype="multipart/form-data" novalidate data-admin-settings-form="true">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
    <input type="hidden" name="settings_section" value="homepage">

    <div class="settings-section-header">
        <h3>Nhận diện &amp; hero</h3>
        <p>Logo, tiêu đề và ảnh nền khu vực đầu trang.</p>
    </div>

    <div class="settings-upload-zone mb-3" id="brandingUploadZone">
        <input type="file" id="branding_asset" name="branding_asset" accept=".png,.svg,.ico" hidden>
        <div class="settings-upload-icon"><i class="ti-upload"></i></div>
        <strong>Kéo thả logo vào đây</strong>
        <p>Hỗ trợ PNG/SVG/ICO (tối đa 2MB)</p>
        <button type="button" class="btn btn-outline-light btn-sm" id="brandingUploadBrowse">Chọn tệp</button>
        <small id="brandingUploadFilename"></small>
    </div>
    <?php if ($logoImageUrl !== ''): ?>
        <div class="mb-3">
            <label class="form-label d-block">Logo hiện tại</label>
            <img src="<?php echo htmlspecialchars($logoImageUrl); ?>" alt="Logo hiện tại" class="settings-logo-preview">
        </div>
    <?php endif; ?>
    <?php if (!empty($errors['branding_asset'])): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($errors['branding_asset']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="site_logo_text" class="col-form-label">Tên logo hiển thị</label>
                <input class="form-control <?php echo !empty($errors['site_logo_text']) ? 'is-invalid' : ''; ?>" type="text" id="site_logo_text" name="site_logo_text" value="<?php echo htmlspecialchars($s['site_logo_text'] ?? ''); ?>" maxlength="60" required>
                <?php if (!empty($errors['site_logo_text'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['site_logo_text']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="home_card_tech_title" class="col-form-label">Tiêu đề card trái (năng lực công nghệ)</label>
                <input class="form-control" type="text" id="home_card_tech_title" name="home_card_tech_title" value="<?php echo htmlspecialchars($s['home_card_tech_title'] ?? ''); ?>" maxlength="80">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="home_hero_title_gradient" class="col-form-label">Tiêu đề chính (dòng gradient)</label>
                <input class="form-control <?php echo !empty($errors['home_hero_title_gradient']) ? 'is-invalid' : ''; ?>" type="text" id="home_hero_title_gradient" name="home_hero_title_gradient" value="<?php echo htmlspecialchars($s['home_hero_title_gradient'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['home_hero_title_gradient'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['home_hero_title_gradient']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="home_hero_title_plain" class="col-form-label">Tiêu đề phụ (dòng trắng)</label>
                <input class="form-control <?php echo !empty($errors['home_hero_title_plain']) ? 'is-invalid' : ''; ?>" type="text" id="home_hero_title_plain" name="home_hero_title_plain" value="<?php echo htmlspecialchars($s['home_hero_title_plain'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['home_hero_title_plain'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['home_hero_title_plain']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="home_hero_subtitle" class="col-form-label">Đoạn mô tả hero</label>
        <textarea class="form-control <?php echo !empty($errors['home_hero_subtitle']) ? 'is-invalid' : ''; ?>" id="home_hero_subtitle" name="home_hero_subtitle" rows="3" maxlength="600"><?php echo htmlspecialchars($s['home_hero_subtitle'] ?? ''); ?></textarea>
        <?php if (!empty($errors['home_hero_subtitle'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['home_hero_subtitle']); ?></div>
        <?php endif; ?>
    </div>

    <div class="settings-upload-zone mb-3" id="heroBgUploadZone">
        <input type="file" id="hero_bg_asset" name="hero_bg_asset" accept=".jpg,.jpeg,.png,.gif,.webp" hidden>
        <div class="settings-upload-icon"><i class="ti-image"></i></div>
        <strong>Ảnh nền hero (JPG/PNG/GIF/WEBP, tối đa 3MB)</strong>
        <p>Để trống nếu giữ ảnh hiện tại hoặc dùng nền mặc định của giao diện.</p>
        <button type="button" class="btn btn-outline-light btn-sm" id="heroBgUploadBrowse">Chọn ảnh nền</button>
        <small id="heroBgUploadFilename"></small>
    </div>
    <?php if ($heroBgUrl !== ''): ?>
        <div class="mb-3">
            <label class="form-label d-block">Xem trước nền hiện tại</label>
            <div class="settings-hero-bg-preview rounded overflow-hidden border border-secondary" style="max-height:140px;">
                <img src="<?php echo htmlspecialchars($heroBgUrl); ?>" alt="" class="w-100 h-auto object-fit-cover" style="max-height:140px;">
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="clear_hero_bg" id="clear_hero_bg" value="1">
                <label class="form-check-label" for="clear_hero_bg">Xóa ảnh nền (dùng nền mặc định)</label>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors['hero_bg_asset'])): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($errors['hero_bg_asset']); ?></div>
    <?php endif; ?>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Card nổi &amp; sản phẩm</h3>
        <p>Chọn review đã duyệt và tối đa 4 gói server hiển thị.</p>
    </div>

    <div class="form-group mb-3">
        <label for="home_review_key" class="col-form-label">Review hiển thị (card phải)</label>
        <select class="form-select <?php echo !empty($errors['home_review_key']) ? 'is-invalid' : ''; ?>" id="home_review_key" name="home_review_key" data-admin-custom-select="true">
            <option value="">— Tự động: review 5 sao mới nhất —</option>
            <?php foreach ($pickerReviews as $rev): ?>
                <?php
                $rk = (int) ($rev->user_id ?? 0) . ':' . (int) ($rev->review_id ?? 0);
                $who = trim((string) ($rev->full_name ?? ''));
                if ($who === '') {
                    $who = trim((string) ($rev->username ?? ''));
                }
                $pn = trim((string) ($rev->product_name ?? ''));
                $label = '★' . (int) ($rev->rating ?? 0) . ' ' . $who . ' — ' . $pn;
                ?>
                <option value="<?php echo htmlspecialchars($rk); ?>" <?php echo $reviewKey === $rk ? ' selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['home_review_key'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['home_review_key']); ?></div>
        <?php endif; ?>
        <?php if (empty($pickerReviews)): ?>
            <small class="settings-help-text">Chưa có review sản phẩm đã duyệt trong CSDL.</small>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="col-md-6 col-xl-3">
                <div class="form-group mb-3">
                    <label class="col-form-label" for="home_product_slot_<?php echo $i + 1; ?>">Sản phẩm <?php echo $i + 1; ?></label>
                    <select class="form-select" id="home_product_slot_<?php echo $i + 1; ?>" name="home_product_slot_<?php echo $i + 1; ?>" data-admin-custom-select="true">
                        <option value="">— Không hiển thị ô này —</option>
                        <?php foreach ($pickerProducts as $p): ?>
                            <?php $pid = (int) ($p->id ?? 0); ?>
                            <option value="<?php echo $pid; ?>" <?php echo $slotSelected[$i] === $pid ? ' selected' : ''; ?>><?php echo htmlspecialchars((string) ($p->name ?? '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <?php if (!empty($errors['home_product_ids'])): ?>
        <div class="alert alert-danger py-2"><?php echo htmlspecialchars($errors['home_product_ids']); ?></div>
    <?php endif; ?>
    <p class="settings-help-text small mb-0">Nếu không chọn sản phẩm nào, hệ thống lấy 4 gói active mới nhất như trước.</p>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Khối #about-us trên trang chủ</h3>
        <p>Tiêu đề và ba thẻ tính năng.</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="home_about_kicker" class="col-form-label">Dòng phụ (uppercase)</label>
                <input class="form-control" type="text" id="home_about_kicker" name="home_about_kicker" value="<?php echo htmlspecialchars($s['home_about_kicker'] ?? ''); ?>" maxlength="80">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group mb-3">
                <label for="home_about_heading" class="col-form-label">Tiêu đề chính</label>
                <input class="form-control" type="text" id="home_about_heading" name="home_about_heading" value="<?php echo htmlspecialchars($s['home_about_heading'] ?? ''); ?>" maxlength="160">
            </div>
        </div>
    </div>
    <div class="form-group mb-3">
        <label for="home_about_lead" class="col-form-label">Đoạn dẫn</label>
        <textarea class="form-control" id="home_about_lead" name="home_about_lead" rows="2" maxlength="400"><?php echo htmlspecialchars($s['home_about_lead'] ?? ''); ?></textarea>
    </div>

    <?php for ($f = 1; $f <= 3; $f++): ?>
        <div class="settings-field-group rounded p-3 mb-3">
            <h4 class="h6 settings-field-group-title mb-3">Cột <?php echo $f; ?></h4>
            <div class="form-group mb-2">
                <label class="col-form-label" for="home_about_feat<?php echo $f; ?>_title">Tiêu đề</label>
                <input class="form-control" type="text" id="home_about_feat<?php echo $f; ?>_title" name="home_about_feat<?php echo $f; ?>_title" value="<?php echo htmlspecialchars($s['home_about_feat' . $f . '_title'] ?? ''); ?>" maxlength="120">
            </div>
            <div class="form-group mb-0">
                <label class="col-form-label" for="home_about_feat<?php echo $f; ?>_text">Nội dung</label>
                <textarea class="form-control" id="home_about_feat<?php echo $f; ?>_text" name="home_about_feat<?php echo $f; ?>_text" rows="3" maxlength="600"><?php echo htmlspecialchars($s['home_about_feat' . $f . '_text'] ?? ''); ?></textarea>
            </div>
        </div>
    <?php endfor; ?>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Thông tin liên hệ chung (footer &amp; meta site)</h3>
        <p>Hotline, email, địa chỉ và mô tả ngắn hiển thị trên footer và meta mặc định. Đã chuyển khỏi tab Liên hệ để gom cùng cấu hình trang chủ.</p>
    </div>

    <div class="form-group mb-3">
        <label for="site_about_snippet" class="col-form-label">Mô tả ngắn / meta site (footer)</label>
        <textarea class="form-control" id="site_about_snippet" name="site_about_snippet" rows="3" maxlength="300"><?php echo htmlspecialchars($s['site_about_snippet'] ?? ''); ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="site_contact_email" class="col-form-label">Email liên hệ</label>
                <div class="input-icon-group">
                    <span><i class="ti-email"></i></span>
                    <input class="form-control <?php echo !empty($errors['site_contact_email']) ? 'is-invalid' : ''; ?>" type="email" id="site_contact_email" name="site_contact_email" value="<?php echo htmlspecialchars($s['site_contact_email'] ?? ''); ?>" maxlength="100" required>
                </div>
                <?php if (!empty($errors['site_contact_email'])): ?>
                    <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['site_contact_email']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="site_hotline" class="col-form-label">Hotline</label>
                <div class="input-icon-group">
                    <span><i class="ti-mobile"></i></span>
                    <input class="form-control <?php echo !empty($errors['site_hotline']) ? 'is-invalid' : ''; ?>" type="text" id="site_hotline" name="site_hotline" value="<?php echo htmlspecialchars($s['site_hotline'] ?? ''); ?>" maxlength="30" required>
                </div>
                <?php if (!empty($errors['site_hotline'])): ?>
                    <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['site_hotline']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="site_address" class="col-form-label">Địa chỉ</label>
        <div class="input-icon-group">
            <span><i class="ti-location-pin"></i></span>
            <input class="form-control <?php echo !empty($errors['site_address']) ? 'is-invalid' : ''; ?>" type="text" id="site_address" name="site_address" value="<?php echo htmlspecialchars($s['site_address'] ?? ''); ?>" maxlength="255" required>
        </div>
        <?php if (!empty($errors['site_address'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['site_address']); ?></div>
        <?php endif; ?>
    </div>

    <div class="settings-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="<?php echo URLROOT; ?>/admin/settings/homepage" class="btn btn-outline-light settings-actions-cancel">Hủy</a>
    </div>
</form>