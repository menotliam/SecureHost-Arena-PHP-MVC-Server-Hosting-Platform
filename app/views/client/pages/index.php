<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<?php
$ps = $data['public_settings'] ?? [];

$featuredProducts = is_array($data['featured_products'] ?? null) ? $data['featured_products'] : [];
$featuredReview   = $data['featured_review'] ?? null;

$heroBgFile = basename(trim((string) ($ps['home_hero_bg_image'] ?? '')));
$heroBgUrl  = $heroBgFile !== '' ? URLROOT . '/uploads/branding/' . rawurlencode($heroBgFile) : '';

$heroTitleGradient = trim((string) ($ps['home_hero_title_gradient'] ?? 'Game Server Hosting'));
$heroTitlePlain    = trim((string) ($ps['home_hero_title_plain'] ?? 'Cho Mọi Game Thủ'));
$heroSubtitle      = trim((string) ($ps['home_hero_subtitle'] ?? ''));

$homeCardTechTitle = trim((string) ($ps['home_card_tech_title'] ?? 'Năng lực công nghệ'));

$homeAboutKicker  = trim((string) ($ps['home_about_kicker'] ?? ''));
$homeAboutHeading = trim((string) ($ps['home_about_heading'] ?? ''));
$homeAboutLead    = trim((string) ($ps['home_about_lead'] ?? ''));

$aboutHeadingHtml = htmlspecialchars((string) $homeAboutHeading, ENT_QUOTES, 'UTF-8');
if ($homeAboutHeading !== '' && preg_match('/\bg[\-\s]?server\b/iu', (string) $homeAboutHeading)) {
    $parts = preg_split('/(\bg[\-\s]?server\b)/iu', (string) $homeAboutHeading, -1, PREG_SPLIT_DELIM_CAPTURE);
    $aboutHeadingHtml = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        if (preg_match('/^\bg[\-\s]?server\b$/iu', $part)) {
            $aboutHeadingHtml .= '<span class="about-brand-shimmer">' . htmlspecialchars($part, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            $aboutHeadingHtml .= htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
        }
    }
}

$aboutFeatures = [
    [
        'icon' => 'fa-bolt-lightning text-cyan-400',
        'box' => 'bg-cyan-500/10',
        'border' => 'hover:border-cyan-500/40',
        'title' => trim((string) ($ps['home_about_feat1_title'] ?? '')),
        'text' => trim((string) ($ps['home_about_feat1_text'] ?? '')),
    ],
    [
        'icon' => 'fa-shield-halved text-purple-400',
        'box' => 'bg-purple-500/10',
        'border' => 'hover:border-purple-500/40',
        'title' => trim((string) ($ps['home_about_feat2_title'] ?? '')),
        'text' => trim((string) ($ps['home_about_feat2_text'] ?? '')),
    ],
    [
        'icon' => 'fa-clock-rotate-left text-pink-400',
        'box' => 'bg-pink-500/10',
        'border' => 'hover:border-pink-500/40',
        'title' => trim((string) ($ps['home_about_feat3_title'] ?? '')),
        'text' => trim((string) ($ps['home_about_feat3_text'] ?? '')),
    ],
];

$reviewAuthor      = 'Khách hàng G-SERVER';
$reviewAvatarUrl   = '';
$reviewProductName = 'Gói dịch vụ gần đây';
$reviewComment     = 'Hệ thống ổn định, tốc độ tốt và hỗ trợ kỹ thuật rất nhanh.';
$reviewRating      = 5;

if ($featuredReview) {
    $candidateAuthor = trim((string) ($featuredReview->full_name ?? ''));
    if ($candidateAuthor === '') {
        $candidateAuthor = trim((string) ($featuredReview->username ?? ''));
    }
    if ($candidateAuthor !== '') {
        $reviewAuthor = $candidateAuthor;
    }

    $candidateProductName = trim((string) ($featuredReview->product_name ?? ''));
    if ($candidateProductName !== '') {
        $reviewProductName = $candidateProductName;
    }

    $candidateComment = trim((string) ($featuredReview->comment ?? ''));
    if ($candidateComment !== '') {
        $reviewComment = $candidateComment;
    }

    $reviewRating = max(1, min(5, (int) ($featuredReview->rating ?? 5)));

    $avatarRaw = trim((string) ($featuredReview->avatar ?? ''));
    if ($avatarRaw !== '') {
        if (strpos($avatarRaw, 'http://') === 0 || strpos($avatarRaw, 'https://') === 0) {
            $reviewAvatarUrl = $avatarRaw;
        } elseif (strpos($avatarRaw, '/uploads/') === 0) {
            $reviewAvatarUrl = URLROOT . $avatarRaw;
        } elseif (strpos($avatarRaw, 'uploads/') === 0) {
            $reviewAvatarUrl = URLROOT . '/' . ltrim($avatarRaw, '/');
        } else {
            $reviewAvatarUrl = URLROOT . '/uploads/avatars/' . ltrim($avatarRaw, '/');
        }
    }
}

