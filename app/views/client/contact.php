<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<?php
$h = static function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
};
$publicSettings = $data['public_settings'] ?? [];
$cStr = static function ($key, $fallback) use ($publicSettings) {
    $v = trim((string) ($publicSettings[$key] ?? ''));
    return $v !== '' ? $v : $fallback;
};
$siteMapEmbedUrl = $publicSettings['site_map_embed_url'] ?? 'https://www.google.com/maps?q=268+Ly+Thuong+Kiet+Q10+TPHCM&output=embed';
$gateHeadline = trim((string) ($publicSettings['contact_gate_headline'] ?? 'Trung tâm'));
$gateAccent = trim((string) ($publicSettings['contact_gate_headline_accent'] ?? 'hỗ trợ'));
$gateSubtitle = trim((string) ($publicSettings['contact_gate_subtitle'] ?? ''));
$nodeCardTitle = trim((string) ($publicSettings['contact_node_card_title'] ?? 'Support Node VN-01'));
$nodeRegion = trim((string) ($publicSettings['contact_node_region'] ?? 'Ho Chi Minh City'));
$nodeOnline = trim((string) ($publicSettings['contact_node_online_label'] ?? 'Online'));
$nodeLatencyLbl = trim((string) ($publicSettings['contact_node_latency_label'] ?? 'Latency'));
$gateCtaBody = trim((string) ($publicSettings['contact_gate_cta_body'] ?? ''));
$gateCtaBtn = trim((string) ($publicSettings['contact_gate_cta_button'] ?? 'Tạo Ticket'));
$discordInviteRaw = trim((string) ($publicSettings['contact_discord_invite_url'] ?? ''));
$discordInviteUrl = '';
if ($discordInviteRaw !== '' && filter_var($discordInviteRaw, FILTER_VALIDATE_URL) !== false) {
    $pu = @parse_url($discordInviteRaw);
    $sch = isset($pu['scheme']) ? strtolower((string) $pu['scheme']) : '';
    $host = isset($pu['host']) ? strtolower((string) $pu['host']) : '';
    if ($sch === 'https' && in_array($host, ['discord.gg', 'discord.com', 'www.discord.com'], true)) {
        $discordInviteUrl = $discordInviteRaw;
    }
}
$discordRaw = trim((string) ($publicSettings['contact_discord_typed_block'] ?? ''));
$normDiscord = str_replace(["\r\n", "\r"], "\n", $discordRaw);
$discordLines = $normDiscord === '' ? [] : explode("\n", $normDiscord);
while (!empty($discordLines) && trim((string) end($discordLines)) === '') {
    array_pop($discordLines);
}
$discordTypeText = $discordRaw;
$discordAnchorText = '';
if ($discordInviteUrl !== '') {
    if (count($discordLines) >= 2) {
        $discordAnchorText = (string) array_pop($discordLines);
        $discordTypeText = implode("\n", $discordLines);
    } elseif (count($discordLines) === 1) {
        $discordAnchorText = (string) $discordLines[0];
        $discordTypeText = '';
    }
}
if ($discordInviteUrl === '') {
    $discordTypeText = $discordRaw !== '' ? $discordRaw : "> relay / discord #support-hq … CONNECTED\n> channel latency … 42ms";
    $discordAnchorText = '';
}
$isLoggedIn = !empty($data['is_logged_in']);
$categories = $data['support_ticket_categories'] ?? [];
$form = $data['form'] ?? [];
$errors = $data['errors'] ?? [];
$pendingOrders = $data['pending_orders'] ?? [];
$initialStep = ($data['contact_initial_step'] ?? 'gate') === 'main' ? 'main' : 'gate';
$formCat = isset($form['ticket_category']) && isset($categories[$form['ticket_category']]) ? $form['ticket_category'] : 'bugs_technical';
$mainTermTitle = $cStr('contact_main_term_title', 'Support Terminal');
$mainNameLbl = $cStr('contact_main_name_label', 'Tên');
$mainEmailLbl = $cStr('contact_main_email_label', 'Email');
$mainIssueLbl = $cStr('contact_main_issue_label', 'Loại vấn đề');
$mainIssueHint = $cStr('contact_main_issue_hint', 'Chọn nhanh bằng các thẻ danh mục phía dưới trang.');
$mainMsgLbl = $cStr('contact_main_msg_label', 'Nội dung');
$mainMsgPh = $cStr('contact_main_msg_placeholder', '> Mô tả chi tiết lỗi, bước tái hiện, mã đơn (nếu có)…');
$mainBtnSend = $cStr('contact_main_btn_send', 'Gửi Ticket');
$mainBtnReset = $cStr('contact_main_btn_reset', 'Reset');
$mainCatHeading = $cStr('contact_main_cat_heading', 'Chọn danh mục ticket');
$mainBack = $cStr('contact_main_back', '← Quay lại');
$mainStatusTitle = $cStr('contact_main_status_title', 'Trạng thái hệ thống hỗ trợ');
$mainStatusOnline = $cStr('contact_main_status_online', 'Support online');
$mainTopoTitle = $cStr('contact_main_topo_title', 'Network topology');
$mainStatLbl1 = $cStr('contact_main_stat_lbl_1', 'Avg response');
$mainStatVal1 = $cStr('contact_main_stat_val_1', '~3m');
$mainStatLbl2 = $cStr('contact_main_stat_lbl_2', 'Active engineers');
$mainStatLbl3 = $cStr('contact_main_stat_lbl_3', 'Nodes healthy');
$formPurchaseOrderLbl = $cStr('contact_form_purchase_order_lbl', 'Đơn hàng (pending)');
$formPurchaseGuest = $cStr('contact_form_purchase_guest', 'Đăng nhập để chọn đơn hàng chờ xử lý.');
$formPurchaseEmpty = $cStr('contact_form_purchase_empty', 'Không có đơn pending.');
$formPurchaseOpt = $cStr('contact_form_purchase_opt', '— Chọn đơn —');
$formForgotPwLbl = $cStr('contact_form_forgot_pw_lbl', 'Mật khẩu trước đó (tuỳ chọn)');
$formForgotPwPh = $cStr('contact_form_forgot_pw_ph', 'Nhập mật khẩu gần nhất của bạn.');
$formBannedUserLbl = $cStr('contact_form_banned_user_lbl', 'Username');
$formBannedUserPh = $cStr('contact_form_banned_user_ph', 'Tên đăng nhập cần hỗ trợ');
$cssVer = @filemtime(dirname(APPROOT) . '/public/css/contact-support.css') ?: '1';
$cfgJson = json_encode([
    'initialStep' => $initialStep,
    'defaultCategory' => $formCat,
    'categoryLabels' => $categories,
    'isLoggedIn' => $isLoggedIn,
    'discordTypeText' => $discordTypeText,
    'discordInviteUrl' => $discordInviteUrl,
    'discordAnchorText' => $discordAnchorText,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
?>
<link rel="stylesheet" href="<?php echo URLROOT; ?>/css/contact-support.css?v=<?php echo (int) $cssVer; ?>">
<script type="application/json" id="contact-support-config"><?php echo $cfgJson; ?></script>

<?php
$catIcons = [
    'purchase_issue' => 'fa-cart-shopping',
    'forgot_password' => 'fa-key',
    'bugs_technical' => 'fa-bug',
    'banned' => 'fa-user-slash',
    'billing_payment' => 'fa-file-invoice-dollar',
    'others' => 'fa-message',
];
$catDescDefaults = [
    'purchase_issue' => 'Chọn đơn pending trong form',
    'forgot_password' => 'Khôi phục truy cập tài khoản',
    'bugs_technical' => 'Lỗi kỹ thuật & máy chủ',
    'banned' => 'Khiếu nại khóa / blacklist',
    'billing_payment' => 'Hóa đơn, thanh toán, hoàn tiền',
    'others' => 'Các vấn đề khác',
];
$catMeta = [];
foreach ($categories as $ck => $_cl) {
    $catMeta[$ck] = [
        'icon' => $catIcons[$ck] ?? 'fa-circle',
        'desc' => $cStr('contact_cat_desc_' . $ck, $catDescDefaults[$ck] ?? ''),
    ];
}
?>

<div class="bg-gray-950 py-16 min-h-[85vh] text-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Layout phụ: cổng Support -->
        <div id="support-step-gate" class="<?php echo $initialStep === 'main' ? 'support-hidden-step' : ''; ?>">
            <div class="flex flex-col gap-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10 items-start">
                    <div class="space-y-3 lg:pr-2">
                        <h2 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white leading-[1.08] tracking-tight">
                            <?php echo $h($gateHeadline); ?>
                            <span class="text-sky-400"><?php echo $h($gateAccent); ?></span>
                        </h2>
                        <?php if ($gateSubtitle !== ''): ?>
                            <p class="text-gray-400 text-base md:text-lg lg:text-xl leading-relaxed max-w-xl"><?php echo $h($gateSubtitle); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="w-full lg:max-w-md lg:justify-self-end space-y-6">
                        <div class="hero-floating-card support-gate-node-float">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <span class="text-sm font-semibold text-white"><?php echo $h($nodeCardTitle); ?></span>
                                <span class="flex items-center gap-2 text-xs text-emerald-300">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400 support-pulse-dot"></span>
                                    </span>
                                    <?php echo $h($nodeOnline); ?>
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mb-1"><?php echo $h($nodeRegion); ?></div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-gray-400 text-sm"><?php echo $h($nodeLatencyLbl); ?></span>
                                <span class="text-2xl font-mono text-cyan-300" data-metric="vn-latency">12ms</span>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/50 px-3 py-2.5 support-discord-terminal text-emerald-400/90 support-discord-terminal-box">
                            <span id="support-discord-typed" class="typed-mount whitespace-pre-wrap block support-discord-typed-mount"></span>
                        </div>
                    </div>
                </div>

                <div class="w-full rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-gray-900/80 to-gray-950/90 p-6 md:p-8 shadow-lg shadow-cyan-500/5 text-center">
                    <?php if ($gateCtaBody !== ''): ?>
                        <p class="text-gray-400 text-sm md:text-base mb-5 leading-relaxed max-w-4xl mx-auto"><?php echo $h($gateCtaBody); ?></p>
                    <?php endif; ?>
                    <button type="button" id="support-open-console" class="hvr-buzz inline-flex items-center justify-center px-8 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-sm tracking-wide transition-all shadow-lg shadow-cyan-500/20">
                    <i class="ti-plus text-white" style="margin-right: 0.7em;" aria-hidden="true"></i>
                        <?php echo $h($gateCtaBtn); ?>
                    </button>
                </div>
            </div>

            <div class="mt-12">
                <h3 class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-3"><i class="ti-map text-blue-400" style="margin-right: 0.6em;" aria-hidden="true"></i>Định vị</h3>
                <div class="rounded-2xl border border-white/5 overflow-hidden bg-gray-900/30">
                    <iframe
                        src="<?php echo $h($siteMapEmbedUrl); ?>"
                        class="w-full h-64 md:h-72 border-0 contrast-125 opacity-90"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Bản đồ văn phòng Cloud Arena"
                    ></iframe>
                </div>
            </div>
        </div>

        <!-- Layout chính: console + cards + categories -->
        <div id="support-step-main" class="<?php echo $initialStep === 'gate' ? 'support-hidden-step' : ''; ?> space-y-12">
            <?php if (!empty($data['success_message'])): ?>
                <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-300 text-sm support-flash-success">
                    <?php echo $h($data['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-red-300 text-sm">
                    <?php echo $h($errors['general']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 xl:gap-10 items-start">
                <!-- Form console -->
                <div class="support-console-wrap xl:col-span-2 rounded-2xl border border-white/10 bg-gray-900/40 p-6 md:p-8 backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-white/10 pb-4 mb-6">
                        <span class="text-red-400 text-xs">●</span><span class="text-amber-300 text-xs">●</span><span class="text-emerald-400 text-xs">●</span>
                        <h2 class="ml-2 text-lg font-bold text-white tracking-tight"><?php echo $h($mainTermTitle); ?></h2>
                    </div>

                    <form id="support-ticket-form" action="<?php echo URLROOT; ?>/contact" method="POST" class="space-y-5 relative" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $h($data['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="ticket_category" id="ticket_category" value="<?php echo $h($formCat); ?>">
                        <div class="absolute -left-[9999px] opacity-0 pointer-events-none" aria-hidden="true">
                            <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
                        </div>

                        <div id="support-client-error-banner" class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-red-300 text-sm hidden" role="alert"></div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($mainNameLbl); ?></label>
                                <?php
                                $nameClass = 'w-full px-3 py-2.5 rounded-lg text-sm border focus:outline-none focus:ring-1 focus:ring-cyan-500/50 ';
                                $nameClass .= $isLoggedIn ? 'bg-gray-800/50 border-white/5 text-gray-400 cursor-not-allowed' : 'bg-black/40 text-white ' . (!empty($errors['name']) ? 'border-red-500' : 'border-white/10');
                                ?>
                                <input id="support-field-name" type="text" name="name" value="<?php echo $h($form['name'] ?? ''); ?>"
                                    class="<?php echo $h($nameClass); ?>"
                                    <?php echo $isLoggedIn ? 'readonly' : ''; ?>
                                >
                                <p id="support-err-name" class="mt-1 text-xs text-red-400"><?php echo $h($errors['name'] ?? ''); ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($mainEmailLbl); ?></label>
                                <?php
                                $emailClass = 'w-full px-3 py-2.5 rounded-lg text-sm border focus:outline-none focus:ring-1 focus:ring-cyan-500/50 ';
                                $emailClass .= $isLoggedIn ? 'bg-gray-800/50 border-white/5 text-gray-400 cursor-not-allowed' : 'bg-black/40 text-white ' . (!empty($errors['email']) ? 'border-red-500' : 'border-white/10');
                                ?>
                                <input id="support-field-email" type="email" name="email" value="<?php echo $h($form['email'] ?? ''); ?>"
                                    class="<?php echo $h($emailClass); ?>"
                                    <?php echo $isLoggedIn ? 'readonly' : ''; ?>
                                >
                                <p id="support-err-email" class="mt-1 text-xs text-red-400"><?php echo $h($errors['email'] ?? ''); ?></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($mainIssueLbl); ?></label>
                            <p class="text-sm text-gray-400 mb-2"><?php echo $h($mainIssueHint); ?></p>
                            <div class="text-xs font-mono text-cyan-300/80 border border-dashed border-cyan-500/25 rounded-lg px-3 py-2 bg-black/30">
                                active_category = <span id="support-category-label"><?php echo $h($categories[$formCat] ?? $formCat); ?></span>
                            </div>
                            <p id="support-err-ticket-cat" class="mt-1 text-xs text-red-400"><?php echo $h($errors['ticket_category'] ?? ''); ?></p>
                        </div>

                        <div data-support-panel="purchase_issue" class="<?php echo $formCat !== 'purchase_issue' ? 'hidden' : ''; ?>">
                            <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($formPurchaseOrderLbl); ?></label>
                            <?php if (!$isLoggedIn): ?>
                                <p class="text-sm text-amber-300/90"><?php echo $h($formPurchaseGuest); ?></p>
                            <?php elseif (empty($pendingOrders)): ?>
                                <p class="text-sm text-gray-500"><?php echo $h($formPurchaseEmpty); ?></p>
                            <?php else: ?>
                                <select name="order_id" id="support_pending_order" class="w-full px-3 py-2.5 bg-black/40 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:ring-1 focus:ring-cyan-500/50" data-admin-custom-select="true">
                                    <option value=""><?php echo $h($formPurchaseOpt); ?></option>
                                    <?php foreach ($pendingOrders as $o): ?>
                                        <?php
                                        $oid = (int) $o->id;
                                        $sel = ((string) ($form['order_id'] ?? '') === (string) $oid) ? ' selected' : '';
                                        $lbl = '#' . $oid . ' — ' . date('d/m/Y', strtotime((string) $o->created_at)) . ' — ' . number_format((float) $o->total_amount, 0, ',', '.') . '₫';
                                        if (!empty($o->items_label)) {
                                            $lbl .= ' — ' . $o->items_label;
                                        }
                                        ?>
                                        <option value="<?php echo $oid; ?>"<?php echo $sel; ?>><?php echo $h($lbl); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                            <p id="support-err-order" class="mt-1 text-xs text-red-400"><?php echo $h($errors['order_id'] ?? ''); ?></p>
                        </div>

                        <div data-support-panel="forgot_password" class="<?php echo $formCat !== 'forgot_password' ? 'hidden' : ''; ?>">
                            <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5" for="support_previous_password"><?php echo $h($formForgotPwLbl); ?></label>
                            <div class="support-forgot-pw-wrap">
                                <input type="password" name="previous_password" id="support_previous_password" autocomplete="new-password"
                                    class="support-forgot-pw-input w-full px-3 py-2.5 bg-black/40 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                                    placeholder="<?php echo $h($formForgotPwPh); ?>">
                                <button type="button" class="support-pw-toggle" data-support-pw-toggle="forgot" aria-label="Hiện mật khẩu" aria-pressed="false" title="Hiện mật khẩu">
                                    <i class="ti-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p id="support-err-prevpw" class="mt-1 text-xs text-red-400"><?php echo $h($errors['previous_password'] ?? ''); ?></p>
                        </div>

                        <div data-support-panel="banned" class="<?php echo $formCat !== 'banned' ? 'hidden' : ''; ?>">
                            <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($formBannedUserLbl); ?></label>
                            <input id="support-field-banned-username" type="text" name="banned_username" value="<?php echo $h($form['banned_username'] ?? ''); ?>"
                                class="w-full px-3 py-2.5 bg-black/40 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                                placeholder="<?php echo $h($formBannedUserPh); ?>">
                            <p id="support-err-banned" class="mt-1 text-xs text-red-400"><?php echo $h($errors['banned_username'] ?? ''); ?></p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-cyan-500/90 uppercase tracking-wider mb-1.5"><?php echo $h($mainMsgLbl); ?></label>
                            <textarea id="support-field-message" name="message" rows="7" required minlength="10" maxlength="5000"
                                class="support-terminal-field w-full px-3 py-3 bg-black/60 border rounded-lg text-sm <?php echo !empty($errors['message']) ? 'border-red-500' : 'border-emerald-500/30'; ?> text-emerald-100/95 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-cyan-500/50 leading-relaxed"
                                placeholder="<?php echo $h($mainMsgPh); ?>"><?php echo $h($form['message'] ?? ''); ?></textarea>
                            <p id="support-err-message" class="mt-1 text-xs text-red-400"><?php echo $h($errors['message'] ?? ''); ?></p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2 sm:items-center">
                            <button type="submit" class="relative sm:flex-none sm:min-w-[9rem] py-2.5 px-6 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-sm transition-all disabled:opacity-60">
                                <span class="support-btn-label"><?php echo $h($mainBtnSend); ?></span>
                                <span class="support-btn-spinner" aria-hidden="true"></span>
                            </button>
                            <button type="button" id="support-form-reset" class="sm:flex-none py-2.5 px-5 rounded-xl border border-white/15 text-gray-200 font-semibold text-sm hover:bg-white/5 transition-colors">
                                <?php echo $h($mainBtnReset); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cột phải: 2 card -->
                <div class="space-y-6 xl:col-span-1">
                    <div class="support-status-card rounded-2xl border border-white/10 bg-gray-900/50 p-6 backdrop-blur-md">
                        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-signal text-cyan-400"></i> <?php echo $h($mainStatusTitle); ?>
                        </h3>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="relative flex h-3 w-3 support-glow-indicator">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400 support-pulse-dot"></span>
                            </span>
                            <span class="text-emerald-300 font-medium"><?php echo $h($mainStatusOnline); ?></span>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-400 font-mono">
                            <li class="flex justify-between gap-4"><span><?php echo $h($mainStatLbl1); ?></span><span class="text-cyan-300"><?php echo $h($mainStatVal1); ?></span></li>
                            <li class="flex justify-between gap-4"><span><?php echo $h($mainStatLbl2); ?></span><span class="text-cyan-300" data-metric="engineers">3</span></li>
                            <li class="flex justify-between gap-4"><span><?php echo $h($mainStatLbl3); ?></span><span class="text-cyan-300" data-metric="nodes-healthy">98.0%</span></li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-gray-900/50 p-6 backdrop-blur-md overflow-hidden">
                        <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-network-wired text-cyan-400"></i> <?php echo $h($mainTopoTitle); ?>
                        </h3>
                    <div class="support-topology-stage relative rounded-xl overflow-hidden h-52 border border-white/10 bg-gray-950/90">
                        <div class="support-topology-layer-bg absolute inset-0 z-0" aria-hidden="true"></div>
                        <div class="relative z-[1] h-full w-full flex items-center justify-center p-1">
                            <svg id="support-topology-svg" class="w-full h-full max-h-[200px]" viewBox="0 0 320 200" aria-hidden="true">
                                <defs>
                                    <filter id="support-topo-glow" x="-50%" y="-50%" width="200%" height="200%">
                                        <feGaussianBlur stdDeviation="3" result="blur"/>
                                        <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                                    </filter>
                                </defs>
                                <path id="support-path-sg-edge" class="support-topo-path-anim" d="M 42 36 Q 96 70 160 100" fill="none" stroke="rgba(168,85,247,0.4)" stroke-width="2" stroke-dasharray="6 10"/>
                                <path id="support-path-tk-edge" class="support-topo-path-anim" d="M 278 36 Q 224 70 160 100" fill="none" stroke="rgba(34,211,238,0.35)" stroke-width="2" stroke-dasharray="6 10"/>
                                <path id="support-path-vn-edge" class="support-topo-path-anim" d="M 46 162 Q 96 138 160 100" fill="none" stroke="rgba(52,211,153,0.35)" stroke-width="2" stroke-dasharray="6 10"/>
                                <circle class="support-topo-node" cx="42" cy="36" r="8" fill="rgba(168,85,247,0.35)" stroke="#a855f7" stroke-width="2" filter="url(#support-topo-glow)"/>
                                <circle class="support-topo-node" cx="278" cy="36" r="8" fill="rgba(34,211,238,0.35)" stroke="#22d3ee" stroke-width="2" filter="url(#support-topo-glow)"/>
                                <circle class="support-topo-node" cx="46" cy="162" r="8" fill="rgba(52,211,153,0.3)" stroke="#34d399" stroke-width="2" filter="url(#support-topo-glow)"/>
                                <circle class="support-topo-node support-topo-node--hub" cx="160" cy="100" r="11" fill="rgba(34,211,238,0.4)" stroke="#22d3ee" stroke-width="2" filter="url(#support-topo-glow)"/>
                                <circle class="support-topo-packet" cx="42" cy="36" r="4" fill="#a855f7" opacity="0.95"/>
                                <circle class="support-topo-packet" cx="278" cy="36" r="4" fill="#22d3ee" opacity="0.95"/>
                                <circle class="support-topo-packet" cx="46" cy="162" r="4" fill="#34d399" opacity="0.95"/>
                            </svg>
                        </div>
                        <div class="support-topology-labels absolute inset-0 z-[2] pointer-events-none text-[10px] sm:text-xs text-gray-200 font-medium tracking-wide" aria-hidden="true">
                            <span class="support-topo-lbl support-topo-lbl--sg"><span class="support-topo-flag">🇸🇬</span> Singapore</span>
                            <span class="support-topo-lbl support-topo-lbl--tk"><span class="support-topo-flag">🇯🇵</span> Tokyo</span>
                            <span class="support-topo-lbl support-topo-lbl--vn"><span class="support-topo-flag">🇻🇳</span> Vietnam</span>
                            <span class="support-topo-lbl support-topo-lbl--edge"><span class="support-topo-flag">🇻🇳</span> VN Edge</span>
                            <div class="support-topo-status">
                                <span class="support-topo-status-dot" aria-hidden="true">●</span>
                                <span data-topology-online>Online</span>
                                <span class="support-topo-status-realtime">realtime</span>
                                <span class="support-topo-status-sep">|</span>
                                <span class="font-mono text-cyan-300/95" data-topology-latency>12ms</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Category cards -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4"><?php echo $h($mainCatHeading); ?></h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $loginUrl = URLROOT . '/users/login'; ?>
                    <?php foreach ($categories as $key => $label): ?>
                        <?php $meta = $catMeta[$key] ?? ['icon' => 'fa-circle', 'desc' => '']; ?>
                        <div role="button" tabindex="0" data-support-category="<?php echo $h($key); ?>"
                            class="support-category-card text-left rounded-2xl border border-white/10 bg-gray-900/40 p-5 hover:border-cyan-500/30 transition-shadow outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/60 cursor-pointer <?php echo $key === 'purchase_issue' && !$isLoggedIn ? 'opacity-60' : ''; ?>">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-400">
                                    <i class="fa-solid <?php echo $h($meta['icon']); ?>"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-white text-sm"><?php echo $h($label); ?></div>
                                    <?php if ($key === 'purchase_issue' && !$isLoggedIn): ?>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Cần <a href="<?php echo $h($loginUrl); ?>" class="text-cyan-400 hover:underline focus:outline-none focus-visible:ring-1 focus-visible:ring-cyan-400/80 rounded-sm">đăng nhập</a> — chọn đơn pending
                                        </p>
                                    <?php else: ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo $h($meta['desc']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="text-center">
                <button type="button" id="support-back-gate" class="support-back-btn inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-white/15 bg-gray-900/50 text-sm text-cyan-300 hover:bg-white/5 hover:border-cyan-500/40 transition-colors">
                    <?php echo $h($mainBack); ?>
                </button>
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    document.getElementById('support-back-gate') && document.getElementById('support-back-gate').addEventListener('click', function () {
        var g = document.getElementById('support-step-gate');
        var m = document.getElementById('support-step-main');
        if (g && m) { m.classList.add('support-hidden-step'); g.classList.remove('support-hidden-step'); }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>