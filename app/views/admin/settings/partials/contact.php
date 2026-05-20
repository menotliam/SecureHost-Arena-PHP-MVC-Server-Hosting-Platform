<?php
$s = $data['settings'];
$errors = $data['errors'] ?? [];
$ticketCats = is_array($data['contact_ticket_categories'] ?? null) ? $data['contact_ticket_categories'] : [];
$mapUrl = trim($s['site_map_embed_url'] ?? '');
$showMapPreview = $mapUrl !== '' && filter_var($mapUrl, FILTER_VALIDATE_URL) !== false;
?>

<form action="<?php echo URLROOT; ?>/admin/settings/contact" method="POST" novalidate data-admin-settings-form="true">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
    <input type="hidden" name="settings_section" value="contact">

    <div class="settings-section-header">
        <h3>Trang Liên hệ — meta &amp; SEO</h3>
        <p>Tiêu đề và mô tả dùng cho thẻ <code>&lt;title&gt;</code> / meta (trình duyệt, tìm kiếm).</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_page_title" class="col-form-label">Tiêu đề trang (meta)</label>
                <input class="form-control <?php echo !empty($errors['contact_page_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_page_title" name="contact_page_title" value="<?php echo htmlspecialchars($s['contact_page_title'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['contact_page_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_page_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_sidebar_title" class="col-form-label">Tiêu đề sidebar (tùy chọn)</label>
                <input class="form-control <?php echo !empty($errors['contact_sidebar_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_sidebar_title" name="contact_sidebar_title" value="<?php echo htmlspecialchars($s['contact_sidebar_title'] ?? ''); ?>" maxlength="120" placeholder="Để trống nếu không dùng">
                <?php if (!empty($errors['contact_sidebar_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_sidebar_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="contact_page_intro" class="col-form-label">Mô tả trang (meta)</label>
        <textarea class="form-control <?php echo !empty($errors['contact_page_intro']) ? 'is-invalid' : ''; ?>" id="contact_page_intro" name="contact_page_intro" rows="3" maxlength="500"><?php echo htmlspecialchars($s['contact_page_intro'] ?? ''); ?></textarea>
        <?php if (!empty($errors['contact_page_intro'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_page_intro']); ?></div>
        <?php endif; ?>
    </div>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Support console — cổng vào</h3>
        <p>Tiêu đề, card node, terminal, CTA và <strong>bản đồ</strong> trên cổng hỗ trợ trước khi mở terminal ticket. Thông tin hotline / email / địa chỉ chung được chuyển sang tab <strong>Trang chủ</strong>.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_gate_headline" class="col-form-label">Tiêu đề — phần trước</label>
                <input class="form-control <?php echo !empty($errors['contact_gate_headline']) ? 'is-invalid' : ''; ?>" type="text" id="contact_gate_headline" name="contact_gate_headline" value="<?php echo htmlspecialchars($s['contact_gate_headline'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_gate_headline'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_gate_headline']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_gate_headline_accent" class="col-form-label">Tiêu đề — phần nhấn màu</label>
                <input class="form-control <?php echo !empty($errors['contact_gate_headline_accent']) ? 'is-invalid' : ''; ?>" type="text" id="contact_gate_headline_accent" name="contact_gate_headline_accent" value="<?php echo htmlspecialchars($s['contact_gate_headline_accent'] ?? ''); ?>" maxlength="40">
                <?php if (!empty($errors['contact_gate_headline_accent'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_gate_headline_accent']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="contact_gate_subtitle" class="col-form-label">Subtitle</label>
        <textarea class="form-control <?php echo !empty($errors['contact_gate_subtitle']) ? 'is-invalid' : ''; ?>" id="contact_gate_subtitle" name="contact_gate_subtitle" rows="2" maxlength="300"><?php echo htmlspecialchars($s['contact_gate_subtitle'] ?? ''); ?></textarea>
        <?php if (!empty($errors['contact_gate_subtitle'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_gate_subtitle']); ?></div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_node_card_title" class="col-form-label">Tiêu đề card node</label>
                <input class="form-control <?php echo !empty($errors['contact_node_card_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_node_card_title" name="contact_node_card_title" value="<?php echo htmlspecialchars($s['contact_node_card_title'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_node_card_title'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_node_card_title']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_node_region" class="col-form-label">Nhãn khu vực node</label>
                <input class="form-control <?php echo !empty($errors['contact_node_region']) ? 'is-invalid' : ''; ?>" type="text" id="contact_node_region" name="contact_node_region" value="<?php echo htmlspecialchars($s['contact_node_region'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_node_region'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_node_region']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_node_online_label" class="col-form-label">Nhãn trạng thái online</label>
                <input class="form-control <?php echo !empty($errors['contact_node_online_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_node_online_label" name="contact_node_online_label" value="<?php echo htmlspecialchars($s['contact_node_online_label'] ?? ''); ?>" maxlength="40">
                <?php if (!empty($errors['contact_node_online_label'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_node_online_label']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_node_latency_label" class="col-form-label">Nhãn trước số latency</label>
                <input class="form-control <?php echo !empty($errors['contact_node_latency_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_node_latency_label" name="contact_node_latency_label" value="<?php echo htmlspecialchars($s['contact_node_latency_label'] ?? ''); ?>" maxlength="40">
                <?php if (!empty($errors['contact_node_latency_label'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_node_latency_label']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="contact_discord_typed_block" class="col-form-label">Terminal Discord</label>
        <textarea class="form-control font-monospace small <?php echo !empty($errors['contact_discord_typed_block']) ? 'is-invalid' : ''; ?>" id="contact_discord_typed_block" name="contact_discord_typed_block" rows="6" maxlength="2000" placeholder="> dòng 1&#10;> dòng 2&#10;> dòng cuối (nhãn liên kết Discord)"><?php echo htmlspecialchars($s['contact_discord_typed_block'] ?? ''); ?></textarea>
        <small class="form-text settings-help-text">Gõ từ trên xuống dưới. Nếu đã cấu hình URL Discord bên dưới, <strong>dòng cuối cùng</strong> sẽ không được gõ máy mà hiển thị thành liên kết mở tab mới.</small>
        <?php if (!empty($errors['contact_discord_typed_block'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_discord_typed_block']); ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group mb-3">
        <label for="contact_discord_invite_url" class="col-form-label">URL mời Discord (tuỳ chọn)</label>
        <input class="form-control font-monospace small <?php echo !empty($errors['contact_discord_invite_url']) ? 'is-invalid' : ''; ?>" type="url" id="contact_discord_invite_url" name="contact_discord_invite_url" value="<?php echo htmlspecialchars($s['contact_discord_invite_url'] ?? ''); ?>" maxlength="500" placeholder="https://discord.gg/… hoặc https://discord.com/…" autocomplete="off">
        <small class="form-text settings-help-text">Chỉ <code>https://</code> tới <code>discord.gg</code> hoặc <code>discord.com</code>. Để trống nếu chưa có — toàn bộ ô terminal phía trên sẽ chỉ hiển thị dạng chữ.</small>
        <?php if (!empty($errors['contact_discord_invite_url'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_discord_invite_url']); ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group mb-3">
        <label for="contact_gate_cta_body" class="col-form-label">Đoạn mô tả trên nút Tạo Ticket</label>
        <textarea class="form-control <?php echo !empty($errors['contact_gate_cta_body']) ? 'is-invalid' : ''; ?>" id="contact_gate_cta_body" name="contact_gate_cta_body" rows="3" maxlength="600"><?php echo htmlspecialchars($s['contact_gate_cta_body'] ?? ''); ?></textarea>
        <?php if (!empty($errors['contact_gate_cta_body'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_gate_cta_body']); ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group mb-3">
        <label for="contact_gate_cta_button" class="col-form-label">Nhãn nút Tạo Ticket</label>
        <input class="form-control <?php echo !empty($errors['contact_gate_cta_button']) ? 'is-invalid' : ''; ?>" type="text" id="contact_gate_cta_button" name="contact_gate_cta_button" value="<?php echo htmlspecialchars($s['contact_gate_cta_button'] ?? ''); ?>" maxlength="60">
        <?php if (!empty($errors['contact_gate_cta_button'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_gate_cta_button']); ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group mb-2">
        <label for="site_map_embed_url" class="col-form-label">URL nhúng Google Maps (cổng Liên hệ)</label>
        <input class="form-control <?php echo !empty($errors['site_map_embed_url']) ? 'is-invalid' : ''; ?>" type="url" id="site_map_embed_url" name="site_map_embed_url" value="<?php echo htmlspecialchars($mapUrl); ?>" placeholder="https://www.google.com/maps?q=...&output=embed" maxlength="500">
        <?php if (!empty($errors['site_map_embed_url'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['site_map_embed_url']); ?></div>
        <?php endif; ?>
    </div>

    <div class="map-preview-block mb-3" id="mapPreviewBlock">
        <div class="map-preview-placeholder <?php echo $showMapPreview ? 'd-none' : ''; ?>" id="mapPreviewPlaceholder">
            <i class="ti-map"></i>
            <p>Bản xem trước sẽ hiển thị tại đây khi URL hợp lệ.</p>
        </div>
        <iframe
            id="mapPreviewFrame"
            class="<?php echo $showMapPreview ? '' : 'd-none'; ?>"
            src="<?php echo $showMapPreview ? htmlspecialchars($mapUrl) : ''; ?>"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Xem trước Google Maps"
        ></iframe>
    </div>

    <hr class="settings-divider">

    <div class="settings-section-header">
        <h3>Support console — Terminal ticket</h3>
        <p>Nhãn công khai trên form ticket, thẻ trạng thái/topology và tiêu đề khu vực chọn danh mục.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_term_title" class="col-form-label">Tiêu đề khung console</label>
                <input class="form-control <?php echo !empty($errors['contact_main_term_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_term_title" name="contact_main_term_title" value="<?php echo htmlspecialchars($s['contact_main_term_title'] ?? ''); ?>" maxlength="160" placeholder="Support Terminal">
                <?php if (!empty($errors['contact_main_term_title'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_term_title']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_cat_heading" class="col-form-label">Tiêu đề khu vực chọn danh mục</label>
                <input class="form-control <?php echo !empty($errors['contact_main_cat_heading']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_cat_heading" name="contact_main_cat_heading" value="<?php echo htmlspecialchars($s['contact_main_cat_heading'] ?? ''); ?>" maxlength="160">
                <?php if (!empty($errors['contact_main_cat_heading'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_cat_heading']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_back" class="col-form-label">Nút quay lại cổng</label>
                <input class="form-control <?php echo !empty($errors['contact_main_back']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_back" name="contact_main_back" value="<?php echo htmlspecialchars($s['contact_main_back'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['contact_main_back'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_back']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_name_label" class="col-form-label">Nhãn ô Tên</label>
                <input class="form-control <?php echo !empty($errors['contact_main_name_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_name_label" name="contact_main_name_label" value="<?php echo htmlspecialchars($s['contact_main_name_label'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_main_name_label'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_name_label']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_email_label" class="col-form-label">Nhãn ô Email</label>
                <input class="form-control <?php echo !empty($errors['contact_main_email_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_email_label" name="contact_main_email_label" value="<?php echo htmlspecialchars($s['contact_main_email_label'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_main_email_label'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_email_label']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_issue_label" class="col-form-label">Nhãn «Loại vấn đề»</label>
                <input class="form-control <?php echo !empty($errors['contact_main_issue_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_issue_label" name="contact_main_issue_label" value="<?php echo htmlspecialchars($s['contact_main_issue_label'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['contact_main_issue_label'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_issue_label']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="contact_main_issue_hint" class="col-form-label">Gợi ý dưới «Loại vấn đề»</label>
        <textarea class="form-control <?php echo !empty($errors['contact_main_issue_hint']) ? 'is-invalid' : ''; ?>" id="contact_main_issue_hint" name="contact_main_issue_hint" rows="2" maxlength="400"><?php echo htmlspecialchars($s['contact_main_issue_hint'] ?? ''); ?></textarea>
        <?php if (!empty($errors['contact_main_issue_hint'])): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_main_issue_hint']); ?></div><?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_msg_label" class="col-form-label">Nhãn ô Nội dung</label>
                <input class="form-control <?php echo !empty($errors['contact_main_msg_label']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_msg_label" name="contact_main_msg_label" value="<?php echo htmlspecialchars($s['contact_main_msg_label'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['contact_main_msg_label'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_msg_label']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_msg_placeholder" class="col-form-label">Placeholder ô Nội dung</label>
                <input class="form-control <?php echo !empty($errors['contact_main_msg_placeholder']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_msg_placeholder" name="contact_main_msg_placeholder" value="<?php echo htmlspecialchars($s['contact_main_msg_placeholder'] ?? ''); ?>" maxlength="500">
                <?php if (!empty($errors['contact_main_msg_placeholder'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_msg_placeholder']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_btn_send" class="col-form-label">Nhãn nút gửi</label>
                <input class="form-control <?php echo !empty($errors['contact_main_btn_send']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_btn_send" name="contact_main_btn_send" value="<?php echo htmlspecialchars($s['contact_main_btn_send'] ?? ''); ?>" maxlength="60">
                <?php if (!empty($errors['contact_main_btn_send'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_btn_send']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_btn_reset" class="col-form-label">Nhãn nút Reset</label>
                <input class="form-control <?php echo !empty($errors['contact_main_btn_reset']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_btn_reset" name="contact_main_btn_reset" value="<?php echo htmlspecialchars($s['contact_main_btn_reset'] ?? ''); ?>" maxlength="40">
                <?php if (!empty($errors['contact_main_btn_reset'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_btn_reset']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_status_title" class="col-form-label">Tiêu đề card trạng thái</label>
                <input class="form-control <?php echo !empty($errors['contact_main_status_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_status_title" name="contact_main_status_title" value="<?php echo htmlspecialchars($s['contact_main_status_title'] ?? ''); ?>" maxlength="160">
                <?php if (!empty($errors['contact_main_status_title'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_status_title']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_status_online" class="col-form-label">Dòng «Support online»</label>
                <input class="form-control <?php echo !empty($errors['contact_main_status_online']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_status_online" name="contact_main_status_online" value="<?php echo htmlspecialchars($s['contact_main_status_online'] ?? ''); ?>" maxlength="120">
                <?php if (!empty($errors['contact_main_status_online'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_status_online']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_topo_title" class="col-form-label">Tiêu đề card topology</label>
                <input class="form-control <?php echo !empty($errors['contact_main_topo_title']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_topo_title" name="contact_main_topo_title" value="<?php echo htmlspecialchars($s['contact_main_topo_title'] ?? ''); ?>" maxlength="160">
                <?php if (!empty($errors['contact_main_topo_title'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_topo_title']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_stat_lbl_1" class="col-form-label">Chỉ số 1 — nhãn</label>
                <input class="form-control <?php echo !empty($errors['contact_main_stat_lbl_1']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_stat_lbl_1" name="contact_main_stat_lbl_1" value="<?php echo htmlspecialchars($s['contact_main_stat_lbl_1'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_main_stat_lbl_1'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_stat_lbl_1']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_stat_val_1" class="col-form-label">Chỉ số 1 — giá trị hiển thị</label>
                <input class="form-control <?php echo !empty($errors['contact_main_stat_val_1']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_stat_val_1" name="contact_main_stat_val_1" value="<?php echo htmlspecialchars($s['contact_main_stat_val_1'] ?? ''); ?>" maxlength="40">
                <?php if (!empty($errors['contact_main_stat_val_1'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_stat_val_1']); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="contact_main_stat_lbl_2" class="col-form-label">Chỉ số 2 — nhãn</label>
                <input class="form-control <?php echo !empty($errors['contact_main_stat_lbl_2']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_stat_lbl_2" name="contact_main_stat_lbl_2" value="<?php echo htmlspecialchars($s['contact_main_stat_lbl_2'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_main_stat_lbl_2'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_stat_lbl_2']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="contact_main_stat_lbl_3" class="col-form-label">Chỉ số 3 — nhãn</label>
                <input class="form-control <?php echo !empty($errors['contact_main_stat_lbl_3']) ? 'is-invalid' : ''; ?>" type="text" id="contact_main_stat_lbl_3" name="contact_main_stat_lbl_3" value="<?php echo htmlspecialchars($s['contact_main_stat_lbl_3'] ?? ''); ?>" maxlength="80">
                <?php if (!empty($errors['contact_main_stat_lbl_3'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_main_stat_lbl_3']); ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <p class="settings-help-text small mb-3">Để trống các ô layout chính = trang client dùng mặc định hệ thống.</p>

    <div class="settings-section-header">
        <h3>Danh mục ticket</h3>
        <p>Tên danh mục (tiêu đề thẻ) lấy từ hệ thống; bạn chỉnh mô tả thẻ và nhãn/placeholder các ô riêng của từng loại.</p>
    </div>

    <?php foreach ($ticketCats as $slug => $catLabel): ?>
        <?php
        $descKey = 'contact_cat_desc_' . $slug;
        $descVal = htmlspecialchars($s[$descKey] ?? '');
        ?>
        <details class="settings-field-group contact-settings-cat-details rounded p-3 mb-3">
            <summary class="contact-settings-cat-summary h6 mb-0">
                <span class="contact-settings-cat-chevron" aria-hidden="true"><i class="ti-angle-right"></i></span>
                <span class="contact-settings-cat-summary-main">
                    <?php echo htmlspecialchars($catLabel); ?> <code class="contact-settings-cat-slug small"><?php echo htmlspecialchars($slug); ?></code>
                </span>
            </summary>
            <div class="mt-3">
                <div class="form-group mb-3">
                    <label class="col-form-label" for="<?php echo htmlspecialchars($descKey); ?>">Mô tả trên thẻ danh mục</label>
                    <textarea class="form-control <?php echo !empty($errors[$descKey]) ? 'is-invalid' : ''; ?>" id="<?php echo htmlspecialchars($descKey); ?>" name="<?php echo htmlspecialchars($descKey); ?>" rows="2" maxlength="300"><?php echo $descVal; ?></textarea>
                    <?php if (!empty($errors[$descKey])): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors[$descKey]); ?></div><?php endif; ?>
                    <?php if ($slug === 'purchase_issue'): ?>
                        <small class="form-text settings-help-text">Khi chưa đăng nhập, client vẫn hiển thị câu có liên kết «đăng nhập» cố định; giá trị này dùng khi đã đăng nhập.</small>
                    <?php endif; ?>
                </div>

                <?php if ($slug === 'purchase_issue'): ?>
                    <div class="form-group mb-2">
                        <label class="col-form-label" for="contact_form_purchase_order_lbl">Nhãn khối đơn hàng</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_purchase_order_lbl']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_purchase_order_lbl" name="contact_form_purchase_order_lbl" value="<?php echo htmlspecialchars($s['contact_form_purchase_order_lbl'] ?? ''); ?>" maxlength="160">
                        <?php if (!empty($errors['contact_form_purchase_order_lbl'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_purchase_order_lbl']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group mb-2">
                        <label class="col-form-label" for="contact_form_purchase_guest">Thông báo khi chưa đăng nhập</label>
                        <textarea class="form-control <?php echo !empty($errors['contact_form_purchase_guest']) ? 'is-invalid' : ''; ?>" id="contact_form_purchase_guest" name="contact_form_purchase_guest" rows="2" maxlength="300"><?php echo htmlspecialchars($s['contact_form_purchase_guest'] ?? ''); ?></textarea>
                        <?php if (!empty($errors['contact_form_purchase_guest'])): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['contact_form_purchase_guest']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group mb-2">
                        <label class="col-form-label" for="contact_form_purchase_empty">Khi không có đơn pending</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_purchase_empty']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_purchase_empty" name="contact_form_purchase_empty" value="<?php echo htmlspecialchars($s['contact_form_purchase_empty'] ?? ''); ?>" maxlength="300">
                        <?php if (!empty($errors['contact_form_purchase_empty'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_purchase_empty']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group mb-0">
                        <label class="col-form-label" for="contact_form_purchase_opt">Nhãn mục chọn đơn (placeholder)</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_purchase_opt']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_purchase_opt" name="contact_form_purchase_opt" value="<?php echo htmlspecialchars($s['contact_form_purchase_opt'] ?? ''); ?>" maxlength="120">
                        <?php if (!empty($errors['contact_form_purchase_opt'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_purchase_opt']); ?></div><?php endif; ?>
                    </div>
                <?php elseif ($slug === 'forgot_password'): ?>
                    <div class="form-group mb-2">
                        <label class="col-form-label" for="contact_form_forgot_pw_lbl">Nhãn ô mật khẩu trước đó</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_forgot_pw_lbl']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_forgot_pw_lbl" name="contact_form_forgot_pw_lbl" value="<?php echo htmlspecialchars($s['contact_form_forgot_pw_lbl'] ?? ''); ?>" maxlength="160">
                        <?php if (!empty($errors['contact_form_forgot_pw_lbl'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_forgot_pw_lbl']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group mb-0">
                        <label class="col-form-label" for="contact_form_forgot_pw_ph">Placeholder</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_forgot_pw_ph']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_forgot_pw_ph" name="contact_form_forgot_pw_ph" value="<?php echo htmlspecialchars($s['contact_form_forgot_pw_ph'] ?? ''); ?>" maxlength="300">
                        <?php if (!empty($errors['contact_form_forgot_pw_ph'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_forgot_pw_ph']); ?></div><?php endif; ?>
                    </div>
                <?php elseif ($slug === 'banned'): ?>
                    <div class="form-group mb-2">
                        <label class="col-form-label" for="contact_form_banned_user_lbl">Nhãn ô username</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_banned_user_lbl']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_banned_user_lbl" name="contact_form_banned_user_lbl" value="<?php echo htmlspecialchars($s['contact_form_banned_user_lbl'] ?? ''); ?>" maxlength="120">
                        <?php if (!empty($errors['contact_form_banned_user_lbl'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_banned_user_lbl']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group mb-0">
                        <label class="col-form-label" for="contact_form_banned_user_ph">Placeholder</label>
                        <input class="form-control <?php echo !empty($errors['contact_form_banned_user_ph']) ? 'is-invalid' : ''; ?>" type="text" id="contact_form_banned_user_ph" name="contact_form_banned_user_ph" value="<?php echo htmlspecialchars($s['contact_form_banned_user_ph'] ?? ''); ?>" maxlength="300">
                        <?php if (!empty($errors['contact_form_banned_user_ph'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['contact_form_banned_user_ph']); ?></div><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="small settings-help-text mb-0">Loại này không có ô form bổ sung ngoài nội dung chung.</p>
                <?php endif; ?>
            </div>
        </details>
    <?php endforeach; ?>

    <div class="settings-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="<?php echo URLROOT; ?>/admin/settings/contact" class="btn btn-outline-light settings-actions-cancel">Hủy</a>
    </div>
</form>