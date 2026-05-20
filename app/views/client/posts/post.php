<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<div id="reading-progress" class="reading-progress"></div>

<div class="article-wrapper max-w-[1400px] mx-auto px-6 py-12">
    <!-- Breadcrumb & Header -->
    <header class="article-header-v2 mb-12" data-aos="fade-down">
        <nav class="breadcrumb flex items-center gap-2 text-xs uppercase tracking-widest text-gray-500 mb-6">
            <a href="<?php echo URLROOT; ?>/posts" class="hover:text-cyan-400 transition-colors">The Feed</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-white"><?php echo $data['article']->title; ?></span>
        </nav>
        
        <h1 class="article-title text-5xl lg:text-7xl font-black font-serif-premium leading-[1.1] text-white mb-8 gs-reveal-text">
            <?php echo $data['article']->title; ?>
        </h1>

        <div class="article-meta-v2 flex flex-wrap items-center gap-8 py-6 border-y border-white/10">
            <div class="meta-author flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center text-xs font-bold">
                    <?php echo substr($data['article']->author_name, 0, 1); ?>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-widest">Written by</div>
                    <div class="text-sm font-bold text-white"><?php echo $data['article']->author_name; ?></div>
                </div>
            </div>
            <div class="meta-date">
                <div class="text-[10px] text-gray-500 uppercase tracking-widest">Published on</div>
                <div class="text-sm font-bold text-white"><?php echo date('F d, Y', strtotime($data['article']->created_at)); ?></div>
            </div>
            <div class="meta-views ml-auto hidden md:flex items-center gap-6">
                <div class="text-right">
                    <div class="text-[10px] text-gray-500 uppercase tracking-widest">Attention</div>
                    <div class="text-sm font-bold text-white"><?php echo number_format($data['article']->views_count); ?> Views</div>
                </div>
                <!-- Article Like Button -->
                <button onclick="toggleLike('news', <?php echo $data['article']->id; ?>)" id="news-like-btn" class="flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 hover:border-pink-500/50 hover:bg-pink-500/10 transition-all <?php echo $data['user_liked'] ? 'text-pink-500 border-pink-500/50 bg-pink-500/10' : 'text-gray-400'; ?>">
                    <i class="<?php echo $data['user_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                    <span id="news-likes-count" class="text-xs font-bold"><?php echo $data['article']->likes_count; ?></span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex flex-col lg:flex-row gap-16">
        <!-- Main Content Column -->
        <div class="lg:w-2/3">
            <?php if($data['article']->thumbnail): ?>
                <div class="article-hero-img relative rounded-3xl overflow-hidden mb-12 border border-white/5 shadow-2xl gs-parallax">
                    <img src="<?php echo URLROOT; ?>/public/uploads/<?php echo $data['article']->thumbnail; ?>" alt="<?php echo $data['article']->title; ?>" class="w-full" data-zoomable>
                </div>
            <?php endif; ?>

            <!-- TL;DR Box -->
            <div class="tldr-box bg-cyan-500/5 border-l-4 border-cyan-500 p-8 rounded-r-2xl mb-12" data-aos="fade-up">
                <h3 class="text-cyan-500 font-bold uppercase tracking-widest text-xs mb-4">The Quick Read</h3>
                <p class="text-lg text-gray-300 italic leading-relaxed">
                    <?php echo substr(strip_tags($data['article']->content), 0, 200); ?>...
                </p>
            </div>

            <article class="article-content-v2 text-xl leading-[1.8] text-gray-300 font-outfit mb-16" data-aos="fade-up">
                <?php 
                    $content = $data['article']->content;
                    // Add drop-cap to the first paragraph
                    $content = preg_replace('/<p>(.*?)<\/p>/', '<p class="drop-cap">$1</p>', $content, 1);
                    $content = preg_replace('/<img /', '<img data-zoomable class="rounded-2xl border border-white/10 my-10" ', $content);
                    $content = preg_replace('/<pre>/', '<pre class="line-numbers language-php rounded-xl my-8">', $content);
                    echo $content; 
                ?>
            </article>

            <!-- Related Content -->
            <?php if(!empty($data['related_news'])): ?>
            <div class="related-content-v2 border-t border-white/10 pt-16 mb-16">
                <h3 class="text-2xl font-black font-serif-premium mb-8">Related Stories</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php foreach($data['related_news'] as $related): ?>
                    <div class="related-card group">
                        <div class="aspect-video rounded-2xl overflow-hidden mb-4 border border-white/5 bg-gray-900">
                            <img src="<?php echo $related->thumbnail ? URLROOT.'/public/uploads/'.$related->thumbnail : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&q=80'; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" alt="">
                        </div>
                        <h4 class="font-bold text-lg leading-tight group-hover:text-cyan-400 transition-colors">
                            <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $related->slug; ?>"><?php echo $related->title; ?></a>
                        </h4>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments Section Integrated -->
            <div class="comments-v2 border-t border-white/10 pt-16">
                <h2 class="text-3xl font-black font-serif-premium mb-10">Join the Conversation</h2>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="<?php echo URLROOT; ?>/posts/comment/<?php echo $data['article']->id; ?>" method="POST" class="mb-12">
                        <textarea name="comment" class="w-full bg-white/5 border border-white/10 rounded-2xl p-6 focus:outline-none focus:border-cyan-500 transition-all min-h-[150px] mb-4" placeholder="Share your thoughts..."></textarea>
                        <button type="submit" class="bg-white text-black font-bold px-8 py-3 rounded-full hover:bg-cyan-500 hover:text-white transition-all transform active:scale-95">Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="p-8 rounded-2xl bg-white/5 border border-dashed border-white/20 text-center mb-12">
                        <p class="text-gray-400">Please <a href="<?php echo URLROOT; ?>/users/login" class="text-cyan-400 font-bold">login</a> to participate in the discussion.</p>
                    </div>
                <?php endif; ?>

                <div id="comments-list" class="space-y-8">
                    <!-- Dynamic comments -->
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <aside class="lg:w-1/3">
            <div class="sticky top-28 space-y-12">
                <!-- Social Sidebar -->
                <div class="social-share-v2">
                    <h3 class="text-[10px] text-gray-500 uppercase tracking-widest mb-4">Share this story</h3>
                    <div class="flex gap-4">
                        <a href="#" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-[#1877f2] hover:border-[#1877f2] transition-all"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-[#1da1f2] hover:border-[#1da1f2] transition-all"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-cyan-500 hover:border-cyan-500 transition-all"><i class="fas fa-link"></i></a>
                    </div>
                </div>

                <!-- Trending Sidebar -->
                <div class="trending-sidebar">
                    <h3 class="text-2xl font-black font-serif-premium mb-6 border-b-4 border-orange-500 inline-block">Trending</h3>
                    <div class="space-y-6 mt-6">
                        <?php foreach($data['trending'] as $index => $trend): ?>
                        <div class="flex gap-4 items-start group">
                            <span class="text-xl font-black text-white/20">0<?php echo $index + 1; ?></span>
                            <div class="flex-1">
                                <h4 class="font-bold text-sm leading-tight group-hover:text-cyan-400 transition-colors">
                                    <a href="<?php echo URLROOT; ?>/posts/show/<?php echo $trend->slug; ?>"><?php echo $trend->title; ?></a>
                                </h4>
                                <span class="text-[10px] text-gray-500 uppercase tracking-widest"><?php echo date('M d', strtotime($trend->created_at)); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sticky Sidebar Ad -->
                <?php foreach($data['ads'] as $ad): ?>
                <div class="ad-sidebar-item rounded-3xl overflow-hidden border border-white/10 group relative">
                    <a href="<?php echo $ad->link_url; ?>" target="_blank">
                        <img src="<?php echo URLROOT . $ad->image_url; ?>" alt="<?php echo $ad->title; ?>" class="w-full h-auto group-hover:scale-105 transition-transform">
                        <div class="absolute top-2 right-2 px-2 py-0.5 bg-black/50 backdrop-blur-md rounded text-[8px] font-bold uppercase tracking-tighter text-white/70">Sponsored</div>
                    </a>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($data['ads'])): ?>
                <div class="newsletter-card bg-gradient-to-br from-purple-900/40 to-blue-900/40 p-8 rounded-3xl border border-white/10">
                    <h3 class="text-xl font-bold mb-2">Cloud Arena Daily</h3>
                    <p class="text-sm text-gray-400 mb-6">Đăng ký nhận tin tức công nghệ mới nhất hàng tuần.</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="Email" class="flex-1 bg-black/40 border border-white/10 rounded-lg px-4 py-2 text-xs focus:outline-none focus:border-cyan-500">
                        <button class="bg-cyan-500 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase">Join</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<style>
