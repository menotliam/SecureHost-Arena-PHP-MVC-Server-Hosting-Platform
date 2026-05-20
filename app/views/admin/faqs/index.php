<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars((string) $_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars((string) $_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0">Thêm FAQ nhanh</h2>
                    <form action="<?php echo URLROOT; ?>/admin/faqs/create" method="post" class="row g-2 align-items-end flex-grow-1" style="min-width: 240px;">
                        <div class="col-md-5">
                            <label class="form-label small mb-1">Câu hỏi</label>
                            <input type="text" name="question" placeholder="Câu hỏi" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Danh mục</label>
                            <?php if (!empty($data['categories'])): ?>
                                <select name="category" class="form-select" data-admin-custom-select="true">
                                    <option value="">(Không chọn)</option>
                                    <?php foreach ($data['categories'] as $c): ?>
                                        <option value="<?php echo htmlspecialchars((string) $c->slug, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $c->title, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" name="category" placeholder="Category (tuỳ chọn)" class="form-control">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="answer" value="-">
                            <button type="submit" class="btn btn-primary w-100">Thêm nhanh</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0">Danh mục FAQ</h2>
                    <form action="<?php echo URLROOT; ?>/admin/faqs/createCategory" method="post" enctype="multipart/form-data" class="row g-2 align-items-end flex-grow-1">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Tiêu đề</label>
                            <input type="text" name="title" placeholder="Tiêu đề category" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small mb-1">Ảnh</label>
                            <input type="file" name="image" accept="image/*" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100">Thêm category</button>
                        </div>
                    </form>
                </div>
                <?php if (!empty($data['categories'])): ?>
                    <div class="row g-3">
                        <?php foreach ($data['categories'] as $cat): ?>
                            <div class="col-md-4">
                                <div class="card panel-card admin-nested-card h-100">
                                    <?php if (!empty($cat->image)): ?>
                                        <img src="<?php echo htmlspecialchars((string) $cat->image, ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars((string) $cat->title, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars((string) $cat->title, ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <p class="text-muted small mb-2">Slug: <?php echo htmlspecialchars((string) $cat->slug, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <form action="<?php echo URLROOT; ?>/admin/faqs/updateCategory/<?php echo (int) $cat->id; ?>" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-2">
                                            <input type="hidden" name="id" value="<?php echo (int) $cat->id; ?>">
                                            <input type="text" name="title" value="<?php echo htmlspecialchars((string) $cat->title, ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                                            <input type="file" name="image" accept="image/*" class="form-control">
                                            <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                                        </form>
                                        <div class="mt-2 text-end">
                                            <form action="<?php echo URLROOT; ?>/admin/faqs/deleteCategory/<?php echo (int) $cat->id; ?>" method="post" class="d-inline" onsubmit="return confirm('Xóa category? Các FAQ liên quan sẽ bỏ category.');">
                                                <input type="hidden" name="id" value="<?php echo (int) $cat->id; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-muted mb-0">Chưa có category FAQ.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0">Danh sách FAQ</h2>
                    <form id="admin-category-form" method="get" action="<?php echo URLROOT; ?>/admin/faqs" class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="admin_category_filter" class="form-label mb-0 small">Lọc danh mục</label>
                        <select id="admin_category_filter" name="category" class="form-select form-select-sm" style="min-width: 12rem;" data-admin-custom-select="true" onchange="document.getElementById('admin-category-form').submit()">
                            <option value="">-- Tất cả --</option>
                            <?php if (!empty($data['categories'])): ?>
                                <?php foreach ($data['categories'] as $c): ?>
                                    <option value="<?php echo htmlspecialchars((string) $c->slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($data['current_category']) && $data['current_category'] === $c->slug) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $c->title, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </form>
                </div>

                <?php if (!empty($data['faqs'])): ?>
                    <div class="list-group admin-list-group">
                        <?php foreach ($data['faqs'] as $faq): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                    <div class="me-auto">
                                        <div class="fw-bold">#<?php echo (int) $faq->id; ?> — <?php echo htmlspecialchars((string) $faq->question, ENT_QUOTES, 'UTF-8'); ?></div>
                                        <small class="text-muted">Trạng thái: <?php echo htmlspecialchars((string) $faq->status, ENT_QUOTES, 'UTF-8'); ?><?php echo isset($faq->category) && $faq->category ? ' · ' . htmlspecialchars((string) $faq->category, ENT_QUOTES, 'UTF-8') : ''; ?></small>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-toggle" data-id="<?php echo (int) $faq->id; ?>">Sửa</button>
                                        <form action="<?php echo URLROOT; ?>/admin/faqs/delete/<?php echo (int) $faq->id; ?>" method="post" class="d-inline" onsubmit="return confirm('Xác nhận xóa?');">
                                            <input type="hidden" name="id" value="<?php echo (int) $faq->id; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="faq-edit mt-3" id="faq-edit-<?php echo (int) $faq->id; ?>" style="display:none;">
                                    <form action="<?php echo URLROOT; ?>/admin/faqs/update/<?php echo (int) $faq->id; ?>" method="post" class="d-flex flex-column gap-2">
                                        <input type="hidden" name="id" value="<?php echo (int) $faq->id; ?>">
                                        <div class="row g-2">
                                            <div class="col-md-8">
                                                <input type="text" name="question" value="<?php echo htmlspecialchars((string) $faq->question, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Câu hỏi">
                                            </div>
                                            <div class="col-md-4">
                                                <?php if (!empty($data['categories'])): ?>
                                                    <select name="category" class="form-select" data-admin-custom-select="true">
                                                        <option value="">(Không chọn)</option>
                                                        <?php foreach ($data['categories'] as $c): ?>
                                                            <option value="<?php echo htmlspecialchars((string) $c->slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (isset($faq->category) && $faq->category === $c->slug) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $c->title, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php else: ?>
                                                    <input type="text" name="category" value="<?php echo htmlspecialchars((string) ($faq->category ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Category">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <textarea name="answer" rows="4" class="form-control" placeholder="Đáp án"><?php echo htmlspecialchars((string) $faq->answer, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                            <select name="status" class="form-select form-select-sm" style="max-width: 10rem;" data-admin-custom-select="true">
                                                <option value="active" <?php echo ($faq->status === 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($faq->status === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-muted mb-0">Chưa có FAQ.</p>
                <?php endif; ?>
            </div>
            <div class="card-footer text-end">
                <?php echo $data['pagination'] ?? ''; ?>
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <h2 class="panel-title mb-3">Tin nhắn người dùng <?php if (!empty($data['new_messages'])): ?><span class="badge bg-danger"><?php echo (int) $data['new_messages']; ?></span><?php endif; ?></h2>
                <?php if (!empty($data['messages'])): ?>
                    <div class="list-group admin-list-group">
                        <?php foreach ($data['messages'] as $m): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars((string) ($m->name ?: 'Khách'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <?php if (!empty($m->email)): ?><small class="text-muted"> &lt;<?php echo htmlspecialchars((string) $m->email, ENT_QUOTES, 'UTF-8'); ?>&gt;</small><?php endif; ?>
                                        <?php if (!empty($m->category)): ?><span class="badge bg-secondary ms-1"><?php echo htmlspecialchars((string) $m->category, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars((string) $m->created_at, ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                                <div class="mt-2"><?php echo nl2br(htmlspecialchars((string) $m->message, ENT_QUOTES, 'UTF-8')); ?></div>
                                <?php if (!empty($m->reply)): ?>
                                    <div class="mt-3 p-3 rounded admin-faq-reply-box small">
                                        <?php echo nl2br(htmlspecialchars((string) $m->reply, ENT_QUOTES, 'UTF-8')); ?>
                                        <br><span class="text-muted">Bởi: <?php echo htmlspecialchars((string) $m->reply_by, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php else: ?>
                                    <form action="<?php echo URLROOT; ?>/admin/faqs/replyMessage/<?php echo (int) $m->id; ?>" method="post" class="mt-3 d-flex flex-column flex-md-row gap-2">
                                        <input type="hidden" name="id" value="<?php echo (int) $m->id; ?>">
                                        <textarea name="reply" class="form-control" placeholder="Viết trả lời..." required></textarea>
                                        <button type="submit" class="btn btn-primary align-self-md-end">Gửi</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-muted mb-0">Chưa có tin nhắn.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var el = document.getElementById('faq-edit-' + id);
            if (!el) {
                return;
            }
            if (el.style.display === 'none' || el.style.display === '') {
                el.style.display = 'block';
                this.textContent = 'Đóng';
            } else {
                el.style.display = 'none';
                this.textContent = 'Sửa';
            }
        });
    });
});
</script>
