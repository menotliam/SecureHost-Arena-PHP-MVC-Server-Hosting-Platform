<?php
$statusLabels = [
    'unread' => 'Chưa đọc',
    'read' => 'Đã đọc',
    'replied' => 'Đã phản hồi'
];
$priorityLabels = [
    'low' => 'Thấp',
    'normal' => 'Bình thường',
    'high' => 'Cao',
    'urgent' => 'Khẩn cấp'
];
$statusBadgeClasses = [
    'unread' => 'pill-status-unread',
    'read' => 'pill-status-read',
    'replied' => 'pill-status-replied'
];
$priorityBadgeClasses = [
    'low' => 'pill-priority-low',
    'normal' => 'pill-priority-normal',
    'high' => 'pill-priority-high',
    'urgent' => 'pill-priority-urgent'
];
$isLockedTicket = $selectedContact && isset($selectedContact->status) && $selectedContact->status === 'replied';
if (!isset($ticketCategoryLabels) || !is_array($ticketCategoryLabels)) {
    $ticketCategoryLabels = [];
}
$customerMessageDisplay = $selectedContact
    ? Contact::customerMessageBodyForDisplay($selectedContact->message ?? '')
    : '';
?>

<?php if (!$selectedContact): ?>
    <div class="panel-header">
        <h2 class="panel-title">Chi tiết ticket</h2>
    </div>
    <p class="panel-muted mb-0">Chọn một ticket từ danh sách để xem nội dung chi tiết.</p>
