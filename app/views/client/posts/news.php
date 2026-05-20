<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<div class="news-container max-w-[1400px] mx-auto px-6 py-12" id="news-section">
    <div class="news-header mb-16 text-center" data-aos="fade-down">
        <h1 class="premium-title text-6xl font-black font-serif-premium tracking-tight mb-4">The Feed</h1>
        <p class="premium-subtitle text-gray-400 text-lg max-w-2xl mx-auto">Khám phá những câu chuyện mới nhất về công nghệ, gaming và hạ tầng máy chủ.</p>
        
        <div class="search-and-filter mt-10 flex flex-col md:flex-row items-center justify-between gap-6 border-b border-white/10 pb-8">
            <div class="filter-group flex flex-wrap gap-3">
                <a href="<?php echo URLROOT; ?>/posts" class="filter-btn <?php echo empty($data['category']) ? 'active' : ''; ?>">Tất cả</a>
                <a href="<?php echo URLROOT; ?>/posts?category=gaming-server" class="filter-btn <?php echo $data['category'] == 'gaming-server' ? 'active' : ''; ?>">Server Game</a>
                <a href="<?php echo URLROOT; ?>/posts?category=web-hosting" class="filter-btn <?php echo $data['category'] == 'web-hosting' ? 'active' : ''; ?>">Hosting</a>
                <a href="<?php echo URLROOT; ?>/posts?category=huong-dan" class="filter-btn <?php echo $data['category'] == 'huong-dan' ? 'active' : ''; ?>">Hướng dẫn</a>
            </div>
            
            <div class="search-wrapper-v2 relative w-full md:w-80">
                <input type="text" id="news-search" class="w-full bg-white/5 border border-white/10 rounded-full py-3 px-6 text-sm focus:outline-none focus:border-cyan-500 transition-all" placeholder="Tìm kiếm câu chuyện..." value="<?php echo htmlspecialchars($data['search']); ?>">
                <i class="fas fa-search absolute right-5 top-1/2 -translate-y-1/2 text-gray-500"></i>
            </div>
        </div>
    </div>

    <?php if(!empty($data['news'])): ?>
    <!-- Editorial Hero Grid (Bento Box) -->
    <div class="editorial-grid mb-20">
        <?php 
            $main = $data['news'][0] ?? null;
            $side1 = $data['news'][1] ?? null;
            $side2 = $data['news'][2] ?? null;
        ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Main Story -->
            <?php if($main): ?>
            <div class="lg:col-span-8 group relative overflow-hidden rounded-3xl bg-gray-900 border border-white/5 h-[500px] lg:h-[650px]" data-aos="fade-right">
                <img src="<?php echo $main->thumbnail ? URLROOT.'/public/uploads/'.$main->thumbnail : 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&q=80'; ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-60" alt="<?php echo htmlspecialchars($main->title); ?>">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-900/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-8 lg:p-12 w-full">
                    <span class="inline-block px-3 py-1 bg-cyan-500 text-white text-[10px] font-bold uppercase tracking-widest rounded mb-4">Tiêu điểm</span>
                    <h2 class="text-4xl lg:text-6xl font-black font-serif-premium leading-[1.1] mb-6">
                        <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $main->slug; ?>" class="hover:text-cyan-400 transition-colors"><?php echo $main->title; ?></a>
                    </h2>
                    <div class="flex items-center gap-6 text-sm text-gray-400">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-user text-cyan-500"></i> <?php echo $main->author_name; ?></span>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-calendar"></i> <?php echo date('d M, Y', strtotime($main->created_at)); ?></span>
                        <span class="flex items-center gap-2"><i class="fa-solid fa-eye"></i> <?php echo number_format($main->views_count); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Side Stories -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <?php foreach([$side1, $side2] as $index => $side): if($side): ?>
                <div class="flex-1 group relative overflow-hidden rounded-3xl bg-gray-900 border border-white/5 min-h-[240px]" data-aos="fade-left" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <img src="<?php echo $side->thumbnail ? URLROOT.'/public/uploads/'.$side->thumbnail : 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80'; ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-40" alt="<?php echo htmlspecialchars($side->title); ?>">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-8">
                        <h3 class="text-2xl font-bold font-serif-premium leading-tight mb-3">
                            <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $side->slug; ?>" class="hover:text-cyan-400 transition-colors"><?php echo $side->title; ?></a>
                        </h3>
                        <div class="text-xs text-gray-400 uppercase tracking-wider"><?php echo date('d M, Y', strtotime($side->created_at)); ?></div>
                    </div>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- The River of News & Trending Sidebar -->
    <div class="flex flex-col lg:flex-row gap-12 mt-12">
        <div class="lg:w-3/4">
            <div class="news-river grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="news-grid">
                <?php 
                    $remaining = array_slice($data['news'], 3);
                    foreach($remaining as $index => $article): 
                ?>
                <div class="news-card-v2 group" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 50; ?>" data-title="<?php echo htmlspecialchars(strtolower($article->title)); ?>" data-category="<?php echo htmlspecialchars($article->category_slug ?? ''); ?>">
                    <div class="relative aspect-video rounded-2xl overflow-hidden mb-5 border border-white/5">
                        <img src="<?php echo $article->thumbnail ? URLROOT.'/public/uploads/'.$article->thumbnail : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&q=80'; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="<?php echo htmlspecialchars($article->title); ?>">
                        <div class="absolute top-4 left-4">
                            <span class="px-2 py-1 bg-white/10 backdrop-blur-md border border-white/10 rounded text-[10px] font-bold uppercase tracking-wider text-white">
                                <?php echo $article->category_name ?? 'Tin tức'; ?>
                            </span>
                        </div>
                    </div>
                    <h4 class="card-title text-xl font-bold font-serif-premium leading-snug mb-3 line-clamp-2">
                        <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $article->slug; ?>" class="hover:text-cyan-400 transition-colors"><?php echo $article->title; ?></a>
                    </h4>
                    <p class="card-excerpt text-gray-400 text-sm line-clamp-2 mb-4"><?php echo substr(strip_tags($article->content), 0, 100); ?>...</p>
                    <div class="flex items-center justify-between pt-4 border-t border-white/5 text-[10px] text-gray-500 uppercase tracking-widest">
                        <span class="card-author">By <?php echo $article->author_name; ?></span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-eye"></i> <?php echo $article->views_count; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if(empty($remaining) && count($data['news']) <= 3): ?>
                <!-- Only hero items, no river cards -->
            <?php endif; ?>
        </div>

        <!-- Trending Sidebar -->
        <aside class="lg:w-1/4">
            <div class="sticky top-24">
                <div class="trending-section bg-white/5 border border-white/10 rounded-3xl p-6 mb-8">
                    <h3 class="text-lg font-bold font-serif-premium flex items-center gap-3 mb-6">
                        <i class="fa-solid fa-fire text-orange-500"></i> Trending Now
                    </h3>
                    <div class="space-y-6">
                        <?php if(!empty($data['trending'])): ?>
                            <?php foreach($data['trending'] as $index => $trend): ?>
                            <div class="trending-item flex gap-4 group">
                                <span class="text-2xl font-black text-white/20 group-hover:text-cyan-500 transition-colors">0<?php echo $index + 1; ?></span>
                                <div>
                                    <h4 class="text-sm font-bold leading-snug mb-1">
                                        <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $trend->slug; ?>" class="hover:text-cyan-400 transition-colors line-clamp-2"><?php echo $trend->title; ?></a>
                                    </h4>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-widest"><?php echo date('M d', strtotime($trend->created_at)); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm">Chưa có bài viết trending.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Ads Sidebar -->
                <?php if(!empty($data['ads'])): ?>
                    <?php foreach($data['ads'] as $ad): ?>
                    <div class="ad-sidebar-item rounded-3xl overflow-hidden border border-white/10 group relative mb-8">
                        <a href="<?php echo $ad->link_url; ?>" target="_blank">
                            <img src="<?php echo URLROOT . $ad->image_url; ?>" alt="<?php echo $ad->title; ?>" class="w-full h-auto group-hover:scale-105 transition-transform">
                            <div class="absolute top-2 right-2 px-2 py-0.5 bg-black/50 backdrop-blur-md rounded text-[8px] font-bold uppercase tracking-tighter text-white/70">Sponsored</div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Newsletter Card -->
                <div class="newsletter-card bg-gradient-to-br from-purple-900/40 to-blue-900/40 p-8 rounded-3xl border border-white/10">
                    <h3 class="text-xl font-bold mb-2">Cloud Arena Daily</h3>
                    <p class="text-sm text-gray-400 mb-6">Đăng ký nhận tin tức công nghệ mới nhất hàng tuần.</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="Email" class="flex-1 bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-xs focus:outline-none focus:border-cyan-500">
                        <button class="bg-cyan-500 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase hover:bg-cyan-600 transition-colors">Join</button>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <?php else: ?>
    <!-- Empty State -->
    <div class="text-center py-20" data-aos="fade-up">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
            <i class="fa-solid fa-newspaper text-3xl text-gray-600"></i>
        </div>
        <h2 class="text-2xl font-bold mb-4">Chưa có bài viết nào</h2>
        <p class="text-gray-400 max-w-md mx-auto">Hiện tại chưa có bài viết tin tức nào được đăng. Hãy quay lại sau nhé!</p>
    </div>
    <?php endif; ?>
