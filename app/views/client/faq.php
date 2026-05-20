<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<div class="bg-gray-950 py-24 min-h-[80vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-extrabold text-white mb-4">Câu hỏi thường gặp (FAQ)</h1>
            <p class="text-gray-400">Giải đáp các thắc mắc phổ biến nhất của người dùng.</p>
        </div>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-white/80">Tổng: <?php echo isset($data['faqs']) ? count($data['faqs']) : 0; ?></h2>
                </div>
                <?php if(!empty($data['categories'])): ?>
                <div class="w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 faq-category-grid">
                    <?php foreach($data['categories'] as $c): ?>
                        <?php
                            $imgUrl = '';
                            if(!empty($c->image)){
                                if(preg_match('#^https?://#i', $c->image)){
                                    $imgUrl = $c->image;
                                } elseif(strpos($c->image, '/uploads') === 0){
                                    $imgUrl = URLROOT . $c->image;
                                } else {
                                    $imgUrl = URLROOT . '/uploads/' . ltrim($c->image, '/');
                                }
                            }
                        ?>
                        <a href="<?php echo URLROOT; ?>/pages/faq?category=<?php echo urlencode($c->slug); ?>" class="cat-card block p-3 rounded bg-gray-800 hover:bg-gray-700 text-center">
                            <?php if(!empty($imgUrl)): ?><img class="cat-img" src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($c->title); ?>" /><?php endif; ?>
                            <div class="text-sm text-gray-200"><?php echo htmlspecialchars($c->title); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if(isset($data['current_category']) && $data['current_category']): ?>
                <div class="mb-4">
                    <form method="get" action="<?php echo URLROOT; ?>/pages/faq" class="flex gap-2">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($data['current_category']); ?>">
                        <input name="q" placeholder="Tìm kiếm trong danh mục này..." value="<?php echo htmlspecialchars($data['q'] ?? ''); ?>" class="w-full p-3 rounded bg-gray-800 text-gray-200" />
                        <button type="submit" class="btn-send">Tìm</button>
                    </form>
                </div>
                <?php if(!empty($data['faqs'])): ?>
                    <div class="divide-y divide-gray-800 rounded-3xl overflow-hidden shadow-xl bg-gradient-to-tr from-gray-900/60 to-gray-800/30">
                        <?php foreach($data['faqs'] as $i => $faq): ?>
                            <div class="faq-item" data-aos="fade-up" data-aos-delay="<?php echo ($i%6)*40; ?>">
                                <button class="w-full text-left p-6 flex justify-between items-start gap-4 faq-q">
                                    <div>
                                        <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($faq->question); ?></h3>
                                    </div>
                                    <div class="text-cyan-400 faq-toggle-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                </button>
                                <div class="faq-a px-6 pb-6 hidden text-gray-300" style="line-height:1.8;">
                                    <?php echo $faq->answer; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if(!empty($data['pagination'])): ?>
                        <div class="mt-6 px-4"><div class="faq-pagination-wrapper"><?php echo $data['pagination']; ?></div></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-2">Chưa có câu hỏi</h3>
                        <p class="text-gray-400">Hiện chưa có FAQ nào trong danh mục này.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.faq-q').forEach(function(btn){
        btn.addEventListener('click', function(){
            var wrapper = btn.closest('.faq-item');
            var ans = wrapper.querySelector('.faq-a');
            var icon = wrapper.querySelector('.faq-toggle-icon');
            var open = !ans.classList.contains('hidden');
            if(open){
                // close
                ans.style.maxHeight = null;
                ans.classList.add('hidden');
                icon.style.transform = '';
            } else {
                // open
                ans.classList.remove('hidden');
                ans.style.maxHeight = ans.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
});
</script>
<script>
// category filter handler
document.getElementById('faq-category-filter') && document.getElementById('faq-category-filter').addEventListener('change', function(){
    var v = this.value;
    var url = '<?php echo URLROOT; ?>/pages/faq';
    if(v) url += '?category=' + encodeURIComponent(v);
    window.location.href = url;
});
// Single floating chat widget
function openFaqChat(){
    document.getElementById('faq-chat-modal').classList.remove('hidden');
}
function closeFaqChat(){
    document.getElementById('faq-chat-modal').classList.add('hidden');
}

// submit via AJAX (attach after DOM ready)
document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('faq-chat-form');
    if(!form) return;
    form.addEventListener('submit', function(e){
        e.preventDefault();
        var f = this;
        var data = new FormData(f);
        fetch(f.getAttribute('action') || '<?php echo URLROOT; ?>/pages/faqMessage', {method:'POST', body: data, headers: {'X-Requested-With':'XMLHttpRequest'}})
            .then(r => r.json()).then(function(j){
                if(j && j.success){
                    alert('Tin nhắn đã gửi. Cảm ơn!');
                    f.reset(); closeFaqChat();
                } else {
                    alert('Gửi thất bại: ' + (j.error||'Lỗi'));
                }
            }).catch(function(){ alert('Lỗi mạng'); });
    });
    // View history button
    var btnHistory = document.getElementById('faq-view-history');
    var convBox = document.getElementById('faq-conversation');
    if(btnHistory){
        btnHistory.addEventListener('click', function(){
            var email = form.querySelector('input[name="email"]').value.trim();
            var page_url = form.querySelector('input[name="page_url"]').value.trim();
            if(!email && !page_url){
                alert('Vui lòng nhập Email hoặc thử lại khi đang ở trang cần kiểm tra.');
                return;
            }
            var url = '<?php echo URLROOT; ?>/pages/faqMessages?';
            if(email) url += 'email=' + encodeURIComponent(email);
            else url += 'page_url=' + encodeURIComponent(page_url);
            fetch(url, {method:'GET', headers:{'X-Requested-With':'XMLHttpRequest'}})
                .then(r => r.json()).then(function(j){
                    if(!j || !j.success){ alert('Không lấy được lịch sử'); return; }
                    var msgs = j.messages || [];
                    if(msgs.length === 0){ convBox.innerHTML = '<div class="text-gray-400">Chưa có lịch sử.</div>'; convBox.classList.remove('hidden'); return; }
                    var html = '';
                    msgs.forEach(function(m){
                        html += '<div style="margin-bottom:8px">';
                        html += '<div style="font-size:13px;color:#9ca3af">' + (m.name ? escapeHtml(m.name) : 'Khách') + (m.email ? ' &lt;'+escapeHtml(m.email)+'&gt;' : '') + ' · ' + escapeHtml(m.created_at) + '</div>';
                        html += '<div style="padding:8px;background:#071025;border-radius:6px;margin-top:4px">' + nl2br(escapeHtml(m.message)) + '</div>';
                        if(m.reply){ html += '<div style="margin-top:6px;padding:8px;background:#072018;border-radius:6px;color:#d1fae5">Trả lời: ' + nl2br(escapeHtml(m.reply)) + '<br><small style="color:#9ca3af">Bởi: ' + escapeHtml(m.reply_by || '') + ' · ' + escapeHtml(m.replied_at || '') + '</small></div>'; }
                        html += '</div>';
                    });
                    convBox.innerHTML = html;
                    convBox.classList.remove('hidden');
                    convBox.scrollTop = convBox.scrollHeight;
                }).catch(function(){ alert('Lỗi mạng khi lấy lịch sử'); });
        });
    }
});

