<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                    <form id="about-form" action="<?php echo URLROOT; ?>/admin/about" method="post" enctype="multipart/form-data">
                        <div class="row gx-4">
                            <div class="col-md-7">
                                <div class="panel-header mb-3">
                                    <h2 class="panel-title mb-0">Quản lý Giới thiệu</h2>
                                </div>
                                <?php // session handled globally
                                    if(!empty($_SESSION['flash_error'])): ?>
                                <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
                                <?php unset($_SESSION['flash_error']); endif; ?>
                                <?php if(!empty($_SESSION['flash_success'])): ?>
                                <div class="alert alert-success mb-3"><?php echo htmlspecialchars($_SESSION['flash_success']); ?></div>
                                <?php unset($_SESSION['flash_success']); endif; ?>
                                <div id="about-alert"></div>
                                
                                <input type="hidden" name="id" value="<?php echo isset($data['about']->id) ? $data['about']->id : ''; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
                                
                                <div class="mb-3">
                                    <label for="about-title" class="form-label">Tiêu đề</label>
                                    <input type="text" id="about-title" name="title" class="form-control" value="<?php echo isset($data['about']->title) ? htmlspecialchars($data['about']->title) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Subtitle (dòng nhỏ dưới tiêu đề)</label>
                                    <input type="text" name="subtitle" class="form-control" value="<?php echo isset($data['about']->subtitle) ? htmlspecialchars($data['about']->subtitle) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Intro duration (giây) — thời lượng intro GIF</label>
                                    <input type="number" step="0.1" min="0" name="intro_duration" class="form-control" value="<?php echo isset($data['about']->intro_duration) ? htmlspecialchars($data['about']->intro_duration) : '5.00'; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Partners Heading (Tiêu đề cho phần Đối tác)</label>
                                    <input type="text" name="partners_heading" class="form-control" value="<?php echo isset($data['about']->partners_heading) ? htmlspecialchars($data['about']->partners_heading) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Modpacks Heading (Tiêu đề cho phần Modpacks)</label>
                                    <input type="text" name="modpacks_heading" class="form-control" value="<?php echo isset($data['about']->modpacks_heading) ? htmlspecialchars($data['about']->modpacks_heading) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Gallery Heading (Tiêu đề cho phần Hình ảnh)</label>
                                    <input type="text" name="gallery_heading" class="form-control" value="<?php echo isset($data['about']->gallery_heading) ? htmlspecialchars($data['about']->gallery_heading) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nội dung (HTML)</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="use-legacy-content">
                                        <label class="form-check-label" for="use-legacy-content">Sử dụng Nội dung cũ thay vì Sections (nếu có)</label>
                                    </div>
                                    <textarea name="content" rows="8" class="form-control" id="legacy-content"><?php echo isset($data['about']->content) ? htmlspecialchars($data['about']->content) : ''; ?></textarea>
                                </div>
                                
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Uptime</label>
                                        <div class="input-group">
                                            <input type="text" name="uptime" class="form-control" value="<?php echo isset($data['about']->uptime) ? htmlspecialchars($data['about']->uptime) : ''; ?>">
                                            <input type="text" name="uptime_icon" class="form-control" placeholder="Icon class (fa-...)" value="<?php echo isset($data['about']->uptime_icon) ? htmlspecialchars($data['about']->uptime_icon) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Support</label>
                                        <div class="input-group">
                                            <input type="text" name="support" class="form-control" value="<?php echo isset($data['about']->support) ? htmlspecialchars($data['about']->support) : ''; ?>">
                                            <input type="text" name="support_icon" class="form-control" placeholder="Icon class (fa-...)" value="<?php echo isset($data['about']->support_icon) ? htmlspecialchars($data['about']->support_icon) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Performance</label>
                                    <div class="input-group">
                                        <input type="text" name="performance" class="form-control" value="<?php echo isset($data['about']->performance) ? htmlspecialchars($data['about']->performance) : ''; ?>">
                                        <input type="text" name="performance_icon" class="form-control" placeholder="Icon class (fa-...)" value="<?php echo isset($data['about']->performance_icon) ? htmlspecialchars($data['about']->performance_icon) : ''; ?>">
                                    </div>
                                </div>

                                <hr>
                                <h5>Sections (nhiều khung nội dung)</h5>
                                <div id="sections-list">
                                    <?php $sections = isset($data['about']->sections) && $data['about']->sections ? json_decode($data['about']->sections, true) : []; ?>
                                    <?php if(!empty($sections)): foreach($sections as $sec): ?>
                                        <div class="section-item mb-3">
                                            <div class="mb-2"><input type="text" name="section_title[]" class="form-control" placeholder="Tiêu đề section" value="<?php echo htmlspecialchars($sec['title'] ?? ''); ?>"></div>
                                            <div class="mb-2"><textarea name="section_content[]" rows="4" class="form-control" placeholder="Nội dung HTML (được phép HTML)"><?php echo htmlspecialchars($sec['content'] ?? ''); ?></textarea></div>
                                            <div><button type="button" class="btn btn-danger remove-section">Xóa</button></div>
                                            <hr>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div class="section-item mb-3">
                                            <div class="mb-2"><input type="text" name="section_title[]" class="form-control" placeholder="Tiêu đề section"></div>
                                            <div class="mb-2"><textarea name="section_content[]" rows="4" class="form-control" placeholder="Nội dung HTML (được phép HTML)"></textarea></div>
                                            <div><button type="button" class="btn btn-danger remove-section">Xóa</button></div>
                                            <hr>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3"><button type="button" id="add-section" class="btn btn-secondary">Thêm section</button></div>
                                
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Năm hoạt động (ví dụ: "5 năm")</label>
                                        <input type="text" name="years_active" class="form-control" value="<?php echo isset($data['about']->years_active) ? htmlspecialchars($data['about']->years_active) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Năm thành lập</label>
                                        <input type="number" name="founded_year" class="form-control" value="<?php echo isset($data['about']->founded_year) ? (int)$data['about']->founded_year : ''; ?>">
                                    </div>
                                </div>

                                <hr>
                                <h5>Partners (đối tác)</h5>
                                <div id="partners-list">
                                    <?php $partners = isset($data['about']->partners) && $data['about']->partners ? json_decode($data['about']->partners, true) : []; ?>
                                    <?php if(!empty($partners)): foreach($partners as $p): ?>
                                        <div class="partner-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="partners_name[]" class="form-control" placeholder="Tên công ty" value="<?php echo htmlspecialchars($p['name'] ?? ''); ?>"></div>
                                            <div class="col-4"><input type="text" name="partners_url[]" class="form-control" placeholder="URL" value="<?php echo htmlspecialchars($p['url'] ?? ''); ?>"></div>
                                            <div class="col-3"><input type="file" name="partners_logo[]" class="form-control"></div>
                                            <input type="hidden" name="partners_logo_text[]" value="<?php echo htmlspecialchars($p['logo'] ?? ''); ?>">
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div class="partner-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="partners_name[]" class="form-control" placeholder="Tên công ty"></div>
                                            <div class="col-4"><input type="text" name="partners_url[]" class="form-control" placeholder="URL"></div>
                                            <div class="col-3"><input type="file" name="partners_logo[]" class="form-control"></div>
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3"><button type="button" id="add-partner" class="btn btn-secondary">Thêm đối tác</button></div>

                                <hr>
                                <h5>Modpacks (gói mod hỗ trợ)</h5>
                                <div id="modpacks-list">
                                    <?php $modpacks = isset($data['about']->modpacks) && $data['about']->modpacks ? json_decode($data['about']->modpacks, true) : []; ?>
                                    <?php if(!empty($modpacks)): foreach($modpacks as $m): ?>
                                        <div class="modpack-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="modpacks_name[]" class="form-control" placeholder="Tên modpack" value="<?php echo htmlspecialchars($m['name'] ?? ''); ?>"></div>
                                            <div class="col-4"><input type="text" name="modpacks_url[]" class="form-control" placeholder="URL" value="<?php echo htmlspecialchars($m['url'] ?? ''); ?>"></div>
                                            <div class="col-3"><input type="file" name="modpacks_logo[]" class="form-control"></div>
                                            <input type="hidden" name="modpacks_logo_text[]" value="<?php echo htmlspecialchars($m['logo'] ?? ''); ?>">
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div class="modpack-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="modpacks_name[]" class="form-control" placeholder="Tên modpack"></div>
                                            <div class="col-4"><input type="text" name="modpacks_url[]" class="form-control" placeholder="URL"></div>
                                            <div class="col-3"><input type="file" name="modpacks_logo[]" class="form-control"></div>
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3"><button type="button" id="add-modpack" class="btn btn-secondary">Thêm modpack</button></div>

                                <hr>
                                <h5>Gallery ảnh</h5>
                                <div id="gallery-list">
                                    <?php $gallery = isset($data['about']->gallery) && $data['about']->gallery ? json_decode($data['about']->gallery, true) : []; ?>
                                    <?php if(!empty($gallery)): foreach($gallery as $g): ?>
                                        <div class="gallery-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="gallery_title[]" class="form-control" placeholder="Tiêu đề (title)" value="<?php echo htmlspecialchars($g['title'] ?? ''); ?>"></div>
                                            <div class="col-4"><input type="text" name="gallery_caption[]" class="form-control" placeholder="Caption" value="<?php echo htmlspecialchars($g['caption'] ?? ''); ?>"></div>
                                            <div class="col-3"><input type="file" name="gallery_images[]" class="form-control"></div>
                                            <input type="hidden" name="gallery_image_text[]" value="<?php echo htmlspecialchars($g['image'] ?? ''); ?>">
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div class="gallery-item mb-2 row gx-2 align-items-center">
                                            <div class="col-4"><input type="text" name="gallery_title[]" class="form-control" placeholder="Tiêu đề (title)"></div>
                                            <div class="col-4"><input type="text" name="gallery_caption[]" class="form-control" placeholder="Caption"></div>
                                            <div class="col-3"><input type="file" name="gallery_images[]" class="form-control"></div>
                                            <div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3"><button type="button" id="add-gallery" class="btn btn-secondary">Thêm ảnh</button></div>

                                <hr>
                                <h5>CTA / Call To Action</h5>
                                <div class="mb-3">
                                    <label class="form-label">CTA Heading</label>
                                    <input type="text" name="cta_heading" class="form-control" value="<?php echo isset($data['about']->cta_heading) ? htmlspecialchars($data['about']->cta_heading) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">CTA Text</label>
                                    <textarea name="cta_text" rows="3" class="form-control"><?php echo isset($data['about']->cta_text) ? htmlspecialchars($data['about']->cta_text) : ''; ?></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CTA Button Text</label>
                                        <input type="text" name="cta_button_text" class="form-control" value="<?php echo isset($data['about']->cta_button_text) ? htmlspecialchars($data['about']->cta_button_text) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CTA Button URL</label>
                                        <input type="text" name="cta_button_url" class="form-control" value="<?php echo isset($data['about']->cta_button_url) ? htmlspecialchars($data['about']->cta_button_url) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h5 class="mb-3 panel-muted">Preview ảnh chính (chọn từ Gallery)</h5>
                                <div class="card panel-card admin-nested-card">
                                    <div class="card-body text-center">
                                        <?php $gallery = isset($data['about']->gallery) && $data['about']->gallery ? json_decode($data['about']->gallery, true) : []; ?>
                                        <?php $initialMain = isset($data['about']->image) && $data['about']->image ? $data['about']->image : (isset($gallery[0]['image']) ? $gallery[0]['image'] : null); ?>
                                        <?php if(!empty($gallery) || $initialMain): ?>
                                            <img id="admin-main-image" src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($initialMain ?? ''); ?>" alt="Main Preview" class="admin-main-img mb-3">
                                            <div id="admin-thumbs" class="d-flex justify-content-center flex-wrap">
                                                <?php if(!empty($gallery)): foreach($gallery as $g): if(!empty($g['image'])): ?>
                                                    <img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($g['image']); ?>" data-fn="<?php echo htmlspecialchars($g['image']); ?>" class="admin-thumb rounded mb-2" alt="thumb">
                                                <?php endif; endforeach; endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted mb-3">Chưa có ảnh gallery</div>
                                        <?php endif; ?>
                                        <div class="mt-3">
                                            <label class="form-label">Hoặc upload ảnh mới (sẽ không tự động thêm vào Gallery)</label>
                                            <input type="file" name="image" accept="image/*" class="form-control">
                                        </div>
                                        <input type="hidden" name="image_text" id="image_text" value="<?php echo isset($data['about']->image) ? htmlspecialchars($data['about']->image) : (isset($gallery[0]['image']) ? htmlspecialchars($gallery[0]['image']) : ''); ?>">

                                        <hr>
                                        <h6>Background (Layer nền)</h6>
                                        <?php $bg = isset($data['about']->background) ? $data['about']->background : ''; ?>
                                        <?php if($bg): ?>
                                            <img id="admin-background-image" src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($bg); ?>" alt="Background Preview" class="admin-bg-img mb-2">
                                        <?php else: ?>
                                            <div class="text-muted mb-2">Chưa có background</div>
                                        <?php endif; ?>
                                        <div class="mb-2"><input type="file" name="background" accept="image/*" class="form-control"></div>
                                        <input type="hidden" name="background_text" id="background_text" value="<?php echo htmlspecialchars($bg); ?>">

                                        <hr>
                                        <h6>Intro GIF (hiển thị khi truy cập lần đầu)</h6>
                                        <?php $intro = isset($data['about']->intro_gif) ? $data['about']->intro_gif : ''; ?>
                                        <?php if($intro): ?>
                                            <img id="admin-intro-gif" src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($intro); ?>" alt="Intro Preview" class="admin-intro-img mb-2">
                                        <?php else: ?>
                                            <div class="text-muted mb-2">Chưa có intro GIF</div>
                                        <?php endif; ?>
                                        <div class="mb-2"><input type="file" name="intro_gif" accept="image/gif,image/webp,image/*" class="form-control"></div>
                                        <input type="hidden" name="intro_gif_text" id="intro_gif_text" value="<?php echo htmlspecialchars($intro); ?>">
                                        <div class="mb-2">
                                            <label class="form-label">Intro hiện tại</label>
                                            <div class="text-break text-muted small"><?php echo $intro ? htmlspecialchars($intro) : 'Chưa có file intro_gif'; ?></div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Intro duration (giây)</label>
                                            <input type="number" step="0.1" min="0" id="admin-intro-duration-visible" class="form-control" value="<?php echo isset($data['about']->intro_duration) ? htmlspecialchars($data['about']->intro_duration) : '5.00'; ?>">
                                            <div class="form-text">Sửa thời lượng intro ở đây (giá trị sẽ được sao chép vào trường thật khi lưu).</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </form>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>

