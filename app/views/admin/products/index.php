<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2">
                    <h2 class="panel-title mb-0">Danh sách sản phẩm</h2>
                    <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                        <form action="<?php echo URLROOT; ?>/admin/products" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="input-group input-group-sm" style="min-width: 200px; max-width: 280px;">
                                <input type="text" class="form-control" name="search" placeholder="Tìm tên sản phẩm..." value="<?php echo htmlspecialchars((string) ($data['keyword'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <button class="btn btn-primary" type="submit" aria-label="Tìm kiếm"><i class="ti-search"></i></button>
                            </div>
                        </form>
                        <a href="<?php echo URLROOT; ?>/admin/products/add" class="btn btn-success btn-sm"><i class="ti-plus"></i> Thêm mới</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Hình ảnh</th>
                                <th scope="col">Tên / Phân loại</th>
                                <th scope="col">Cấu hình</th>
                                <th scope="col" class="text-end">Giá (VNĐ)</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['products'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Không có sản phẩm nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['products'] as $product): ?>
                                    <tr>
                                        <th scope="row">#<?php echo (int) $product->id; ?></th>
                                        <td>
                                            <img src="<?php echo htmlspecialchars(URLROOT . '/' . $product->image_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars((string) $product->name, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars((string) $product->category_name, ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="ti-server"></i> CPU: <?php echo (int) $product->cpu_cores; ?> Core<br>
                                                <i class="ti-harddrives"></i> RAM: <?php echo (int) $product->ram_mb / 1024; ?> GB<br>
                                                <i class="ti-save"></i> SSD: <?php echo (int) $product->disk_gb; ?> GB
                                            </small>
                                        </td>
                                        <td class="text-end fw-bold text-danger"><?php echo number_format((float) $product->price, 0, ',', '.'); ?>đ</td>
                                        <td>
                                            <?php if ($product->status === 'active'): ?>
                                                <span class="pill-badge pill-status-replied">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="pill-badge pill-priority-normal">Đã ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 align-items-center">
                                                <a href="<?php echo URLROOT; ?>/admin/products/edit/<?php echo (int) $product->id; ?>" class="btn btn-soft btn-sm btn-icon" title="Sửa" aria-label="Sửa sản phẩm"><i class="fa fa-edit"></i></a>
                                                <form action="<?php echo URLROOT; ?>/admin/products/delete/<?php echo (int) $product->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xoá sản phẩm này?');">
                                                    <button type="submit" class="btn btn-soft btn-sm btn-icon text-danger" title="Xóa" aria-label="Xóa sản phẩm"><i class="ti-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($data['totalPages']) && (int) $data['totalPages'] > 1): ?>
                    <nav class="mt-3" aria-label="Phân trang sản phẩm">
                        <ul class="pagination pagination-sm mb-0 flex-wrap">
                            <?php for ($i = 1; $i <= (int) $data['totalPages']; $i++): ?>
                                <li class="page-item <?php echo (int) $data['currentPage'] === $i ? 'active' : ''; ?>">
                                    <a class="page-link bg-transparent border-secondary text-light" href="<?php echo URLROOT; ?>/admin/products?page=<?php echo $i; ?><?php echo !empty($data['keyword']) ? '&search=' . rawurlencode((string) $data['keyword']) : ''; ?>"><?php echo $i; ?></a>
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
