<?php
class Setting {
    private $db;
    private $publicKeys = [
        'site_logo_text',
        'site_logo_image',
        'site_hotline',
        'site_contact_email',
        'site_address',
        'site_about_snippet',
        'site_map_embed_url',
        'home_hero_title_gradient',
        'home_hero_title_plain',
        'home_hero_subtitle',
        'home_hero_bg_image',
        'home_card_tech_title',
        'home_review_key',
        'home_product_ids',
        'home_about_kicker',
        'home_about_heading',
        'home_about_lead',
        'home_about_feat1_title',
        'home_about_feat1_text',
        'home_about_feat2_title',
        'home_about_feat2_text',
        'home_about_feat3_title',
        'home_about_feat3_text',
        'profile_page_title',
        'profile_page_intro',
        'profile_section_avatar_title',
        'profile_avatar_upload_label',
        'profile_avatar_hint',
        'profile_section_personal_title',
        'profile_section_password_title',
        'profile_label_display_name',
        'profile_label_email',
        'profile_label_current_password',
        'profile_label_new_password',
        'profile_label_confirm_password',
        'profile_btn_save',
        'profile_btn_update_password',
        'contact_gate_headline',
        'contact_gate_headline_accent',
        'contact_gate_subtitle',
        'contact_node_card_title',
        'contact_node_region',
        'contact_node_online_label',
        'contact_node_latency_label',
        'contact_gate_cta_body',
        'contact_gate_cta_button',
        'contact_discord_typed_block',
        'contact_discord_invite_url',
        'contact_page_title',
        'contact_page_intro',
        'contact_sidebar_title',
        'contact_main_term_title',
        'contact_main_name_label',
        'contact_main_email_label',
        'contact_main_issue_label',
        'contact_main_issue_hint',
        'contact_main_msg_label',
        'contact_main_msg_placeholder',
        'contact_main_btn_send',
        'contact_main_btn_reset',
        'contact_main_cat_heading',
        'contact_main_back',
        'contact_main_status_title',
        'contact_main_status_online',
        'contact_main_topo_title',
        'contact_main_stat_lbl_1',
        'contact_main_stat_val_1',
        'contact_main_stat_lbl_2',
        'contact_main_stat_lbl_3',
        'contact_cat_desc_purchase_issue',
        'contact_cat_desc_forgot_password',
        'contact_cat_desc_bugs_technical',
        'contact_cat_desc_banned',
        'contact_cat_desc_billing_payment',
        'contact_cat_desc_others',
        'contact_form_purchase_order_lbl',
        'contact_form_purchase_guest',
        'contact_form_purchase_empty',
        'contact_form_purchase_opt',
        'contact_form_forgot_pw_lbl',
        'contact_form_forgot_pw_ph',
        'contact_form_banned_user_lbl',
        'contact_form_banned_user_ph',
    ];