<script>
// Submit normally (non-AJAX): disable submit button to prevent double-submit
document.getElementById('about-form').addEventListener('submit', function(e){
    var btn = this.querySelector('button[type="submit"]');
    if(btn){ btn.disabled = true; btn.innerText = 'Đang lưu...'; }
    // allow normal form submission to server (no e.preventDefault())
});

document.addEventListener('click', function(e){
    function createFromHTML(html){ var d=document.createElement('div'); d.innerHTML = html.trim(); return d.firstElementChild; }
    if(e.target && e.target.id === 'add-partner'){
        var tpl = document.querySelector('.partner-item');
        if(!tpl){
            tpl = createFromHTML('<div class="partner-item mb-2 row gx-2 align-items-center">' +
                '<div class="col-4"><input type="text" name="partners_name[]" class="form-control" placeholder="Tên công ty"></div>' +
                '<div class="col-4"><input type="text" name="partners_url[]" class="form-control" placeholder="URL"></div>' +
                '<div class="col-3"><input type="file" name="partners_logo[]" class="form-control"></div>' +
                '<div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>' +
                '</div>');
        } else {
            var clone = tpl.cloneNode(true);
            clone.querySelectorAll('input').forEach(function(i){ if(i.type !== 'hidden') i.value = ''; });
            tpl = clone;
        }
        document.getElementById('partners-list').appendChild(tpl);
    }
    if(e.target && e.target.id === 'add-modpack'){
        var tpl = document.querySelector('.modpack-item');
        if(!tpl){
            tpl = createFromHTML('<div class="modpack-item mb-2 row gx-2 align-items-center">' +
                '<div class="col-4"><input type="text" name="modpacks_name[]" class="form-control" placeholder="Tên modpack"></div>' +
                '<div class="col-4"><input type="text" name="modpacks_url[]" class="form-control" placeholder="URL"></div>' +
                '<div class="col-3"><input type="file" name="modpacks_logo[]" class="form-control"></div>' +
                '<div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>' +
                '</div>');
        } else {
            var clone = tpl.cloneNode(true);
            clone.querySelectorAll('input').forEach(function(i){ if(i.type !== 'hidden') i.value = ''; });
            tpl = clone;
        }
        document.getElementById('modpacks-list').appendChild(tpl);
    }
    if(e.target && e.target.id === 'add-gallery'){
        var tpl = document.querySelector('.gallery-item');
        if(!tpl){
            tpl = createFromHTML('<div class="gallery-item mb-2 row gx-2 align-items-center">' +
                '<div class="col-4"><input type="text" name="gallery_title[]" class="form-control" placeholder="Tiêu đề (title)"></div>' +
                '<div class="col-4"><input type="text" name="gallery_caption[]" class="form-control" placeholder="Caption"></div>' +
                '<div class="col-3"><input type="file" name="gallery_images[]" class="form-control"></div>' +
                '<div class="col-1"><button type="button" class="btn btn-danger remove-item">-</button></div>' +
                '</div>');
        } else {
            var clone = tpl.cloneNode(true);
            clone.querySelectorAll('input').forEach(function(i){ if(i.type !== 'hidden') i.value = ''; });
            tpl = clone;
        }
        document.getElementById('gallery-list').appendChild(tpl);
    }
    if(e.target && e.target.classList.contains('remove-item')){
        var row = e.target.closest('.partner-item, .modpack-item, .gallery-item');
        if(row) row.remove();
    }
    if(e.target && e.target.id === 'add-section'){
        var tpl = document.querySelector('.section-item');
        var clone = tpl.cloneNode(true);
        clone.querySelectorAll('input, textarea').forEach(function(i){ i.value = ''; });
        document.getElementById('sections-list').appendChild(clone);
    }
    if(e.target && e.target.classList && e.target.classList.contains('remove-section')){
        var s = e.target.closest('.section-item'); if(s) s.remove();
    }
});

