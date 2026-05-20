<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="admin-module-header">
                    <div>
                        <h1 class="admin-module-title">Thêm quảng cáo</h1>
                        <p class="admin-module-lead">Tạo banner mới cho website.</p>
                    </div>
                    <a href="<?php echo URLROOT; ?>/admin/ads" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </div>

                <form action="<?php echo URLROOT; ?>/admin/ads/add" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required placeholder="Ví dụ: Khuyến mãi mùa hè">
                        </div>
                        <div class="col-12">
                            <label for="link_url" class="form-label">Link URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="link_url" id="link_url" required placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="position" class="form-label">Vị trí</label>
                            <select class="form-select" name="position" id="position" data-admin-custom-select="true">
                                <option value="sticky-sidebar">Thanh bên (sticky)</option>
                                <option value="header-top">Trên header</option>
                                <option value="footer-bottom">Dưới footer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" name="status" id="status" data-admin-custom-select="true">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Tạm dừng</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="start_at" class="form-label">Ngày bắt đầu</label>
                            <input type="datetime-local" class="form-control" name="start_at" id="start_at">
                        </div>
                        <div class="col-md-6">
                            <label for="end_at" class="form-label">Ngày kết thúc</label>
                            <input type="datetime-local" class="form-control" name="end_at" id="end_at">
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Hình ảnh banner <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="image" id="image" required accept="image/*">
                            <p class="form-text panel-muted mb-0">Gợi ý: 300×600px cho sidebar.</p>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tạo quảng cáo</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