    private $defaultPublicSettings = [
        'site_logo_text' => 'G-SERVER',
        'site_logo_image' => '',
        'site_hotline' => '0123 456 789',
        'site_contact_email' => 'contact@gameserver.vn',
        'site_address' => '268 Lý Thường Kiệt, Q10, TP.HCM',
        'site_about_snippet' => 'Nền tảng cho thuê Game Server ổn định, hiệu năng cao.',
        'site_map_embed_url' => 'https://www.google.com/maps?q=268+Ly+Thuong+Kiet+Q10+TPHCM&output=embed',
        'home_hero_title_gradient' => 'Game Server Hosting',
        'home_hero_title_plain' => 'Cho Mọi Game Thủ',
        'home_hero_subtitle' => 'Máy chủ game chuyên nghiệp với hiệu năng cao, hỗ trợ modpack và quản lý dễ dàng. Khởi động Server chỉ trong vài phút với công nghệ ảo hóa tiên tiến nhất.',
        'home_hero_bg_image' => '',
        'home_card_tech_title' => 'Năng lực công nghệ',
        'home_review_key' => '',
        'home_product_ids' => '',
        'home_about_kicker' => 'Tính năng vượt trội',
        'home_about_heading' => 'Tại sao chọn G-SERVER?',
        'home_about_lead' => 'Nền tảng tập trung cho cộng đồng game thủ: triển khai nhanh, bảo mật cao và vận hành ổn định xuyên suốt.',
        'home_about_feat1_title' => 'Hiệu năng tối đa',
        'home_about_feat1_text' => 'Sử dụng CPU Intel Core i9 & AMD Ryzen mới nhất, cùng ổ cứng NVMe Gen4 cho tốc độ xử lý vượt trội.',
        'home_about_feat2_title' => 'Anti-DDoS mạnh mẽ',
        'home_about_feat2_text' => 'Lớp bảo vệ đa tầng giúp lọc bỏ các cuộc tấn công DDoS lên đến hàng trăm Gbps, giữ server luôn ổn định.',
        'home_about_feat3_title' => 'Backup tự động',
        'home_about_feat3_text' => 'Dữ liệu của bạn luôn an toàn với hệ thống sao lưu tự động hàng ngày. Khôi phục nhanh chóng khi cần thiết.',
        'profile_page_title' => 'Hồ sơ thành viên',
        'profile_page_intro' => 'Quản lý thông tin tài khoản và bảo mật.',
        'profile_section_avatar_title' => 'Ảnh đại diện',
        'profile_avatar_upload_label' => 'Tải ảnh lên',
        'profile_avatar_hint' => 'JPG, PNG, GIF, WEBP. Tối đa 2MB.',
        'profile_section_personal_title' => 'Thông tin cá nhân',
        'profile_section_password_title' => 'Đổi mật khẩu',
        'profile_label_display_name' => 'Họ tên hiển thị',
        'profile_label_email' => 'Địa chỉ email',
        'profile_label_current_password' => 'Mật khẩu hiện tại',
        'profile_label_new_password' => 'Mật khẩu mới',
        'profile_label_confirm_password' => 'Xác nhận mật khẩu',
        'profile_btn_save' => 'Lưu thay đổi',
        'profile_btn_update_password' => 'Cập nhật mật khẩu',
        'contact_gate_headline' => 'Trung tâm',
        'contact_gate_headline_accent' => 'hỗ trợ',
        'contact_gate_subtitle' => 'Kết nối trực tiếp tới đội kỹ sư Cloud Arena.',
        'contact_node_card_title' => 'Support Node VN-01',
        'contact_node_region' => 'Ho Chi Minh City',
        'contact_node_online_label' => 'Online',
        'contact_node_latency_label' => 'Latency',
        'contact_gate_cta_body' => 'Mở Support Terminal để chọn loại ticket, đính kèm thông tin đơn hàng hoặc bằng chứng kỹ thuật, và gửi yêu cầu được mã hóa an toàn.',
        'contact_gate_cta_button' => 'Tạo Ticket',
        'contact_discord_typed_block' => "> relay / discord #support-hq … CONNECTED\n> channel latency … 42ms\n> join Discord #support-hq _",
        'contact_discord_invite_url' => '',
        'contact_page_title' => 'Liên Hệ',
        'contact_page_intro' => 'Gửi ticket hỗ trợ cho chúng tôi. Đội ngũ sẽ phản hồi sớm nhất có thể.',
        'contact_sidebar_title' => 'Thông tin liên hệ',
        'contact_main_term_title' => 'Support Terminal',
        'contact_main_name_label' => 'Tên',
        'contact_main_email_label' => 'Email',
        'contact_main_issue_label' => 'Loại vấn đề',
        'contact_main_issue_hint' => 'Chọn nhanh bằng các thẻ danh mục phía dưới trang (đồng bộ với ô ẩn).',
        'contact_main_msg_label' => 'Nội dung',
        'contact_main_msg_placeholder' => '> Mô tả chi tiết lỗi, bước tái hiện, mã đơn (nếu có)…',
        'contact_main_btn_send' => 'Gửi Ticket',
        'contact_main_btn_reset' => 'Reset',
        'contact_main_cat_heading' => 'Chọn danh mục ticket',
        'contact_main_back' => '← Quay lại',
        'contact_main_status_title' => 'Trạng thái hệ thống hỗ trợ',
        'contact_main_status_online' => 'Support online',
        'contact_main_topo_title' => 'Network topology',
        'contact_main_stat_lbl_1' => 'Avg response',
        'contact_main_stat_val_1' => '~3m',
        'contact_main_stat_lbl_2' => 'Active engineers',
        'contact_main_stat_lbl_3' => 'Nodes healthy',
        'contact_cat_desc_purchase_issue' => 'Chọn đơn pending trong form',
        'contact_cat_desc_forgot_password' => 'Khôi phục truy cập tài khoản',
        'contact_cat_desc_bugs_technical' => 'Lỗi kỹ thuật & máy chủ',
        'contact_cat_desc_banned' => 'Khiếu nại khóa / blacklist',
        'contact_cat_desc_billing_payment' => 'Hóa đơn, thanh toán, hoàn tiền',
        'contact_cat_desc_others' => 'Các vấn đề khác',
        'contact_form_purchase_order_lbl' => 'Đơn hàng (pending)',
        'contact_form_purchase_guest' => 'Đăng nhập để chọn đơn hàng chờ xử lý.',
        'contact_form_purchase_empty' => 'Không có đơn pending.',
        'contact_form_purchase_opt' => '— Chọn đơn —',
        'contact_form_forgot_pw_lbl' => 'Mật khẩu trước đó (tuỳ chọn)',
        'contact_form_forgot_pw_ph' => 'Được băm bcrypt trước khi lưu — admin chỉ thấy hash',
        'contact_form_banned_user_lbl' => 'Username',
        'contact_form_banned_user_ph' => 'Tên đăng nhập cần hỗ trợ',
    ];

    public function __construct() {
        $this->db = new Database;
    }

    public function getPublicSettings() {
        $settings = $this->defaultPublicSettings;

        $this->db->query('SELECT key_name, value FROM settings');
        $rows = $this->db->resultSet();

        foreach ($rows as $row) {
            if (in_array($row->key_name, $this->publicKeys, true)) {
                $settings[$row->key_name] = $row->value;
            }
        }

        return $settings;
    }

    public function updatePublicSettings($data) {
        foreach ($this->publicKeys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = trim((string) $data[$key]);
            $this->db->query(
                'INSERT INTO settings (key_name, value)
                 VALUES (:key_name, :value)
                 ON DUPLICATE KEY UPDATE value = VALUES(value)'
            );
            $this->db->bind(':key_name', $key);
            $this->db->bind(':value', $value);

            if (!$this->db->execute()) {
                return false;
            }
        }

        return true;
    }

    public function getValueByKey($keyName, $defaultValue = '') {
        $this->db->query(
            'SELECT value
             FROM settings
             WHERE key_name = :key_name
             LIMIT 1'
        );
        $this->db->bind(':key_name', trim((string) $keyName));
        $row = $this->db->single();
        if (!$row) {
            return $defaultValue;
        }
        return (string) $row->value;
    }

    public function upsertValue($keyName, $value) {
        $this->db->query(
            'INSERT INTO settings (key_name, value)
             VALUES (:key_name, :value)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $this->db->bind(':key_name', trim((string) $keyName));
        $this->db->bind(':value', (string) $value);
        return $this->db->execute();
    }
}