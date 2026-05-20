<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0">Cập nhật: <span class="text-primary"><?php echo htmlspecialchars((string) $data['name'], ENT_QUOTES, 'UTF-8'); ?></span></h2>
                    <a href="<?php echo URLROOT; ?>/admin/products" class="btn btn-outline-light btn-sm"><i class="ti-arrow-left"></i> Quay lại</a>
                </div>

                <form action="<?php echo URLROOT; ?>/admin/products/edit/<?php echo (int) $data['id']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên package <span class="text-danger">*</span></label>
                                <input class="form-control <?php echo !empty($data['name_err']) ? 'is-invalid' : ''; ?>" type="text" name="name" id="name" value="<?php echo htmlspecialchars((string) $data['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars((string) $data['name_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="category_id" data-admin-custom-select="true">
                                    <?php foreach ($data['categories'] as $category): ?>
                                        <option value="<?php echo (int) $category->id; ?>" <?php echo (int) $data['category_id'] === (int) $category->id ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $category->name, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Mức giá / tháng (VNĐ) <span class="text-danger">*</span></label>
                                <input class="form-control <?php echo !empty($data['price_err']) ? 'is-invalid' : ''; ?>" type="number" name="price" id="price" value="<?php echo htmlspecialchars((string) $data['price'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars((string) $data['price_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái bán</label>
                                <select class="form-select" name="status" id="status" data-admin-custom-select="true">
                                    <option value="active" <?php echo $data['status'] === 'active' ? 'selected' : ''; ?>>Đang triển khai (Active)</option>
                                    <option value="hidden" <?php echo $data['status'] === 'hidden' ? 'selected' : ''; ?>>Ẩn (Hidden)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cpu_cores" class="form-label">Số core CPU <span class="text-danger">*</span></label>
                                <input class="form-control <?php echo !empty($data['cpu_err']) ? 'is-invalid' : ''; ?>" type="number" name="cpu_cores" id="cpu_cores" value="<?php echo htmlspecialchars((string) $data['cpu_cores'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars((string) $data['cpu_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="ram_mb" class="form-label">RAM (MB) <span class="text-danger">*</span></label>
                                <input class="form-control <?php echo !empty($data['ram_mb_err']) ? 'is-invalid' : ''; ?>" type="number" name="ram_mb" id="ram_mb" value="<?php echo htmlspecialchars((string) $data['ram_mb'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars((string) $data['ram_mb_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="disk_gb" class="form-label">SSD (GB) <span class="text-danger">*</span></label>
                                <input class="form-control <?php echo !empty($data['disk_err']) ? 'is-invalid' : ''; ?>" type="number" name="disk_gb" id="disk_gb" value="<?php echo htmlspecialchars((string) $data['disk_gb'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="invalid-feedback"><?php echo htmlspecialchars((string) $data['disk_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Cập nhật hình ảnh</label>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars(URLROOT . '/' . $data['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <input type="file" class="form-control <?php echo !empty($data['image_err']) ? 'is-invalid' : ''; ?>" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                                <p class="form-text panel-muted mb-0">Bỏ trống để giữ ảnh hiện tại.</p>
                                <div class="text-danger small"><?php echo htmlspecialchars((string) $data['image_err'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả chi tiết</label>
                        <textarea class="form-control" name="description" id="description" rows="4"><?php echo htmlspecialchars((string) $data['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="ti-save"></i> Cập nhật</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