function escapeHtml(s){ if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function nl2br(s){ return (s||'').replace(/\n/g,'<br>'); }
</script>

<!-- Chat modal + floating button -->
<div id="faq-chat-modal" class="fixed inset-0 flex items-end justify-end p-6 hidden">
    <div class="w-full max-w-md bg-gray-900/95 rounded-xl p-4 shadow-lg">
        <div class="flex justify-between items-center mb-2">
            <h4 class="text-white">Chat hỗ trợ</h4>
            <button onclick="closeFaqChat()" class="text-gray-400">Đóng</button>
        </div>
        <form id="faq-chat-form" action="<?php echo URLROOT; ?>/pages/faqMessage" method="post">
            <input type="hidden" name="page_url" value="<?php echo htmlspecialchars( $_SERVER['REQUEST_URI'] ?? '/pages/faq' ); ?>">
            <div id="faq-conversation" class="mb-2 hidden" style="max-height:240px;overflow:auto;background:#0b1220;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.03)"></div>
            <div class="mb-2"><input name="name" placeholder="Tên (tùy chọn)" class="w-full p-2 rounded bg-gray-800 text-gray-200"></div>
            <div class="mb-2"><input name="email" placeholder="Email (tùy chọn)" class="w-full p-2 rounded bg-gray-800 text-gray-200"></div>
            <div class="mb-2"><button type="button" id="faq-view-history" class="btn-send" style="background:transparent;border:1px solid rgba(255,255,255,0.06);padding:6px 10px">Xem lịch sử hỏi đáp</button></div>
            <?php if(!empty($data['categories'])): ?>
            <div class="mb-2">
                <select name="category" class="w-full p-2 rounded bg-gray-800 text-gray-200">
                    <option value="">Chọn danh mục (tùy chọn)</option>
                    <?php foreach($data['categories'] as $c): ?>
                        <option value="<?php echo htmlspecialchars($c->slug); ?>" <?php echo (isset($data['current_category']) && $data['current_category'] === $c->slug) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="mb-2"><textarea name="message" placeholder="Nội dung tin nhắn" required rows="4" class="w-full p-2 rounded bg-gray-800 text-gray-200"></textarea></div>
            <div class="flex justify-end"><button type="submit" class="btn-send">Gửi</button></div>
        </form>
    </div>
</div>
<button onclick="openFaqChat()" title="Chat hỗ trợ" class="fixed right-6 bottom-6 bg-gradient-to-r from-cyan-500 to-purple-600 text-white rounded-full p-4 shadow-lg">Chat</button>
<style>
.faq-category-grid .cat-card{transition:transform .22s ease,box-shadow .22s ease;border-radius:12px;padding:14px;display:block;text-decoration:none;color:inherit}
.faq-category-grid .cat-card{transition:transform .24s cubic-bezier(.2,.9,.2,1),box-shadow .24s ease;border-radius:14px;padding:18px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;color:inherit;background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(0,0,0,0.06));border:1px solid rgba(255,255,255,0.03)}
.faq-category-grid .cat-card:hover{transform:translateY(-8px) scale(1.02);box-shadow:0 18px 40px rgba(2,6,23,0.6)}
.faq-category-grid .cat-card .cat-img{width:100%;height:110px;object-fit:cover;border-radius:10px;margin-bottom:12px;box-shadow:0 8px 20px rgba(0,0,0,0.45);border:1px solid rgba(255,255,255,0.03)}
.faq-category-grid .cat-card .text-sm{font-weight:600;font-size:15px;color:#e6eef8}
.faq-category-grid .cat-card .cat-img + div{margin-top:4px}
.faq-a{transition:max-height .28s ease, opacity .28s ease}
.btn-send{background:linear-gradient(90deg,#06b6d4,#9333ea);color:#fff;border:0;padding:10px 14px;border-radius:8px;cursor:pointer}
#faq-chat-modal{z-index:9999}
</style>
<style>
/* Pagination: horizontal, centered, pill buttons */
.faq-pagination-wrapper{display:flex;justify-content:center;padding:8px 0}
.faq-pagination-wrapper .pagination, .faq-pagination-wrapper nav.pagination{display:block}
.faq-pagination-wrapper .pagination-list{display:flex;gap:8px;align-items:center;list-style:none;margin:0;padding:0}
.faq-pagination-wrapper .page-item{display:inline-flex}
.faq-pagination-wrapper .page-link, .faq-pagination-wrapper .page-ellipsis{display:inline-block;padding:8px 12px;border-radius:999px;background:transparent;border:1px solid rgba(255,255,255,0.04);color:#cbd5e1;text-decoration:none}
.faq-pagination-wrapper .page-link:hover{background:linear-gradient(90deg,#06b6d4,#9333ea);color:#fff;border-color:transparent;box-shadow:0 8px 20px rgba(99,102,241,0.12)}
.faq-pagination-wrapper .page-item.active .page-link, .faq-pagination-wrapper .page-item.active .page-link{background:linear-gradient(90deg,#06b6d4,#9333ea);color:#fff;border-color:transparent}
.faq-pagination-wrapper .page-ellipsis{padding:8px 10px;color:#94a3b8;border:0;background:transparent}
@media (max-width:640px){
    .faq-category-grid .cat-card .cat-img{height:84px}
    .faq-category-grid{grid-template-columns:repeat(2,1fr)}
}
</style>
<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
