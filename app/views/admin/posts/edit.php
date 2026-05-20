<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="admin-module-header">
                    <div>
                        <h1 class="admin-module-title">Chỉnh sửa bài viết</h1>
                        <p class="admin-module-lead"><?php echo htmlspecialchars((string) $data['article']->title, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo URLROOT; ?>/posts/show/<?php echo htmlspecialchars((string) $data['article']->slug, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info btn-sm text-white"><i class="fas fa-eye"></i> Xem bài</a>
                        <a href="<?php echo URLROOT; ?>/admin/posts" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    </div>
                </div>

                <form action="<?php echo URLROOT; ?>/admin/posts/edit/<?php echo (int) $data['article']->id; ?>" method="POST" enctype="multipart/form-data" id="post-form">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required value="<?php echo htmlspecialchars((string) $data['article']->title, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Danh mục</label>
                            <select name="category_id" id="category_id" class="form-select" data-admin-custom-select="true">
                                <option value="">-- Chọn danh mục --</option>
                                <?php if (!empty($data['categories'])): ?>
                                    <?php foreach ($data['categories'] as $cat): ?>
                                        <option value="<?php echo (int) $cat->id; ?>" <?php echo ((int) $data['article']->category_id === (int) $cat->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $cat->name, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select name="status" id="status" class="form-select" data-admin-custom-select="true">
                                <option value="published" <?php echo $data['article']->status === 'published' ? 'selected' : ''; ?>>Công khai</option>
                                <option value="draft" <?php echo $data['article']->status === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <div class="admin-rich-editor-wrap">
                                <textarea name="content" id="content" class="form-control rich-text-editor" rows="12"><?php echo htmlspecialchars((string) $data['article']->content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Thumbnail</label>
                            <?php if ($data['article']->thumbnail): ?>
                                <div class="admin-current-thumb-row" id="current-thumb-container">
                                    <img src="<?php echo htmlspecialchars(URLROOT . '/public/uploads/' . $data['article']->thumbnail, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                    <span><?php echo htmlspecialchars((string) $data['article']->thumbnail, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
                            <div id="thumbnail-dropzone" class="dropzone admin-dropzone-wrap"></div>
                            <input type="hidden" name="thumbnail" id="thumbnail-path" value="<?php echo htmlspecialchars((string) $data['article']->thumbnail, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top" style="border-color: var(--admin-border) !important;">
                        <h3 class="admin-form-section-title"><i class="fas fa-bolt"></i> Cấu hình đăng bài</h3>
                        <div class="row g-3">
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_breaking" id="is_breaking" value="1" <?php echo !empty($data['article']->is_breaking) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_breaking">Tin nóng (breaking)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="breaking_until" class="form-label">Tin nóng đến</label>
                                <input type="datetime-local" name="breaking_until" id="breaking_until" class="form-control" value="<?php echo $data['article']->breaking_until ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($data['article']->breaking_until)), ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="publish_at" class="form-label">Hẹn giờ đăng</label>
                                <input type="datetime-local" name="publish_at" id="publish_at" class="form-control" value="<?php echo $data['article']->publish_at ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($data['article']->publish_at)), ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top" style="border-color: var(--admin-border) !important;">
                        <h3 class="admin-form-section-title"><i class="fas fa-search"></i> SEO</h3>
                        <div class="admin-seo-preview-box" id="seo-preview">
                            <div class="seo-preview-title" id="seo-preview-title"><?php echo htmlspecialchars((string) $data['article']->title, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars(SITENAME, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="seo-preview-url"><?php echo htmlspecialchars(URLROOT . '/posts/show/' . $data['article']->slug, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="seo-preview-desc" id="seo-preview-desc"><?php echo htmlspecialchars((string) ($data['article']->meta_description ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Từ khóa SEO</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="<?php echo htmlspecialchars((string) ($data['article']->meta_keywords ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Mô tả SEO <span class="seo-counter small text-muted" id="desc-counter"><?php echo (int) mb_strlen((string) ($data['article']->meta_description ?? '')); ?>/160</span></label>
                            <textarea name="meta_description" id="meta_description" class="form-control" rows="3" maxlength="200"><?php echo htmlspecialchars((string) ($data['article']->meta_description ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top" style="border-color: var(--admin-border) !important;">
                        <h3 class="admin-form-section-title"><i class="fas fa-chart-bar"></i> Thống kê</h3>
                        <div class="admin-post-stat-grid">
                            <div class="admin-post-stat-tile">
                                <div class="admin-post-stat-icon"><i class="fas fa-eye"></i></div>
                                <div class="admin-post-stat-value"><?php echo number_format((int) ($data['article']->views_count ?? 0)); ?></div>
                                <div class="admin-post-stat-label">Lượt xem</div>
                            </div>
                            <div class="admin-post-stat-tile">
                                <div class="admin-post-stat-icon"><i class="fas fa-heart"></i></div>
                                <div class="admin-post-stat-value"><?php echo number_format((int) ($data['article']->likes_count ?? 0)); ?></div>
                                <div class="admin-post-stat-label">Lượt thích</div>
                            </div>
                            <div class="admin-post-stat-tile">
                                <div class="admin-post-stat-icon"><i class="fas fa-calendar"></i></div>
                                <div class="admin-post-stat-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($data['article']->created_at)), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="admin-post-stat-label">Ngày tạo</div>
                            </div>
                            <div class="admin-post-stat-tile">
                                <div class="admin-post-stat-icon"><i class="fas fa-chart-line"></i></div>
                                <div class="admin-post-stat-value"><?php echo (int) ($data['article']->seo_score ?? 0); ?>%</div>
                                <div class="admin-post-stat-label">Điểm SEO</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
        })
        .catch(function (error) {
            console.error('CKEditor error:', error);
        });

    Dropzone.autoDiscover = false;
    new Dropzone('#thumbnail-dropzone', {
        url: "<?php echo URLROOT; ?>/admin/posts/uploadImage",
        paramName: 'file',
        maxFilesize: 2,
        maxFiles: 1,
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        dictDefaultMessage: "<i class='fas fa-cloud-upload-alt' style='font-size:2rem;margin-bottom:10px;display:block;opacity:.6'></i> Kéo thả ảnh mới để thay đổi",
        success: function (file, response) {
            try {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res.success) {
                    document.getElementById('thumbnail-path').value = res.filename;
                    var cur = document.getElementById('current-thumb-container');
                    if (cur) {
                        cur.style.opacity = '0.35';
                    }
                }
            } catch (e) {
                console.error('Dropzone parse error:', e);
            }
        },
        error: function (file, response) {
            alert('Lỗi tải ảnh: ' + (typeof response === 'string' ? response : (response.message || 'Unknown error')));
        }
    });

    var titleInput = document.getElementById('title');
    var descInput = document.getElementById('meta_description');
    var descCounter = document.getElementById('desc-counter');
    if (titleInput) {
        titleInput.addEventListener('input', function () {
            document.getElementById('seo-preview-title').textContent = (this.value || 'Tiêu đề') + ' - <?php echo htmlspecialchars(SITENAME, ENT_QUOTES, 'UTF-8'); ?>';
        });
    }
    if (descInput && descCounter) {
        descInput.addEventListener('input', function () {
            document.getElementById('seo-preview-desc').textContent = this.value || '';
            descCounter.textContent = this.value.length + '/160';
            descCounter.style.color = this.value.length > 160 ? '#ef4444' : (this.value.length >= 120 ? '#22c55e' : '');
        });
    }
    var breakingCheckbox = document.getElementById('is_breaking');
    var breakingUntil = document.getElementById('breaking_until');
    if (breakingCheckbox && breakingUntil) {
        breakingUntil.disabled = !breakingCheckbox.checked;
        breakingCheckbox.addEventListener('change', function () {
            breakingUntil.disabled = !this.checked;
        });
    }
</script>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
