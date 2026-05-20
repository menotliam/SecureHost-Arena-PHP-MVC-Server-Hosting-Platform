<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header mb-3">
                    <h2 class="panel-title mb-0">Quản lý đánh giá</h2>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Sản phẩm</th>
                                <th>Điểm</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['reviews'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Chưa có đánh giá nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['reviews'] as $review): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#<?php echo (int) $review->id; ?></td>
                                        <td><strong><?php echo htmlspecialchars((string) ($review->full_name ?: $review->username), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td><?php echo htmlspecialchars((string) $review->product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa fa-star <?php echo $i <= (int) $review->rating ? 'text-warning' : 'review-star-empty'; ?>" aria-hidden="true"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td class="text-start text-truncate" style="max-width: 14rem;"><?php echo htmlspecialchars((string) $review->comment, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <form action="<?php echo URLROOT; ?>/admin/reviews/updateStatus/<?php echo (int) $review->id; ?>" method="POST" class="d-flex flex-wrap gap-1 align-items-center">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <select name="status" class="form-select form-select-sm" style="min-width: 7rem;" data-admin-custom-select="true" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $review->status === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                                    <option value="approved" <?php echo $review->status === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                                                    <option value="hidden" <?php echo $review->status === 'hidden' ? 'selected' : ''; ?>>Đã ẩn</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <form action="<?php echo URLROOT; ?>/admin/reviews/delete/<?php echo (int) $review->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="ti-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
