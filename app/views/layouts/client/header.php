<?php
$publicSettings = $data['public_settings'] ?? [];
$siteLogoText = $publicSettings['site_logo_text'] ?? 'G-SERVER';
$siteLogoImageFile = basename((string) ($publicSettings['site_logo_image'] ?? ''));
$siteLogoImageUrl = $siteLogoImageFile !== '' ? URLROOT . '/uploads/branding/' . rawurlencode($siteLogoImageFile) : '';
$sessionAvatarRaw = trim((string) ($_SESSION['user_avatar'] ?? ''));
$sessionAvatarUrl = '';

if ($sessionAvatarRaw !== '') {
    if (strpos($sessionAvatarRaw, 'http://') === 0 || strpos($sessionAvatarRaw, 'https://') === 0) {
        $sessionAvatarUrl = $sessionAvatarRaw;
    } 
    elseif (strpos($sessionAvatarRaw, 'uploads/') === 0 || strpos($sessionAvatarRaw, '/uploads/') === 0) {
        $sessionAvatarUrl = URLROOT . '/' . ltrim($sessionAvatarRaw, '/');
    }
    else {
        $sessionAvatarUrl = URLROOT . '/uploads/' . ltrim($sessionAvatarRaw, '/');
    }
}

$currentUrl = trim((string) ($_GET['url'] ?? ''), '/');
$urlParts = $currentUrl === '' ? [] : explode('/', $currentUrl);
$currentController = strtolower((string) ($urlParts[0] ?? 'pages'));
$currentMethod = strtolower((string) ($urlParts[1] ?? 'index'));
$isHomePage = empty($urlParts) || ($currentController === 'pages' && $currentMethod === 'index');
// Always point the "Chúng tôi" navbar item to the dedicated About page.
$aboutAnchorHref = URLROOT . '/about';

$hAttr = static function ($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
};
$urlSlugRaw = isset($_GET['url']) ? trim((string) $_GET['url'], '/') : '';
$canonicalAutomatic = rtrim(URLROOT, '/') . ($urlSlugRaw === '' ? '/' : '/' . $urlSlugRaw);
$canonicalUrl = trim((string) ($data['canonical_url'] ?? ''));
if ($canonicalUrl === '') {
    $canonicalUrl = $canonicalAutomatic;
}

$seoTruncate = static function ($text, $maxLen) {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $maxLen) {
            return mb_substr($text, 0, max(0, $maxLen - 1), 'UTF-8') . '…';
        }
        return $text;
    }
    return strlen($text) > $maxLen ? substr($text, 0, $maxLen - 1) . '...' : $text;
};

$defaultMetaDescription = trim((string) ($publicSettings['site_about_snippet'] ?? ''));
if ($defaultMetaDescription === '') {
    $defaultMetaDescription = SITENAME;
}
$rawDescription = trim((string) ($data['description'] ?? ''));
if ($rawDescription === '') {
    $rawDescription = $defaultMetaDescription;
}
$metaDescription = $seoTruncate($rawDescription, 160);

if (!empty($data['meta_title'])) {
    $documentTitle = trim((string) $data['meta_title']);
} elseif (!empty($data['title'])) {
    $documentTitle = trim((string) $data['title']) . ' - ' . SITENAME;
} else {
    $documentTitle = SITENAME;
}

$ogTitle = trim((string) ($data['og_title'] ?? ''));
if ($ogTitle === '') {
    $ogTitle = $documentTitle;
}
$ogDescRaw = trim((string) ($data['og_description'] ?? ''));
$ogDescription = $ogDescRaw !== '' ? $seoTruncate($ogDescRaw, 200) : $metaDescription;

$ogType = trim((string) ($data['og_type'] ?? 'website'));
if ($ogType === '') {
    $ogType = 'website';
}