// Hide legacy content if there are sections and checkbox not checked
(function(){
    var sectionsList = document.getElementById('sections-list');
    var legacy = document.getElementById('legacy-content');
    var checkbox = document.getElementById('use-legacy-content');
    function updateLegacyVisibility(){
        var hasSections = sectionsList && sectionsList.querySelectorAll('.section-item').length > 0;
        if(!checkbox) return;
        if(hasSections && !checkbox.checked){
            legacy.style.display = 'none';
        } else {
            legacy.style.display = '';
        }
    }
    document.addEventListener('click', function(e){ if(e.target && e.target.id === 'add-section'){ setTimeout(updateLegacyVisibility,50); } });
    document.addEventListener('click', function(e){ if(e.target && e.target.classList && e.target.classList.contains('remove-section')){ setTimeout(updateLegacyVisibility,50); } });
    if(checkbox){ checkbox.addEventListener('change', updateLegacyVisibility); }
    // initial state
    updateLegacyVisibility();
})();
</script>

<style>
.admin-main-img{width:100%;height:260px;object-fit:cover;border-radius:8px}
.admin-thumb{cursor:pointer;width:84px;height:64px;object-fit:cover;margin:4px;border:2px solid rgba(255,255,255,0.06)}
.admin-thumb:hover{border-color:rgba(255,255,255,0.18)}
.admin-bg-img,.admin-intro-img{width:100%;height:120px;object-fit:cover;border-radius:8px}
</style>