</div>

<style>
.news-container {
    background: radial-gradient(circle at top right, rgba(6, 182, 212, 0.05), transparent 40%),
                radial-gradient(circle at bottom left, rgba(147, 51, 234, 0.05), transparent 40%);
}

.premium-title {
    background: linear-gradient(to bottom, #fff 30%, #64748b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.filter-btn {
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #94a3b8;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.filter-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.2);
}

.filter-btn.active {
    background: #fff;
    color: #000;
    border-color: #fff;
}

.news-card-v2 {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.news-card-v2:hover {
    transform: translateY(-5px);
}

.news-card-v2.is-hidden {
    display: none;
}

.editorial-grid h2 a, .editorial-grid h3 a {
    position: relative;
    background-image: linear-gradient(rgba(6, 182, 212, 0.5), rgba(6, 182, 212, 0.5));
    background-position: 0% 100%;
    background-repeat: no-repeat;
    background-size: 0% 2px;
    transition: background-size 0.3s;
}

.editorial-grid h2 a:hover, .editorial-grid h3 a:hover {
    background-size: 100% 2px;
}

.news-river {
    transition: height 0.4s ease;
}

@media (max-width: 1024px) {
    .editorial-grid h2 { font-size: 2.5rem; }
    .editorial-grid .h-\[650px\] { height: 500px; }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Vanilla JS Real-time Search (replaces broken Isotope/List.js)
        const searchInput = document.getElementById('news-search');
        const newsGrid = document.getElementById('news-grid');
        
        if (searchInput && newsGrid) {
            const cards = newsGrid.querySelectorAll('.news-card-v2');
            
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let visibleCount = 0;
                
                cards.forEach(function(card) {
                    const title = card.getAttribute('data-title') || '';
                    const excerpt = card.querySelector('.card-excerpt');
                    const excerptText = excerpt ? excerpt.textContent.toLowerCase() : '';
                    const authorEl = card.querySelector('.card-author');
                    const authorText = authorEl ? authorEl.textContent.toLowerCase() : '';
                    
                    const match = query === '' || 
                                  title.includes(query) || 
                                  excerptText.includes(query) || 
                                  authorText.includes(query);
                    
                    if (match) {
                        card.classList.remove('is-hidden');
                        card.style.opacity = '1';
                        card.style.transform = '';
                        visibleCount++;
                    } else {
                        card.classList.add('is-hidden');
                        card.style.opacity = '0';
                    }
                });
            });
        }

        // GSAP entrance animation for news cards
        if (typeof gsap !== 'undefined') {
            gsap.from('.news-card-v2', {
                y: 40,
                opacity: 0,
                duration: 0.6,
                stagger: 0.08,
                ease: 'power3.out',
                delay: 0.3
            });
            
            gsap.from('.trending-item', {
                x: 30,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: 'power2.out',
                delay: 0.5
            });
        }
    });
</script>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
