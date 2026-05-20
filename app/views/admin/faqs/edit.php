<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php $isNew = empty($data['faq']); ?>

<div class="row g-3">
    <div class="col-12 col-lg-10">
        <section class="card panel-card">
            <div class="card-body">
                <div class="panel-header flex-wrap gap-2 mb-3">
                    <h2 class="panel-title mb-0"><?php echo $isNew ? 'Thêm FAQ' : 'Sửa FAQ'; ?></h2>
                    <a href="<?php echo URLROOT; ?>/admin/faqs" class="btn btn-outline-light btn-sm">Huỷ</a>
                </div>

                <form action="<?php echo $isNew ? URLROOT . '/admin/faqs/create' : URLROOT . '/admin/faqs/update/' . (int) $data['faq']->id; ?>" method="post">
                    <?php if (!$isNew): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $data['faq']->id; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="faq_question" class="form-label">Câu hỏi</label>
                        <input id="faq_question" type="text" name="question" class="form-control" value="<?php echo htmlspecialchars((string) ($data['faq']->question ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="faq_category" class="form-label">Danh mục (tuỳ chọn)</label>
                        <?php if (!empty($data['categories'])): ?>
                            <select id="faq_category" name="category" class="form-select" data-admin-custom-select="true">
                                <option value="">(Không chọn)</option>
                                <?php foreach ($data['categories'] as $c): ?>
                                    <option value="<?php echo htmlspecialchars((string) $c->slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (!empty($data['faq']->category) && $data['faq']->category === $c->slug) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $c->title, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars((string) ($data['faq']->category ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Slug hoặc tên category">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="faq_answer" class="form-label">Trả lời</label>
                        <textarea id="faq_answer" name="answer" rows="8" class="form-control"><?php echo htmlspecialchars((string) ($data['faq']->answer ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" value="active" id="faq_status_active" <?php echo (!empty($data['faq']) && $data['faq']->status === 'active') || $isNew ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="faq_status_active">Hiển thị (active)</label>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