/* Reading Progress Bar */
.reading-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #06b6d4, #a855f7, #ec4899);
    z-index: 9999;
    transition: width 0.1s ease;
}

.article-wrapper {
    background: radial-gradient(circle at top right, rgba(6, 182, 212, 0.03), transparent 40%);
}

.article-title {
    background: linear-gradient(to bottom, #fff 40%, #94a3b8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.drop-cap::first-letter {
    font-family: 'Playfair Display', serif;
    float: left;
    font-size: 5rem;
    line-height: 1;
    font-weight: 900;
    margin-right: 1rem;
    color: #06b6d4;
    text-transform: uppercase;
}

.article-content-v2 p {
    margin-bottom: 2rem;
}

.article-content-v2 blockquote {
    border-left: 4px solid #06b6d4;
    padding-left: 2rem;
    font-style: italic;
    font-size: 1.5rem;
    margin: 3rem 0;
    color: #fff;
}

/* Custom Scrollbar for Pre/Code */
pre::-webkit-scrollbar {
    height: 8px;
}
pre::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.2);
}
pre::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

/* Sidebar sticky adjustment */
aside .sticky {
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    scrollbar-width: none; /* Firefox */
}
aside .sticky::-webkit-scrollbar {
    display: none; /* Chrome/Safari */
}

