<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header mb-3">
                    <h2 class="panel-title mb-0">Quản lý đơn hàng</h2>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th class="text-end">Tổng tiền</th>
                                <th>Ngày đặt</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['orders'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['orders'] as $order): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#<?php echo (int) $order->id; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars((string) $order->username, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars((string) $order->email, ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td class="text-end fw-bold text-danger"><?php echo number_format((float) $order->total_amount, 0, ',', '.'); ?>đ</td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php
                                            $bgColor = '#6c757d';
                                            $textColor = '#ffffff';
                                            if ($order->status === 'completed') {
                                                $bgColor = '#28a745';
                                            } elseif ($order->status === 'pending') {
                                                $bgColor = '#ffc107';
                                                $textColor = '#000000';
                                            } elseif ($order->status === 'processing') {
                                                $bgColor = '#17a2b8';
                                            } elseif ($order->status === 'cancelled') {
                                                $bgColor = '#dc3545';
                                            }
                                            ?>
                                            <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8'); ?>;color: <?php echo htmlspecialchars($textColor, ENT_QUOTES, 'UTF-8'); ?>;"><?php echo htmlspecialchars(strtoupper((string) $order->status), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex flex-wrap gap-2 align-items-center justify-content-end">
                                                <form action="<?php echo URLROOT; ?>/admin/orders/updateStatus/<?php echo (int) $order->id; ?>" method="POST" class="m-0">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <select name="status" class="form-select form-select-sm" style="min-width: 9rem;" data-admin-custom-select="true" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order->status === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                        <option value="processing" <?php echo $order->status === 'processing' ? 'selected' : ''; ?>>Đang thiết lập</option>
                                                        <option value="completed" <?php echo $order->status === 'completed' ? 'selected' : ''; ?>>Hoàn tất</option>
                                                        <option value="cancelled" <?php echo $order->status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                    </select>
                                                </form>
                                                <a href="<?php echo URLROOT; ?>/admin/orders/show/<?php echo (int) $order->id; ?>" class="btn btn-soft btn-sm btn-icon" title="Xem chi tiết" aria-label="Xem chi tiết đơn"><i class="ti-eye"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($data['totalPages']) && (int) $data['totalPages'] > 1): ?>
                    <nav class="mt-3" aria-label="Phân trang đơn hàng">
                        <ul class="pagination pagination-sm mb-0 flex-wrap">
                            <?php for ($i = 1; $i <= (int) $data['totalPages']; $i++): ?>
                                <li class="page-item <?php echo (int) $data['currentPage'] === $i ? 'active' : ''; ?>">
                                    <a class="page-link bg-transparent border-secondary text-light" href="<?php echo URLROOT; ?>/admin/orders?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