<?php else: ?>
    <div class="panel-header align-items-start ticket-detail-header">
        <div>
            <h2 class="panel-title">Chi tiết ticket</h2>
        </div>
        <form action="<?php echo URLROOT; ?>/admincontacts/delete/<?php echo (int) $selectedContact->user_id; ?>/<?php echo (int) $selectedContact->contact_id; ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ticket này?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="redirect_query" value="<?php echo htmlspecialchars($queryString, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-sm btn-danger ticket-delete-btn" title="Xóa ticket" aria-label="Xóa ticket">
                <i class="ti-trash"></i>
            </button>
        </form>
    </div>

    <div class="ticket-user-meta mb-3">
        <strong><?php echo htmlspecialchars($selectedContact->name); ?></strong>
        <small>
            <?php echo htmlspecialchars($selectedContact->email); ?>
            | <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($selectedContact->created_at))); ?>
        </small>
        <?php
        $subjRaw = (string) ($selectedContact->subject ?? '');
        if ($subjRaw !== '' && preg_match('/^CA:([^|]+)\|/', $subjRaw, $m) && !empty($ticketCategoryLabels)) {
            $slug = $m[1];
            $lab = isset($ticketCategoryLabels[$slug]) ? $ticketCategoryLabels[$slug] : $slug;
            ?>
            <div class="mt-2 small ticket-detail-ticket-type">
                <span class="ticket-detail-field-label d-block mb-1">Loại ticket</span>
                <span class="ticket-detail-ticket-type-value"><?php echo htmlspecialchars($lab); ?></span>
            </div>
        <?php } ?>
        <?php if (!empty($purchaseComplaintOrderDisplay)): ?>
            <div class="mt-3">
                <span class="ticket-detail-field-label d-block mb-1 small">Đơn hàng khiếu nại</span>
                <div class="ticket-purchase-order-display" role="status"><?php echo htmlspecialchars($purchaseComplaintOrderDisplay); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($bannedTicketUsernameDisplay)): ?>
            <div class="mt-3">
                <span class="ticket-detail-field-label d-block mb-1 small">Tên người dùng</span>
                <div class="ticket-purchase-order-display" role="status"><?php echo htmlspecialchars($bannedTicketUsernameDisplay); ?></div>
            </div>
        <?php endif; ?>
        <?php
        $pwdHash = isset($selectedContact->previous_password_bcrypt) ? trim((string) $selectedContact->previous_password_bcrypt) : '';
        if ($pwdHash !== '' && Contact::isStoredPasswordHashFormat($pwdHash)):
            ?>
            <div class="mt-2 small text-muted">
                <strong>Mật khẩu trước đó:</strong>
                <code class="d-block mt-1 p-2 small rounded border border-secondary text-break user-select-all">Đã cung cấp mật khẩu và được mã hóa</code>
            </div>
        <?php endif; ?>
    </div>

    <div class="ticket-conversation mb-3">
        <div class="ticket-bubble ticket-bubble-customer">
            <div class="ticket-bubble-author">Khách hàng</div>
            <div class="ticket-bubble-content"><?php echo nl2br(htmlspecialchars($customerMessageDisplay)); ?></div>
        </div>
        <?php if (!empty($selectedContact->admin_reply)): ?>
            <div class="ticket-bubble ticket-bubble-admin">
                <div class="ticket-bubble-author">Quản trị viên phản hồi</div>
                <div class="ticket-bubble-content"><?php echo nl2br(htmlspecialchars($selectedContact->admin_reply)); ?></div>
                <?php if (!empty($selectedContact->replied_at)): ?>
                    <small class="d-block mt-2">
                        <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($selectedContact->replied_at))); ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isLockedTicket): ?>
        <div class="alert alert-secondary py-2 mb-3 ticket-locked-alert">
            Ticket đã phản hồi được khóa chỉnh sửa. Bạn chỉ có thể xóa ticket này.
        </div>
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Trạng thái</label>
                <div>
                    <span class="pill-badge <?php echo $statusBadgeClasses[$selectedContact->status] ?? 'pill-status-read'; ?>">
                        <?php echo htmlspecialchars($statusLabels[$selectedContact->status] ?? ucfirst($selectedContact->status)); ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ưu tiên</label>
                <div>
                    <span class="pill-badge <?php echo $priorityBadgeClasses[$selectedContact->priority] ?? 'pill-priority-normal'; ?>">
                        <?php echo htmlspecialchars($priorityLabels[$selectedContact->priority] ?? ucfirst($selectedContact->priority)); ?>
                    </span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Trạng thái</label>
                <div>
                    <span class="pill-badge <?php echo $statusBadgeClasses[$selectedContact->status] ?? 'pill-status-read'; ?>">
                        <?php echo htmlspecialchars($statusLabels[$selectedContact->status] ?? ucfirst($selectedContact->status)); ?>
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <form
                    action="<?php echo URLROOT; ?>/admincontacts/updatePriority/<?php echo (int) $selectedContact->user_id; ?>/<?php echo (int) $selectedContact->contact_id; ?>"
                    method="POST"
                    class="ticket-auto-save-form"
                    data-admin-autosave="true"
                    data-ticket-priority-list-sync="1"
                    data-ticket-user-id="<?php echo (int) $selectedContact->user_id; ?>"
                    data-ticket-contact-id="<?php echo (int) $selectedContact->contact_id; ?>"
                    data-toast-success="Ưu tiên đã cập nhật (tự động)."
                >
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="redirect_query" value="<?php echo htmlspecialchars($queryString, ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="form-label">Ưu tiên</label>
                    <select name="priority" class="form-select" data-admin-autosave-input="true" data-admin-custom-select="true" required>
                        <?php foreach ($priorities as $priority): ?>
                            <option value="<?php echo htmlspecialchars($priority); ?>" <?php echo $selectedContact->priority === $priority ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($priorityLabels[$priority] ?? ucfirst($priority)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <form action="<?php echo URLROOT; ?>/admincontacts/reply/<?php echo (int) $selectedContact->user_id; ?>/<?php echo (int) $selectedContact->contact_id; ?>" method="POST" class="mb-1 ticket-reply-form" data-ticket-reply-form="true">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="redirect_query" value="<?php echo htmlspecialchars($queryString, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="reply_message" class="form-label">Phản hồi quản trị viên</label>
            <textarea
                id="reply_message"
                name="reply_message"
                rows="4"
                class="form-control"
                placeholder="Nhập nội dung phản hồi..."
                required
                minlength="1"
            ><?php echo htmlspecialchars($selectedContact->admin_reply ?? ''); ?></textarea>
            <button type="submit" class="btn btn-primary mt-2" data-ticket-reply-submit="true">
                Gửi phản hồi
            </button>
        </form>
    <?php endif; ?>
<?php endif; ?>