@media (max-width: 1024px) {
    .article-title { font-size: 3rem; }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Register GSAP plugins
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            // GSAP ScrollTrigger for Reading Progress
            gsap.to("#reading-progress", {
                width: "100%",
                ease: "none",
                scrollTrigger: {
                    trigger: "body",
                    start: "top top",
                    end: "bottom bottom",
                    scrub: 0.3
                }
            });

            // GSAP Parallax Effect for Hero Image
            if (document.querySelector('.gs-parallax img')) {
                gsap.to(".gs-parallax img", {
                    y: "10%",
                    ease: "none",
                    scrollTrigger: {
                        trigger: ".gs-parallax",
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            }

            // GSAP Reveal Text
            gsap.from(".gs-reveal-text", {
                y: 50,
                opacity: 0,
                duration: 1.2,
                ease: "power4.out",
                delay: 0.2
            });
        }

        // Fetching real-time comments (existing logic)
        const newsId = <?php echo $data['article']->id; ?>;
        let lastCommentId = <?php echo !empty($data['comments']) ? $data['comments'][0]->id : 0; ?>;

        function renderComment(comment) {
            const avatar = comment.avatar ? `<img src="<?php echo URLROOT; ?>/public/uploads/${comment.avatar}" alt="Avatar" class="w-full h-full object-cover">` : `<div class="w-full h-full bg-gray-800 flex items-center justify-center border border-gray-700 rounded-full"><i class="fa-solid fa-user text-xs"></i></div>`;
            
            return `
                <div class="comment-item-v2 flex gap-6 group" data-id="${comment.id}">
                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                        ${avatar}
                    </div>
                    <div class="flex-1 pb-8 border-b border-white/5">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-white">${comment.username}</span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest">${comment.created_at_formatted}</span>
                        </div>
                        <div class="text-gray-400 text-sm leading-relaxed mb-4">${comment.comment}</div>
                        <div class="flex items-center gap-4">
                            <button onclick="toggleLike('comment', ${comment.id})" id="comment-like-${comment.id}" class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest transition-colors ${comment.user_liked ? 'text-pink-500' : 'text-gray-500 hover:text-pink-400'}">
                                <i class="${comment.user_liked ? 'fa-solid' : 'fa-regular'} fa-heart text-xs"></i>
                                <span>Like</span>
                                <span id="comment-likes-count-${comment.id}" class="ml-1">${comment.likes_count || 0}</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function fetchNewComments() {
            fetch(`<?php echo URLROOT; ?>/posts/api_comments/${newsId}/${lastCommentId}`)
                .then(response => response.json())
                .then(comments => {
                    if (comments && comments.length > 0) {
                        const list = document.getElementById('comments-list');
                        comments.forEach(comment => {
                            if (document.querySelector(`.comment-item-v2[data-id="${comment.id}"]`)) return;
                            list.insertAdjacentHTML('afterbegin', renderComment(comment));
                            if (parseInt(comment.id) > lastCommentId) lastCommentId = parseInt(comment.id);
                        });
                    }
                });
        }

        // Like Functionality
        window.toggleLike = function(type, id) {
            fetch(`<?php echo URLROOT; ?>/posts/toggle_like/${type}/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (type === 'news') {
                            const btn = document.getElementById('news-like-btn');
                            const icon = btn.querySelector('i');
                            const count = document.getElementById('news-likes-count');
                            
                            if (icon.classList.contains('fa-regular')) {
                                icon.classList.replace('fa-regular', 'fa-solid');
                                btn.classList.add('text-pink-500', 'border-pink-500/50', 'bg-pink-500/10');
                            } else {
                                icon.classList.replace('fa-solid', 'fa-regular');
                                btn.classList.remove('text-pink-500', 'border-pink-500/50', 'bg-pink-500/10');
                                btn.classList.add('text-gray-400');
                            }
                            count.textContent = data.likes_count;
                        } else {
                            const btn = document.getElementById(`comment-like-${id}`);
                            const icon = btn.querySelector('i');
                            const count = document.getElementById(`comment-likes-count-${id}`);
                            
                            if (icon.classList.contains('fa-regular')) {
                                icon.classList.replace('fa-regular', 'fa-solid');
                                btn.classList.add('text-pink-500');
                                btn.classList.remove('text-gray-500');
                                count.textContent = parseInt(count.textContent) + 1;
                            } else {
                                icon.classList.replace('fa-solid', 'fa-regular');
                                btn.classList.remove('text-pink-500');
                                btn.classList.add('text-gray-500');
                                count.textContent = parseInt(count.textContent) - 1;
                            }
                        }
                    } else if (data.message === 'Unauthorized') {
                        window.location.href = '<?php echo URLROOT; ?>/users/login';
                    }
                });
        };

        // Initial render for existing comments
        const list = document.getElementById('comments-list');
        <?php foreach($data['comments'] as $comment): ?>
            list.insertAdjacentHTML('beforeend', renderComment({
                id: '<?php echo $comment->id; ?>',
                username: '<?php echo $comment->username; ?>',
                comment: `<?php echo addslashes(nl2br(htmlspecialchars($comment->comment))); ?>`,
                avatar: '<?php echo $comment->avatar; ?>',
                created_at_formatted: '<?php echo date('H:i d/m/Y', strtotime($comment->created_at)); ?>',
                user_liked: <?php echo isset($comment->user_liked) && $comment->user_liked ? 'true' : 'false'; ?>,
                likes_count: <?php echo $comment->likes_count; ?>
            }));
        <?php endforeach; ?>

        setInterval(fetchNewComments, 10000);
    });
</script>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