if (function_exists('mb_strlen') && mb_strlen($reviewComment) > 140) {
    $reviewComment = mb_substr($reviewComment, 0, 137) . '...';
} elseif (strlen($reviewComment) > 140) {
    $reviewComment = substr($reviewComment, 0, 137) . '...';
}

?>
<div class="landing-scene">
    <?php if ($heroBgUrl !== ''): ?>
        <div class="landing-scene__image" style="background-image:url('<?php echo htmlspecialchars($heroBgUrl, ENT_QUOTES); ?>');opacity:0.28;"></div>
    <?php else: ?>
        <div class="landing-scene__image"></div>
    <?php endif; ?>
     <div class="landing-scene__fade"></div>
        <!-- Hero Section -->
        <section class="relative overflow-hidden hero-scene" id="hero-parallax">
            <div class="parallax-layer" data-speed="0.08"></div>
            <div class="absolute -top-32 -left-16 w-80 h-80 rounded-full bg-cyan-500/10 blur-3xl parallax-layer" data-speed="0.22"></div>
            <div class="absolute top-20 -right-16 w-80 h-80 rounded-full bg-violet-500/10 blur-3xl parallax-layer" data-speed="0.16"></div>

            <div class="relative max-w-7xl mx-auto px-4 pt-32 pb-24 sm:px-6 lg:px-8">

                <!-- Title + CTA — full width, centered -->
                <div class="text-center space-y-7 max-w-3xl mx-auto">
                    <h1 class="text-4xl md:text-5xl xl:text-6xl 2xl:text-7xl font-extrabold tracking-tight"
                        data-aos="fade-up"
                        aria-label="<?php echo htmlspecialchars(trim($heroTitleGradient . ' ' . $heroTitlePlain), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="hero-gradient-type-line">
                            <span id="hero-typewriter"
                                  class="bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent"
                                  data-typewriter="<?php echo htmlspecialchars($heroTitleGradient, ENT_QUOTES, 'UTF-8'); ?>"></span><span class="hero-type-cursor" aria-hidden="true"></span>
                        </span>
                        <br />
                        <span class="hero-title-plain block text-center text-white"><?php echo htmlspecialchars($heroTitlePlain); ?></span>
                    </h1>

                    <p class="text-lg text-gray-300 max-w-xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="80">
                        <?php echo htmlspecialchars($heroSubtitle); ?>
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center overflow-visible py-1" data-aos="fade-up" data-aos-delay="140">
                        <a href="<?php echo URLROOT; ?>/products"
                        class="hvr-float-shadow group inline-block px-8 py-4 bg-gradient-to-r from-purple-500 to-blue-600 text-white font-bold rounded-xl hover:shadow-[0_0_40px_rgba(6,182,212,0.5)] transition-shadow">
                            Khám phá Gói Server
                            <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#about-us"
                        class="hvr-ripple-out hero-cta-ripple-out inline-block px-8 py-4 border border-gray-700 text-gray-200 font-bold rounded-xl hover:bg-white/5 hover:border-cyan-400/40 transition-colors bg-gray-950/40">
                            Tìm hiểu thêm
                        </a>
                    </div>

                    <!-- Quick Resource Search -->
                    <form id="quick-resource-search" class="flex flex-col sm:flex-row items-center gap-3 max-w-xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                        <div class="flex-shrink-0">
                            <select id="resource_type" data-admin-custom-select="true">
                                <option value="products">Sản phẩm</option>
                                <option value="news">Tin tức</option>
                            </select>
                        </div>
                        <div class="relative flex-1 w-full">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input id="resource_keyword" type="text" maxlength="100"
                                   placeholder="Tìm kiếm sản phẩm, tin tức..."
                                   class="w-full bg-gray-900 border border-gray-700 text-gray-200 text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-cyan-500 placeholder-gray-500">
                        </div>
                        <button type="submit" class="hvr-forward flex-shrink-0 inline-block px-5 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:opacity-90 transition-opacity">
                            Tìm kiếm
                        </button>
                    </form>
                </div>

                <!-- Floating cards: absolute on desktop, stacked on mobile -->
                <div class="hero-cards-wrapper mt-8 grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <!-- Card trái: Năng lực công nghệ -->
                    <aside class="hero-floating-card hero-card-left" data-aos="fade-up" data-aos-delay="100">
                        <p class="text-xs uppercase tracking-widest font-semibold text-cyan-300"><?php echo htmlspecialchars($homeCardTechTitle); ?></p>
                        <div class="mt-3 rounded-xl border border-cyan-500/20 bg-black/60 p-3">
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>CPU / RAM Monitor</span>
                                <span class="text-cyan-300">● Online</span>
                            </div>
                            <div class="mt-2 space-y-1.5">
                                <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                    <span class="block h-full w-[74%] bg-gradient-to-r from-cyan-400 to-cyan-300 rounded-full"></span>
                                </div>
                                <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                    <span class="block h-full w-[61%] bg-gradient-to-r from-violet-400 to-violet-300 rounded-full"></span>
                                </div>
                            </div>
                            <div class="mt-2.5 rounded-lg bg-black border border-slate-800 px-3 py-2 text-[11px] text-slate-300 font-mono leading-snug">
                                <p><span class="text-cyan-400">&gt;</span> start minecraft-node-04</p>
                                <p class="text-emerald-300">Server started in 1.9s ✓</p>
                            </div>
                        </div>
                    </aside>

                    <!-- Card phải: Social Proof -->
                    <aside class="hero-floating-card hero-floating-card--delayed hero-card-right" data-aos="fade-up" data-aos-delay="220">
                        <p class="text-xs uppercase tracking-widest font-semibold text-violet-300">Review gần đây</p>
                        <div class="flex items-center gap-1 mt-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star text-sm <?php echo $i <= $reviewRating ? 'text-yellow-400' : 'text-gray-700'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mt-3 text-sm text-gray-300 leading-relaxed">"<?php echo htmlspecialchars($reviewComment); ?>"</p>
                        <div class="mt-4 flex items-center gap-3">
                            <?php if ($reviewAvatarUrl !== ''): ?>
                                <img src="<?php echo htmlspecialchars($reviewAvatarUrl); ?>"
                                    alt="Avatar khách hàng"
                                    class="w-9 h-9 rounded-full object-cover border border-gray-700 flex-shrink-0">
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-user text-xs text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-sm font-semibold text-white leading-tight"><?php echo htmlspecialchars($reviewAuthor); ?></p>
                                <p class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($reviewProductName); ?></p>
                            </div>
                        </div>
                    </aside>

                </div>

                <!-- Product preview cards: grid desktop, horizontal swipe mobile -->
                <div class="mt-12 home-product-deck" id="home-product-deck">
                    <?php if (empty($featuredProducts)): ?>
                        <div class="home-product-deck__empty p-6 rounded-2xl bg-gray-900 border border-dashed border-gray-700 text-gray-400 text-center" data-aos="fade-up">
                            Chưa có gói server để hiển thị.
                        </div>
                    <?php else: ?>
                        <?php foreach ($featuredProducts as $pi => $product): ?>
                            <?php
                            $productSlug = trim((string) ($product->slug ?? ''));
                            if (isLoggedIn()) {
                                $launchHref = $productSlug !== ''
                                    ? URLROOT . '/products/show/' . rawurlencode($productSlug)
                                    : URLROOT . '/products';
                            } else {
                                $launchHref = URLROOT . '/users/login';
                            }
                            $ramMbTotal = max(0, (int) $product->ram_mb);
                            $ramGb = round($ramMbTotal / 1024, 1);
                            // Độ hiếm theo RAM (minh họa UI — DB chưa có trường rarity)
                            if ($ramMbTotal >= 15360) {
                                $rarityKey = 'legendary';
                                $rarityLabel = 'Legendary';
                                $bandwidthLabel = '10 Gbps';
                            } elseif ($ramMbTotal >= 12288) {
                                $rarityKey = 'epic';
                                $rarityLabel = 'Epic';
                                $bandwidthLabel = '1 Gbps';
                            } elseif ($ramMbTotal >= 6144) {
                                $rarityKey = 'rare';
                                $rarityLabel = 'Rare';
                                $bandwidthLabel = '500 Mbps';
                            } else {
                                $rarityKey = 'common';
                                $rarityLabel = 'Common';
                                $bandwidthLabel = '100 Mbps';
                            }
                            ?>
                            <article class="home-product-card home-product-card--rarity-<?php echo htmlspecialchars($rarityKey, ENT_QUOTES, 'UTF-8'); ?>"
                                     data-home-product-card="true"
                                     data-home-bandwidth="<?php echo htmlspecialchars($bandwidthLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                     data-aos="fade-up"
                                     data-aos-delay="<?php echo (int) min(360, 40 + $pi * 70); ?>">
                                <div class="home-product-card__aura" aria-hidden="true"></div>
                                <div class="home-product-card__inner">
                                    <div class="home-product-card__noise" aria-hidden="true"></div>
                                    <div class="home-product-card__glowspot" aria-hidden="true"></div>
                                    <div class="home-product-card__surface flex flex-col">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="home-product-card__rarity"><?php echo htmlspecialchars($rarityLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <div class="home-product-card__ping text-right shrink-0" title="Độ trễ minh họa (UI)">
                                                <span class="home-product-card__online" aria-hidden="true"></span>
                                                <span class="home-product-card__ping-value font-mono text-[11px] text-emerald-300/95" data-home-product-ping="true" data-ping-base="12">12 ms</span>
                                            </div>
                                        </div>
                                        <h3 class="home-product-card__title mt-3 text-base font-bold text-white leading-tight"><?php echo htmlspecialchars((string) $product->name); ?></h3>
                                        <p class="home-product-card__desc mt-1.5 text-xs text-gray-300 leading-relaxed line-clamp-2"><?php echo htmlspecialchars((string) $product->description); ?></p>
                                        <div class="home-product-card__lower">
                                            <div class="home-product-card__price text-xl font-black text-white">
                                                <?php echo number_format((float) $product->price, 0, ',', '.'); ?>đ
                                                <span class="home-product-card__price-suffix text-xs text-gray-300 font-medium">/tháng</span>
                                            </div>
                                            <div class="home-product-card__specs mt-2.5 grid grid-cols-3 gap-2 text-[12px] text-gray-300 font-medium border-t border-gray-700/80 pt-2.5 text-left">
                                                <span><?php echo (int) $product->cpu_cores; ?> vCPU</span>
                                                <span><?php echo $ramGb; ?> GB RAM</span>
                                                <span><?php echo (int) $product->disk_gb; ?> GB SSD</span>
                                            </div>
                                            <p class="home-product-card__bandwidth home-product-card__bandwidth--static">
                                                <span class="home-product-card__bandwidth-label">Băng thông gói</span>
                                                <span class="home-product-card__bandwidth-value"><?php echo htmlspecialchars($bandwidthLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </p>
                                            <div class="home-product-card__foot-spacer" aria-hidden="true"></div>
                                            <a href="<?php echo htmlspecialchars($launchHref, ENT_QUOTES, 'UTF-8'); ?>" class="product-detail-btn">
                                                <i class="ti-bolt text-yellow-400" style="margin-right: 0.45em;" aria-hidden="true"></i>
                                                Launch
                                            </a>
                                        </div>
                                        <div class="home-product-card__spin" aria-hidden="true"></div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- About Us / Features Section -->
        <section id="about-us" class="py-24 relative border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 space-y-4">
                    <p class="text-cyan-400 font-bold tracking-widest uppercase text-sm" data-aos="fade-up"><?php echo htmlspecialchars($homeAboutKicker); ?></p>
                    <h2 class="text-4xl font-bold text-white" data-aos="fade-up" data-aos-delay="60"><?php echo $aboutHeadingHtml; ?></h2>
                    <p class="text-gray-400 max-w-2xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="120">
                        <?php echo htmlspecialchars($homeAboutLead); ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($aboutFeatures as $fi => $feat): ?>
                    <div class="about-feature-card group p-8 rounded-3xl bg-white/[0.03] border border-white/8 <?php echo htmlspecialchars($feat['border']); ?> transition-all duration-300"
                         data-aos="fade-up"
                         data-aos-delay="<?php echo (int) ($fi * 100); ?>">
                        <div class="w-14 h-14 <?php echo htmlspecialchars($feat['box']); ?> rounded-2xl flex items-center justify-center mb-6">
                            <i class="fa-solid <?php echo htmlspecialchars($feat['icon']); ?> text-2xl about-feature-icon"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4"><?php echo htmlspecialchars($feat['title']); ?></h3>
                        <p class="text-gray-400 leading-relaxed">
                            <?php echo htmlspecialchars($feat['text']); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
</div>
<?php require APPROOT . '/views/layouts/client/footer.php'; ?>