<script>
// gallery -> main preview interaction
document.addEventListener('click', function(e){
    if(e.target && e.target.classList && e.target.classList.contains('admin-thumb')){
        var fn = e.target.getAttribute('data-fn');
        var main = document.getElementById('admin-main-image');
        var hidden = document.getElementById('image_text');
        if(main && fn){ main.src = '<?php echo URLROOT; ?>/uploads/' + fn; }
        if(hidden && fn){ hidden.value = fn; }
    }
});

// live preview for background and intro GIF when selecting files
(function(){
    function bindPreview(fileInputSelector, imgSelector, hiddenSelector){
        var fi = document.querySelector(fileInputSelector);
        var img = document.querySelector(imgSelector);
        var hidden = document.querySelector(hiddenSelector);
        if(!fi) return;
        fi.addEventListener('change', function(){
            var f = this.files && this.files[0];
            if(!f){ return; }
            var reader = new FileReader();
            reader.onload = function(ev){
                if(img) img.src = ev.target.result;
                if(hidden) hidden.value = ''; // clear text reference when new file chosen
            };
            reader.readAsDataURL(f);
        });
    }
    bindPreview('input[name="background"]', '#admin-background-image', '#background_text');
    bindPreview('input[name="intro_gif"]', '#admin-intro-gif', '#intro_gif_text');
})();
</script>

<script>
// Sync visible duration input with real (left-column) input before submit
(function(){
    var real = document.querySelector('input[name="intro_duration"]');
    var vis = document.getElementById('admin-intro-duration-visible');
    var form = document.getElementById('about-form');
    if(!real || !vis || !form) return;
    // initialize
    try{ vis.value = real.value || vis.value; } catch(e){}
    vis.addEventListener('input', function(){ try{ real.value = this.value; }catch(e){} });
    form.addEventListener('submit', function(){ try{ real.value = vis.value; }catch(e){} });
})();
</script>