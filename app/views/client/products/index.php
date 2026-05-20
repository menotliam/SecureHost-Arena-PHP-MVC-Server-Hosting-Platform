<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6" data-aos="fade-down">
        <div>
            <h2 class="text-4xl font-black text-white tracking-tighter mb-2">SERVER CỦA BẠN.</h2>
            <p class="text-gray-400">Chọn cấu hình phù hợp nhất cho dự án của bạn.</p>
        </div>
        <form action="<?= URLROOT ?>/products" method="GET" class="w-full flex flex-col md:flex-row gap-4" id="searchForm">
            <div class="relative flex-1 group">
                <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($data['keyword'] ?? '', ENT_QUOTES) ?>" placeholder="Tìm kiếm server..." 
                       class="w-full bg-gray-900 border border-gray-800 text-white px-6 py-4 rounded-2xl focus:outline-none focus:border-cyan-500 transition-all duration-500">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-hover:text-cyan-400 transition-colors">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <div id="liveSearchResults" class="absolute z-50 w-full mt-2 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden hidden transform origin-top transition-all duration-300">
                    <div class="p-2 max-h-[350px] overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-gray-600 transition-colors" id="liveSearchContent">
                    </div>
                </div>
            </div>

            <select name="category" id="categoryFilter" class="bg-gray-900 border border-gray-800 text-gray-300 px-6 py-4 rounded-2xl focus:outline-none focus:border-cyan-500 cursor-pointer">
                <option value="">Tất cả danh mục</option>
                <?php foreach($data['categories'] ?? [] as $cat): ?>
                    <option value="<?= (int)$cat->id ?>" <?= (isset($data['categoryId']) && $data['categoryId'] == $cat->id) ? 'selected' : '' ?>><?= htmlspecialchars($cat->name, ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex gap-2">
                <input type="number" name="min_price" id="minPrice" value="<?= htmlspecialchars($data['minPrice'] ?? '', ENT_QUOTES) ?>" placeholder="Giá từ..." class="w-28 bg-gray-900 border border-gray-800 text-white px-4 py-4 rounded-2xl focus:outline-none focus:border-cyan-500">
                <span class="text-gray-500 self-center">-</span>
                <input type="number" name="max_price" id="maxPrice" value="<?= htmlspecialchars($data['maxPrice'] ?? '', ENT_QUOTES) ?>" placeholder="Đến..." class="w-28 bg-gray-900 border border-gray-800 text-white px-4 py-4 rounded-2xl focus:outline-none focus:border-cyan-500">
            </div>

            <button type="submit" class="bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-lg shadow-cyan-500/25">
                <i class="fa-solid fa-filter mr-2"></i> Lọc
            </button>
        </form>
    </div>

    <div id="loadingOverlay" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-cyan-500/20 border-t-cyan-500 rounded-full animate-spin"></div>
        <p class="text-cyan-400 mt-4 font-bold tracking-widest animate-pulse">ĐANG TẢI DỮ LIỆU...</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10" id="productsGrid">
        <?php foreach($data['products'] as $p): ?>
        <div class="group relative bg-gray-900 rounded-[2rem] overflow-hidden border border-gray-800 hover:border-cyan-500/50 transition-all duration-700 shadow-2xl product-card" data-aos="fade-up">
            <div class="relative h-64 overflow-hidden bg-gray-800">
                <?php
                $img = !empty($p->image_url) ? $p->image_url : (!empty($p->image) ? $p->image : '');
                if (!empty($img)):
                    // If already a full URL or data URI, use it as-is. Otherwise build uploads URL and encode path segments.
                    if (preg_match('#^https?://#i', $img) || strpos($img, 'data:') === 0) {
                        $imgPath = $img;
                    } else {
                        // support values like "media/xxx", "uploads/xxx" or "branding/xxx"
                        $segments = explode('/', ltrim($img, '/'));
                        $encSegments = array_map('rawurlencode', $segments);
                        $imgPath = rtrim(URLROOT, '/') . '/' . implode('/', $encSegments);
                    }
                ?>
                    <img src="<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000 ease-in-out">
                <?php else: ?>
                    <div class="w-full h-full flex flex-col items-center justify-center transform group-hover:scale-110 transition-transform duration-1000 ease-in-out opacity-60">
                        <i class="fa-solid fa-server text-5xl text-gray-500 mb-3"></i>
                        <span class="text-gray-500 text-xs font-bold tracking-widest uppercase">No Image</span>
                    </div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent opacity-60"></div>

                <div class="absolute inset-0 flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-[2px]">
                    <button type="button" data-product-id="<?= (int)$p->id ?>" class="add-to-cart bg-white text-black rounded-full flex items-center justify-center hover:bg-cyan-500 hover:text-white transform translate-y-10 group-hover:translate-y-0 transition-all duration-500 shadow-xl" style="width: 3.5rem; height: 3.5rem; outline:none;">
                        <i class="fa-solid fa-cart-plus text-xl"></i>
                    </button>
                    <a href="<?= URLROOT ?>/products/show/<?= htmlspecialchars($p->slug, ENT_QUOTES, 'UTF-8') ?>" class="w-14 h-14 bg-gray-800 text-white rounded-full flex items-center justify-center hover:bg-purple-500 transform translate-y-10 group-hover:translate-y-0 transition-all duration-500 delay-75 shadow-xl border border-gray-700">
                        <i class="fa-solid fa-expand text-xl"></i>
                    </a>
                </div>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-cyan-400 text-[10px] font-black uppercase tracking-[0.2em]"><?= htmlspecialchars($p->category_name ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-white font-bold"><?= number_format($p->price, 0, ',', '.') ?>đ<span class="text-gray-500 text-xs font-normal">/th</span></span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-6 group-hover:text-cyan-400 transition-colors"><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8') ?></h3>
                
                <div class="grid grid-cols-3 gap-2 py-4 border-t border-gray-800">
                    <div class="text-center">
                        <i class="fa-solid fa-microchip text-gray-600 mb-1"></i>
                        <div class="text-white text-xs font-bold"><?= $p->cpu_cores ?> vCPU</div>
                    </div>
                    <div class="text-center">
                        <i class="fa-solid fa-memory text-gray-600 mb-1"></i>
                        <div class="text-white text-xs font-bold"><?= $p->ram_mb/1024 ?>GB RAM</div>
                    </div>
                    <div class="text-center">
                        <i class="fa-solid fa-hdd text-gray-600 mb-1"></i>
                        <div class="text-white text-xs font-bold"><?= $p->disk_gb ?>GB SSD</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($data['products'])): ?>
            <div class="col-span-full py-20 text-center">
                <i class="fa-solid fa-ghost text-6xl text-gray-700 mb-4 block"></i>
                <h3 class="text-xl text-gray-400 font-bold">Không tìm thấy máy chủ nào</h3>
            </div>
        <?php endif; ?>
    </div>

    <?php if(isset($data['totalPages']) && $data['totalPages'] > 1): ?>
    <div class="mt-20 flex justify-center items-center gap-4">
        <?php for($i=1; $i<=$data['totalPages']; $i++): 
            $qs = http_build_query([
                'page' => $i,
                'search' => $data['keyword'] ?? '',
                'category' => $data['categoryId'] ?? '',
                'min_price' => $data['minPrice'] ?? '',
                'max_price' => $data['maxPrice'] ?? ''
            ]);
        ?>
            <a href="?<?= $qs ?>" 
               class="w-12 h-12 flex items-center justify-center rounded-xl border <?= ($data['currentPage'] ?? 1) == $i ? 'bg-cyan-500 border-cyan-500 text-black font-bold focus:ring-2 focus:ring-cyan-500/50' : 'bg-gray-900 border-gray-800 text-gray-400 hover:border-cyan-500 transition-colors' ?> pagination-link" data-page="<?= $i ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    AOS.init({ duration: 1000, once: true });

    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. AJAX Thêm vào Giỏ Hàng (Animation giỏ hàng trên Navbar) ---
        const initAddToCart = () => {
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    
                    fetch(`<?= URLROOT ?>/cart/add/${productId}?ajax=1`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        credentials: 'same-origin',
                        body: 'quantity=1'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            // Không chuyển trang nữa, mà tìm thẻ span chứa số lượng trên Header để cập nhật
                            const cartBadge = document.querySelector('.fa-cart-shopping').nextElementSibling;
                            if(cartBadge) {
                                cartBadge.textContent = data.cartCount;
                                // Thêm hiệu ứng giật (bounce) nhẹ để thu hút sự chú ý
                                cartBadge.classList.add('animate-bounce');
                                setTimeout(() => cartBadge.classList.remove('animate-bounce'), 1000);
                            }
                            
                            // Hiển thị thông báo Toast góc trên bên phải
                            Swal.fire({
                                title: 'Thành công!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false
                            });
                        } else if (data.message) {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Lỗi hệ thống', 'Không thể kết nối đến máy chủ.', 'error');
                    });
                });
            });
        };
        initAddToCart();


        // --- 2. Live Search (Debounce) ---
        const searchInput = document.getElementById('searchInput');
        const liveSearchResults = document.getElementById('liveSearchResults');
        const liveSearchContent = document.getElementById('liveSearchContent');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const term = this.value.trim();
            
            if(term.length === 0) {
                liveSearchResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                // Gọi API sang chính Controller Products nhưng có tham số ajax_search
                fetch(`<?= URLROOT ?>/products?search=${encodeURIComponent(term)}&ajax_search=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.products && data.products.length > 0) {
                        let html = '';
                        data.products.forEach(p => {
                            // Fix SEO URL: Dùng biến ${p.slug} thay vì PHP tag
                            // build safe image URL from product.image_url (strip leading slash, encode segments)
                            var root = <?= json_encode(rtrim(URLROOT, '/')) ?>;
                            var cleaned = (p.image_url || '').replace(/^\//, '');
                            var imgSrc = cleaned ? root + '/' + cleaned.split('/').map(encodeURIComponent).join('/') : '';
                            html += `
                                <a href="<?= URLROOT ?>/products/show/${p.slug}" class="flex items-center gap-4 p-3 hover:bg-gray-800 rounded-xl transition-colors">
                                    <img src="${imgSrc}" class="w-12 h-12 object-cover rounded-lg border border-gray-700">
                                    <div>
                                        <h4 class="text-white text-sm font-bold">${p.name}</h4>
                                        <span class="text-cyan-400 text-xs">${new Intl.NumberFormat('vi-VN').format(p.price)}đ/th</span>
                                    </div>
                                </a>`;
                        });
                        liveSearchContent.innerHTML = html;
                        liveSearchResults.classList.remove('hidden');
                    } else {
                        liveSearchContent.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Không tìm thấy sản phẩm...</div>';
                        liveSearchResults.classList.remove('hidden');
                    }
                })
                .catch(err => console.error(err));
            }, 300); // 300ms debounce
        });

        // Ẩn bảng kết quả Live Search khi click ra ngoài
        document.addEventListener('click', function(e) {
            if(!searchInput.contains(e.target) && !liveSearchResults.contains(e.target)) {
                liveSearchResults.classList.add('hidden');
            }
        });


        // --- 3. AJAX Phân trang (Không Load lại Website) ---
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');

                fetch(url + '&ajax_page=1', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text()) // Nhận Raw HTML phần grid và pagination
                .then(html => {
                    // Extract nội dung grid và pagination từ raw HTML để thay thế (đơn giản, an toàn)
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    const newGrid = tempDiv.querySelector('#productsGrid');
                    if(newGrid) {
                        document.getElementById('productsGrid').innerHTML = newGrid.innerHTML;
                        initAddToCart(); // Gọi lại hàm gắn event listener cho nút add to cart mới
                    }

                    // Tự update URL bar mà không reload (History API)
                    window.history.pushState({}, '', url);

                    // Re-init AOS Animations
                    AOS.refreshHard();

                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                    
                    window.scrollTo({ top: document.getElementById('productsGrid').offsetTop - 100, behavior: 'smooth' });
                })
                .catch(err => {
                    console.error(err);
                    window.location.href = url; // Fallback
                });
            });
        });
    });
</script>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>