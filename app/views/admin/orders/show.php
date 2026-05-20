<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0">Chi tiết đơn hàng <span class="text-primary">#<?php echo (int) $data['order']->id; ?></span></h2>
                    <a href="<?php echo URLROOT; ?>/admin/orders" class="btn btn-outline-light btn-sm"><i class="ti-arrow-left"></i> Quay lại</a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="admin-detail-box">
                            <h5 class="admin-detail-box-title"><i class="ti-user"></i> Thông tin khách hàng</h5>
                            <p><strong>Tài khoản:</strong> <?php echo htmlspecialchars((string) $data['order']->username, ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars((string) $data['order']->email, ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars((string) ($data['order']->phone ?? 'Không có'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-detail-box">
                            <h5 class="admin-detail-box-title"><i class="ti-info-alt"></i> Thông tin đơn hàng</h5>
                            <p><strong>Ngày đặt:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($data['order']->created_at)), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Trạng thái:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars((string) $data['order']->status, ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p><strong>Ghi chú / địa chỉ:</strong> <?php echo htmlspecialchars((string) ($data['order']->address ?? 'Không có'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </div>

                <h3 class="h6 text-uppercase panel-muted mb-2"><i class="ti-shopping-cart"></i> Sản phẩm trong đơn</h3>
                <div class="table-responsive">
                    <table class="table admin-table text-center">
                        <thead>
                            <tr>
                                <th>Hình ảnh</th>
                                <th class="text-start">Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thời hạn</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['items'])): ?>
                                <?php foreach ($data['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item->image_url)): ?>
                                                <img src="<?php echo htmlspecialchars(URLROOT . '/' . $item->image_url, ENT_QUOTES, 'UTF-8'); ?>" width="50" class="img-thumbnail" alt="">
                                            <?php else: ?>
                                                <i class="ti-server text-muted" style="font-size: 1.5rem;" aria-hidden="true"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start fw-bold"><?php echo htmlspecialchars((string) ($item->name ?? ('Sản phẩm ID: ' . $item->product_id)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo number_format((float) $item->price, 0, ',', '.'); ?>đ</td>
                                        <td>x<?php echo (int) $item->quantity; ?></td>
                                        <td><?php echo (int) ($item->duration_months ?? 1); ?> tháng</td>
                                        <td class="text-end fw-bold text-danger"><?php echo number_format((float) $item->price * (int) $item->quantity * (int) ($item->duration_months ?? 1), 0, ',', '.'); ?>đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted">Không tải được chi tiết sản phẩm.</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="admin-table-total-row">
                                <td colspan="5" class="text-end fw-bold text-uppercase">Tổng thanh toán</td>
                                <td class="text-end fw-bold text-danger" style="font-size: 1.1rem;"><?php echo number_format((float) $data['order']->total_amount, 0, ',', '.'); ?>đ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