$ogImage = trim((string) ($data['og_image'] ?? ''));
if ($ogImage !== '' && strpos($ogImage, 'http://') !== 0 && strpos($ogImage, 'https://') !== 0) {
    $ogImage = rtrim(URLROOT, '/') . '/' . ltrim($ogImage, '/');
} elseif ($ogImage === '' && $siteLogoImageUrl !== '') {
    $ogImage = $siteLogoImageUrl;
}

$twitterCard = trim((string) ($data['twitter_card'] ?? ''));
if ($twitterCard === '') {
    $twitterCard = $ogImage !== '' ? 'summary_large_image' : 'summary';
}

$metaKeywords = trim((string) ($data['meta_keywords'] ?? ''));

$navItems = [
    [
        'label' => 'Trang chủ',
        'href' => URLROOT . '/',
        'is_active' => $isHomePage,
        'nav_spy' => null,
    ],
    [
        'label' => 'Sản phẩm',
        'href' => URLROOT . '/products',
        'is_active' => $currentController === 'products',
        'nav_spy' => null,
    ],
    [
        'label' => 'Tin tức',
        'href' => URLROOT . '/posts',
        'is_active' => $currentController === 'posts',
        'nav_spy' => null,
    ],
    [
        'label' => 'Chúng tôi',
        'href' => $aboutAnchorHref,
        'is_active' => ($currentController === 'about') || ($currentController === 'pages' && $currentMethod === 'about'),
        'nav_spy' => null,
    ],
    [
        'label' => 'Liên hệ',
        'href' => URLROOT . '/contact',
        'is_active' => $currentController === 'pages' && $currentMethod === 'contact',
        'nav_spy' => null,
    ]
];

