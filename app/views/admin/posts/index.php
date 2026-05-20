<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="admin-module-header">
                    <div>
                        <h1 class="admin-module-title">Quản lý tin tức</h1>
                        <p class="admin-module-lead">Thêm, sửa và xóa bài viết.</p>
                    </div>
                    <a href="<?php echo URLROOT; ?>/admin/posts/add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm bài viết
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Tác giả</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Ngày</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['news'])): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Chưa có bài viết.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['news'] as $article): ?>
                                    <tr>
                                        <td><?php echo (int) $article->id; ?></td>
                                        <td>
                                            <?php if (!empty($article->thumbnail)): ?>
                                                <img src="<?php echo htmlspecialchars(URLROOT . '/public/uploads/' . $article->thumbnail, ENT_QUOTES, 'UTF-8'); ?>" class="admin-post-thumb" alt="">
                                            <?php else: ?>
                                                <span class="badge rounded-pill badge-soft-primary small">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <strong><?php echo htmlspecialchars((string) $article->title, ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <div class="admin-post-slug"><?php echo htmlspecialchars((string) $article->slug, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) $article->author_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($article->status === 'published'): ?>
                                                <span class="pill-badge pill-status-replied">Đã đăng</span>
                                            <?php else: ?>
                                                <span class="pill-badge pill-priority-high">Bản nháp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo (int) $article->views_count; ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($article->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end admin-post-actions">
                                            <a href="<?php echo URLROOT; ?>/posts/show/<?php echo htmlspecialchars((string) $article->slug, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-soft btn-sm btn-icon" title="Xem"><i class="fas fa-eye"></i></a>
                                            <a href="<?php echo URLROOT; ?>/admin/posts/edit/<?php echo (int) $article->id; ?>" class="btn btn-soft btn-sm btn-icon" title="Sửa"><i class="fas fa-edit"></i></a>
                                            <form action="<?php echo URLROOT; ?>/admin/posts/delete/<?php echo (int) $article->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết này?');">
                                                <button type="submit" class="btn btn-soft btn-sm btn-icon text-danger" title="Xóa"><i class="fas fa-trash"></i></button>
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