$clientHoverMinPath = dirname(APPROOT) . '/public/admin_assets/css/hover-min.css';
$clientHoverMinVer = is_readable($clientHoverMinPath) ? (string) filemtime($clientHoverMinPath) : '1';
?>
<!DOCTYPE html>
<html lang="vi" class="bg-black">
<head>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/themify-icons.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $hAttr($metaDescription); ?>">
    <?php if ($metaKeywords !== '') : ?>
    <meta name="keywords" content="<?php echo $hAttr($metaKeywords); ?>">
    <?php endif; ?>
    <title><?php echo $hAttr($documentTitle); ?></title>
    <link rel="canonical" href="<?php echo $hAttr($canonicalUrl); ?>">
    <meta property="og:title" content="<?php echo $hAttr($ogTitle); ?>">
    <meta property="og:description" content="<?php echo $hAttr($ogDescription); ?>">
    <meta property="og:url" content="<?php echo $hAttr($canonicalUrl); ?>">
    <meta property="og:type" content="<?php echo $hAttr($ogType); ?>">
    <?php if ($ogImage !== '') : ?>
    <meta property="og:image" content="<?php echo $hAttr($ogImage); ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="<?php echo $hAttr(SITENAME); ?>">
    <meta name="twitter:card" content="<?php echo $hAttr($twitterCard); ?>">
    <meta name="twitter:title" content="<?php echo $hAttr($ogTitle); ?>">
    <meta name="twitter:description" content="<?php echo $hAttr($ogDescription); ?>">
    <?php if ($ogImage !== '') : ?>
    <meta name="twitter:image" content="<?php echo $hAttr($ogImage); ?>">
    <?php endif; ?>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#06b6d4',
                        secondary: '#9333ea',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css?v=<?php echo filemtime(dirname(APPROOT) . '/public/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/admin_assets/css/hover-min.css?v=<?php echo htmlspecialchars($clientHoverMinVer, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" crossorigin="anonymous">
    <!-- GSAP + ScrollTrigger + Medium-Zoom + Prism.js (used by news/post pages) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/medium-zoom@1.0.8/dist/style.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
       
        body { font-family: 'Inter', sans-serif; }

        /* ── Sticky header ─────────────────────────────── */
        .site-header.header-home {
            border-bottom: 1px solid transparent;
            background: transparent;
            transition: background 0.3s ease, border-color 0.3s ease,
                        backdrop-filter 0.3s ease, box-shadow 0.3s ease;
        }
        .site-header.header-inner {
             background: #030712;
             border-bottom: 1px solid transparent;
             transition: background 0.3s ease, border-color 0.3s ease, backdrop-filter 0.3s ease, box-shadow 0.3s ease;
        }
        .site-header.is-scrolled {
            background: rgba(2, 8, 23, 0.82);
            border-bottom-color: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.55);
        }

        /* ── Nav links ─────────────────────────────────── */
        .site-nav-link {
            position: relative;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .site-nav-link::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -5px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, #22d3ee, #a855f7, #ec4899);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.22s ease;
        }
        .site-nav-link:hover {
            color: #e2e8f0;
            text-shadow: 0 0 12px rgba(34, 211, 238, 0.5);
        }
        .site-nav-link.is-active { color: #22d3ee; }
        .site-nav-link.is-active::after,
        .site-nav-link:hover::after { transform: scaleX(1); }

        /* ── Ghost CTA button ──────────────────────────── */
        .cta-ghost-btn {
            position: relative;
            isolation: isolate;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            color: #f1f5f9;
            font-size: 0.85rem;
            font-weight: 600;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .cta-ghost-btn::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(120deg,
                rgba(255,255,255,0.55),
                rgba(34,211,238,0.45),
                rgba(168,85,247,0.5));
            z-index: -1;
        }
        .cta-ghost-btn::after {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background: #020817;
            z-index: -1;
        }
        .cta-ghost-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 22px rgba(34, 211, 238, 0.4);
        }

        /* ── Mobile menu ───────────────────────────────── */
        body.site-mobile-menu-open {
            overflow: hidden;
        }
        .site-mobile-menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .site-mobile-menu-toggle:hover {
            background: rgba(34, 211, 238, 0.12);
            border-color: rgba(34, 211, 238, 0.35);
            color: #fff;
        }
        .site-mobile-menu-backdrop {
            position: fixed;
            inset: 0;
            top: 5rem;
            z-index: 40;
            background: rgba(0, 0, 0, 0.55);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }
        .site-mobile-menu-backdrop.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .site-mobile-menu-panel {
            position: fixed;
            left: 0;
            right: 0;
            top: 5rem;
            z-index: 45;
            max-height: min(70vh, calc(100dvh - 5rem));
            overflow-y: auto;
            background: rgba(2, 8, 23, 0.97);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
            transform: translateY(-0.5rem);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: transform 0.22s ease, opacity 0.22s ease, visibility 0.22s ease;
        }
        .site-mobile-menu-panel.is-open {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .site-mobile-menu-icon-close {
            display: none;
        }
        .site-mobile-menu-toggle.is-open .site-mobile-menu-icon-bars {
            display: none;
        }
        .site-mobile-menu-toggle.is-open .site-mobile-menu-icon-close {
            display: inline-block;
        }
        .site-mobile-nav-link {
            display: block;
            padding: 0.85rem 0;
            font-size: 1rem;
            font-weight: 500;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .site-mobile-nav-link:last-child {
            border-bottom: none;
        }
        .site-mobile-nav-link.is-active {
            color: #22d3ee;
        }
    </style>
    <script>
        window.URLROOT = '<?php echo URLROOT; ?>';
    </script>
</head>
<body class="site-client-body text-white antialiased flex flex-col min-h-screen">
    
    <!-- Navbar -->
    <nav id="site-header" class="site-header fixed top-0 inset-x-0 z-50 <?php echo $isHomePage ? 'header-home' : 'header-inner'; ?>">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-20 grid grid-cols-[auto_1fr_auto] items-center gap-6">
                <div class="flex items-center">
                    <a href="<?php echo URLROOT; ?>" class="site-header-brand flex-shrink-0 flex items-center gap-3">
                        <?php if ($siteLogoImageUrl !== ''): ?>
                            <img src="<?php echo htmlspecialchars($siteLogoImageUrl); ?>" alt="Logo thương hiệu" class="hvr-glow site-brand-logo-glow w-10 h-10 rounded-lg object-cover shadow-lg shadow-cyan-500/20">
                        <?php else: ?>
                            <div class="hvr-glow site-brand-logo-fallback w-10 h-10 bg-gradient-to-br from-cyan-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg shadow-cyan-500/20">
                                <i class="fa-solid fa-server text-white text-xl"></i>
                            </div>
                        <?php endif; ?>
                        <span class="brand-sitename font-bold text-2xl tracking-tighter bg-gradient-to-r from-white via-violet-500 to-blue-600 bg-clip-text text-transparent inline-block">
                            <?php echo htmlspecialchars($siteLogoText); ?>
                        </span>
                    </a>
                </div>

                <div class="hidden md:flex items-center justify-center">
                    <div class="site-nav-links flex items-center justify-center gap-8">
                        <?php foreach ($navItems as $item): ?>
                            <?php
                            $navSpy = isset($item['nav_spy']) && $item['nav_spy'] !== null && $item['nav_spy'] !== ''
                                ? (string) $item['nav_spy']
                                : '';
                            ?>
                            <a href="<?php echo htmlspecialchars($item['href']); ?>"
                               class="site-nav-link <?php echo $item['is_active'] ? 'is-active' : ''; ?>"
                               <?php if ($navSpy !== ''): ?>data-nav-spy="<?php echo htmlspecialchars($navSpy, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 sm:gap-3 md:gap-6">
                    <div class="flex items-center gap-2 md:hidden">
                        <a href="<?php echo URLROOT; ?>/cart" class="text-gray-300 hover:text-cyan-400 relative inline-flex p-2.5 rounded-xl border border-white/10 bg-white/[0.06] transition-colors" aria-label="Giỏ hàng">
                            <i class="fa-solid fa-cart-shopping text-lg" aria-hidden="true"></i>
                            <span class="absolute top-1 right-1 bg-gradient-to-r from-pink-500 to-rose-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center border-2 border-gray-950">0</span>
                        </a>
                        <button type="button"
                                id="site-mobile-menu-toggle"
                                class="site-mobile-menu-toggle"
                                aria-expanded="false"
                                aria-controls="site-mobile-menu"
                                aria-label="Mở menu điều hướng">
                            <i class="fa-solid fa-bars text-lg site-mobile-menu-icon-bars" aria-hidden="true"></i>
                            <i class="fa-solid fa-xmark text-lg site-mobile-menu-icon-close" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="hidden md:flex items-center gap-6">
                    <?php if(isset($_SESSION['user_id'])) : ?>
                        <div class="flex items-center gap-4">
                            <a href="<?php echo URLROOT; ?>/users/profile" class="text-sm font-medium text-gray-300 hover:text-cyan-400 transition-colors flex items-center gap-2">
                                <?php if ($sessionAvatarUrl !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($sessionAvatarUrl); ?>" alt="Avatar người dùng" class="w-8 h-8 rounded-full object-cover border border-gray-700">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center border border-gray-700">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                <?php endif; ?>
                                <?php echo htmlspecialchars((string) ($_SESSION['user_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <a href="<?php echo URLROOT; ?>/users/dashboard" class="text-sm font-medium text-gray-300 hover:text-cyan-400 transition-colors">
                                <i class="fa-solid fa-gauge mr-1"></i> Dashboard
                            </a>
                            
                            <a href="<?php echo URLROOT; ?>/users/orders" class="text-sm font-medium text-gray-300 hover:text-cyan-400 transition-colors">
                                <i class="fa-solid fa-clock-rotate-left mr-1"></i> Đơn hàng
                            </a>

                            <?php if($_SESSION['user_role'] == 'admin') : ?>
                                <a href="<?php echo URLROOT; ?>/admin" class="text-sm font-medium text-purple-400 hover:text-purple-300">
                                    <i class="fa-solid fa-shield mr-1"></i> Admin
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo URLROOT; ?>/users/logout" class="text-gray-400 hover:text-white transition-colors" title="Đăng xuất">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        </div>
                    <?php else : ?>
                        <a href="<?php echo URLROOT; ?>/users/login" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Đăng nhập</a>
                        <a href="<?php echo URLROOT; ?>/users/register" class="hvr-ripple-in cta-register-ripple inline-flex items-center rounded-full">
                            <span class="cta-ghost-btn">
                                <i class="fa-solid fa-rocket"></i>
                                <span>Tham gia ngay</span>
                            </span>
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo URLROOT; ?>/cart" class="text-gray-400 hover:text-cyan-400 relative p-2 transition-colors">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <span class="absolute top-0 right-0 bg-gradient-to-r from-pink-500 to-rose-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center border-2 border-gray-950">0</span>
                    </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="site-mobile-menu-backdrop" class="site-mobile-menu-backdrop md:hidden" aria-hidden="true"></div>
        <div id="site-mobile-menu"
             class="site-mobile-menu-panel md:hidden"
             role="region"
             aria-label="Menu điều hướng"
             aria-hidden="true">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="py-2">
                    <?php foreach ($navItems as $item): ?>
                        <?php
                        $navSpy = isset($item['nav_spy']) && $item['nav_spy'] !== null && $item['nav_spy'] !== ''
                            ? (string) $item['nav_spy']
                            : '';
                        ?>
                        <a href="<?php echo htmlspecialchars($item['href']); ?>"
                           class="site-mobile-nav-link <?php echo $item['is_active'] ? 'is-active' : ''; ?>"
                           <?php if ($navSpy !== ''): ?>data-nav-spy="<?php echo htmlspecialchars($navSpy, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="border-t border-white/10 py-4 space-y-3">
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <a href="<?php echo URLROOT; ?>/users/profile" class="flex items-center gap-3 text-gray-200 hover:text-cyan-400 py-1">
                            <?php if ($sessionAvatarUrl !== ''): ?>
                                <img src="<?php echo htmlspecialchars($sessionAvatarUrl); ?>" alt="" class="w-10 h-10 rounded-full object-cover border border-gray-700">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center">
                                    <i class="fa-solid fa-user text-sm text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <span class="font-medium"><?php echo htmlspecialchars((string) ($_SESSION['user_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                        
                        <a href="<?php echo URLROOT; ?>/users/dashboard" class="block text-gray-300 hover:text-cyan-400 py-1">
                            <i class="fa-solid fa-gauge mr-2"></i>Bảng điều khiển
                        </a>
                        <a href="<?php echo URLROOT; ?>/users/orders" class="block text-gray-300 hover:text-cyan-400 py-1">
                            <i class="fa-solid fa-clock-rotate-left mr-2"></i>Đơn hàng
                        </a>

                        <?php if ($_SESSION['user_role'] == 'admin') : ?>
                            <a href="<?php echo URLROOT; ?>/admin" class="block text-purple-400 hover:text-purple-300 py-1">
                                <i class="fa-solid fa-shield mr-2"></i>Admin
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo URLROOT; ?>/users/logout" class="inline-flex items-center gap-2 text-gray-400 hover:text-white py-1">
                            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                        </a>
                        
                    <?php else : ?>
                        <a href="<?php echo URLROOT; ?>/users/login" class="block w-full text-center py-3 rounded-xl border border-white/15 text-gray-200 font-medium hover:bg-white/5">Đăng nhập</a>
                        <a href="<?php echo URLROOT; ?>/users/register" class="block w-full text-center py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-semibold hover:opacity-95">Tham gia ngay</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow <?php echo $isHomePage ? '' : 'pt-20'; ?>">