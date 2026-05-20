<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<style>
/* Inlined about page visual enhancements (previously public/css/about-enhancements.css) */
/* Hero wrapper 3D + subtle lift */
.about-hero-box{
    perspective:1200px;
    transform-style:preserve-3d;
    transition:transform .45s cubic-bezier(.2,.9,.2,1), box-shadow .45s ease;
}
.about-hero-box:hover{
    transform:translateY(-8px);
    box-shadow:0 30px 70px rgba(2,6,23,0.6);
}

/* panels respond to pointer via CSS vars set by JS */
.about-hero-box .hero-panel{
    transform: translateZ(var(--tz,0px)) rotateY(var(--ry,0deg)) rotateX(var(--rx,0deg)) translate(var(--tx,0px), var(--ty,0px));
    transition:transform .28s cubic-bezier(.2,.9,.2,1), filter .28s ease;
    will-change:transform;
}

/* media image gentle scale + hover */
.hero-media img{
    transition:transform .7s cubic-bezier(.2,.9,.2,1), box-shadow .5s ease, filter .5s ease;
    transform-origin:center center;
}
.about-hero-box:hover .hero-media img{
    transform:scale(1.025) rotate(-0.25deg);
    filter:brightness(1.02) saturate(1.06);
}
.hero-media img:hover{
    transform:scale(1.06) rotate(-0.5deg);
    box-shadow:0 18px 60px rgba(2,6,23,0.5);
}

/* animated gradient blobs (extra depth) */
.about-vibe::after{
    content:'';position:absolute;inset:auto auto 8% 4%;width:380px;height:380px;border-radius:50%;filter:blur(60px);opacity:.18;pointer-events:none;background:linear-gradient(135deg,#06b6d4,#9333ea);
    transform:translate3d(0,0,0);mix-blend-mode:screen;animation:blobFloat 10s ease-in-out infinite;
}
@keyframes blobFloat{0%{transform:translateY(0) scale(1)}50%{transform:translateY(-18px) scale(1.04)}100%{transform:translateY(0) scale(1)}}

/* Section dots style */
.sections-dots button{width:12px;height:12px;border-radius:50%;border:0;padding:0;background:linear-gradient(135deg,#06b6d4,#9333ea);box-shadow:0 8px 18px rgba(6,182,212,0.12);transition:transform .28s ease, opacity .28s ease}
.sections-dots button:hover{transform:scale(1.28);opacity:1}
.sections-dots button[aria-current="true"]{transform:scale(1.2);box-shadow:0 12px 30px rgba(147,51,234,0.14)}

/* Stat card shimmer on hover */
.stat-card{position:relative;overflow:hidden}
.stat-card::after{content:'';position:absolute;left:-40%;top:0;width:40%;height:100%;background:linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.12), rgba(255,255,255,0.02));transform:skewX(-18deg) translateX(-100%);transition:transform .9s cubic-bezier(.2,.8,.2,1)}
.stat-card:hover::after{transform:skewX(-18deg) translateX(220%)}

/* Info rows hover lift */
.info-row{transition:transform .36s cubic-bezier(.2,.9,.2,1), box-shadow .36s}
.info-row:hover{transform:translateY(-8px);box-shadow:0 24px 60px rgba(2,6,23,0.45)}
.info-row .info-image img{transition:transform .6s ease, filter .6s ease}
.info-row:hover .info-image img{transform:translateY(-6px) scale(1.03);filter:brightness(1.03)}

/* subtle entrance animations if JS toggles .floating */
.hero-media .floating{animation:floatSlow 6.6s ease-in-out infinite}
@keyframes floatSlow{0%{transform:translateY(0) scale(1)}50%{transform:translateY(-8px) scale(1.01)}100%{transform:translateY(0) scale(1)}}

/* title char hover intensify */
.about-title-char{transition:transform .28s cubic-bezier(.2,.9,.2,1), color .3s}
.about-title-char:hover{transform:translateY(-6px) scale(1.14);color:#7ee0ff}

/* Lightbox small polish */
#lightbox.show{display:flex;opacity:1;transition:opacity .28s ease}
#lightbox{transition:opacity .28s ease}

/* Swiper slide entry polish (if Swiper used) */
.swiper-slide .slide-inner{opacity:0;transform:translateY(14px) scale(.996);transition:opacity .48s cubic-bezier(.2,.9,.2,1), transform .48s cubic-bezier(.2,.9,.2,1)}
.swiper-slide-active .slide-inner, .swiper-slide-active .enter{opacity:1;transform:translateY(0) scale(1)}

/* keep everything responsive and non-invasive */
@media (prefers-reduced-motion: reduce){
    .about-hero-box, .about-vibe::after, .stat-card::after, .hero-media .floating{animation:none !important;transition:none !important}
}
</style>

<script>
/* Inlined about page JS enhancements (previously public/js/about-enhancements.js)
     - pointer parallax for hero
     - floating effect for hero media
     - autoplay for sections carousel (non-invasive)
     - Swiper appearance hookup when Swiper is present
*/
(function(){
    'use strict';
    var heroWrapper = document.querySelector('.about-hero-box');
    if(heroWrapper){
        var maxTilt = 8; // degrees
        var maxTranslate = 12; // px
        var tz = 12; // translateZ on hover
        var pointerActive = false;

        function setVars(rx, ry, tx, ty, tzv){
            heroWrapper.style.setProperty('--rx', rx + 'deg');
            heroWrapper.style.setProperty('--ry', ry + 'deg');
            heroWrapper.style.setProperty('--tx', tx + 'px');
            heroWrapper.style.setProperty('--ty', ty + 'px');
            heroWrapper.style.setProperty('--tz', tzv + 'px');
        }

        function onMove(e){
            var r = heroWrapper.getBoundingClientRect();
            var cx = r.left + r.width/2;
            var cy = r.top + r.height/2;
            var clientX = e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX) || cx;
            var clientY = e.clientY || (e.touches && e.touches[0] && e.touches[0].clientY) || cy;
            var dx = (clientX - cx) / (r.width/2);
            var dy = (clientY - cy) / (r.height/2);
            dx = Math.max(-1, Math.min(1, dx));
            dy = Math.max(-1, Math.min(1, dy));
            var ry = dx * maxTilt * -1; // horizontal movement -> rotateY
            var rx = dy * maxTilt; // vertical movement -> rotateX
            var tx = dx * maxTranslate * -1;
            var ty = dy * (maxTranslate/1.6) * -1;
            setVars(rx, ry, tx, ty, tz);
        }

        function onEnter(){ pointerActive = true; heroWrapper.classList.add('hovering'); setVars(0,0,0,0,tz); }
        function onLeave(){ pointerActive = false; heroWrapper.classList.remove('hovering'); setVars(0,0,0,0,0); }

        heroWrapper.addEventListener('pointermove', onMove);
        heroWrapper.addEventListener('pointerenter', onEnter);
        heroWrapper.addEventListener('pointerleave', onLeave);
        heroWrapper.addEventListener('touchstart', onEnter);
        heroWrapper.addEventListener('touchend', onLeave);

        // apply a small floating class to the hero media image (if present)
        var heroImg = heroWrapper.querySelector('.hero-media img');
        if(heroImg) heroImg.classList.add('floating');
    }

    // Sections carousel autoplay (non-invasive: uses existing next button)
    (function(){
        var sections = document.getElementById('sections-carousel');
        if(!sections) return;
        var next = document.getElementById('sections-next');
        var prev = document.getElementById('sections-prev');
        var autoplay = true;
        var delay = 5600; // ms
        var timer = null;
        function start(){ if(!autoplay || !next) return; stop(); timer = setInterval(function(){ try{ next.click(); }catch(e){} }, delay); }
        function stop(){ if(timer) { clearInterval(timer); timer = null; } }
        sections.addEventListener('mouseenter', stop); sections.addEventListener('mouseleave', start);
        sections.addEventListener('touchstart', stop); sections.addEventListener('touchend', start);
        start();
    })();

    // Enhance keyboard / focus visibility for section dots (ARIA current)
    (function(){
        var dotsWrap = document.getElementById('sections-dots');
        if(!dotsWrap) return;
        dotsWrap.querySelectorAll('button').forEach(function(b){
            b.addEventListener('click', function(){
                dotsWrap.querySelectorAll('button').forEach(function(x){ x.removeAttribute('aria-current'); });
                b.setAttribute('aria-current','true');
            });
        });
    })();

    // Progressive reveal for title chars (keyboard accessible)
    (function(){
        var title = document.getElementById('about-title');
        if(!title) return;
        title.addEventListener('focus', function(){ title.querySelectorAll('.about-title-char').forEach(function(ch,i){ setTimeout(function(){ ch.style.transform = 'translateY(-8px) scale(1.12)'; }, i*40); setTimeout(function(){ title.querySelectorAll('.about-title-char').forEach(function(c){ c.style.transform = ''; }); }, 1200); }); }, true);
    })();

    // Swiper appearance hookup: if Swiper is loaded and there are .swiper-container elements, initialize with entry animations
    (function(){
        if(typeof window.Swiper !== 'function') return;
        var nodes = document.querySelectorAll('.swiper-container, .swiper');
        if(!nodes || nodes.length === 0) return;
        nodes.forEach(function(node, i){
            try{
                var s = new window.Swiper(node, {
                    effect: 'fade',
                    fadeEffect: { crossFade: true },
                    speed: 700,
                    loop: false,
                    autoplay: { delay: 5200, disableOnInteraction: true },
                    navigation: { nextEl: node.querySelector('.swiper-button-next'), prevEl: node.querySelector('.swiper-button-prev') },
                    pagination: { el: node.querySelector('.swiper-pagination'), clickable: true },
                    on: {
                        init: function(){
                            // ensure active slide contents animate in
                            node.querySelectorAll('.swiper-slide').forEach(function(sl){ sl.querySelectorAll('.slide-inner, .enter').forEach(function(el){ el.style.opacity = 0; el.style.transform = 'translateY(14px) scale(.996)'; }); });
                            var active = node.querySelector('.swiper-slide-active');
                            if(active) active.querySelectorAll('.slide-inner, .enter').forEach(function(el){ setTimeout(function(){ el.style.opacity = 1; el.style.transform = 'translateY(0) scale(1)'; }, 60); });
                        },
                        slideChangeTransitionStart: function(){
                            node.querySelectorAll('.swiper-slide').forEach(function(sl){ sl.querySelectorAll('.slide-inner, .enter').forEach(function(el){ el.style.opacity = 0; el.style.transform = 'translateY(14px) scale(.996)'; }); });
                        },
                        slideChangeTransitionEnd: function(){
                            var active = node.querySelector('.swiper-slide-active');
                            if(active) active.querySelectorAll('.slide-inner, .enter').forEach(function(el,i){ setTimeout(function(){ el.style.opacity = 1; el.style.transform = 'translateY(0) scale(1)'; }, i*60 + 40); });
                        }
                    }
                });
            }catch(e){console.warn('swiper init error', e);}    
        });
    })();

})();
</script>

<style>
/* Small-screen responsive overrides for About page */
@media (max-width: 992px) {
    .stats-row{flex-wrap:wrap !important;}
    .stat-card{flex:1 1 260px !important;min-width:160px !important;margin-bottom:12px;}
    .info-row{flex-direction:column !important;align-items:flex-start !important;gap:12px !important;}
    .info-row .info-image{width:100% !important;}
    .info-row img{width:100% !important;max-width:100% !important;height:auto !important;}
    .section-content{max-height:none !important;overflow:visible !important;}
    .about-hero-inner{padding:0 1rem !important;}
    .hero-outer{padding:2.4rem 0 !important;}
    .panel-inner{padding:18px !important;}
    .hero-media img{height:auto !important;max-height:420px !important;border-radius:12px !important;}
}
@media (max-width:480px){
    .stat-card{flex:1 1 100% !important;min-width:0 !important;}
    .about-title{font-size:1.4rem !important;}
    .about-hero-inner{padding:0 .75rem !important;}
}
</style>

<section id="about-section" class="relative bg-gradient-to-b from-gray-900 via-gray-950 to-black text-white overflow-hidden" style="opacity:0;transition:opacity .35s ease;padding-top:5.6rem">
    <?php
        // Build background style: prefer explicit background field if present, otherwise fallback to gradient
        $bgStyle = '';
        if(!empty($data['about']->background)){
            $imgUrl = URLROOT . '/uploads/' . htmlspecialchars($data['about']->background);
            $bgStyle = "background-image: url('" . $imgUrl . "'); background-size:cover; background-position:center; opacity:.32; mix-blend-mode:screen; animation: vibeMoveImg 18s linear infinite;";
        } elseif(!empty($data['about']->image)){
            // fallback: legacy image
            $imgUrl = URLROOT . '/uploads/' . htmlspecialchars($data['about']->image);
            $bgStyle = "background-image: url('" . $imgUrl . "'); background-size:cover; background-position:center; opacity:.32; mix-blend-mode:screen; animation: vibeMoveImg 18s linear infinite;";
        } else {
            $bgStyle = "background:radial-gradient(closest-side at 20% 20%, #06b6d4, transparent 20%), radial-gradient(closest-side at 80% 80%, #9333ea, transparent 20%);";
        }
    ?>
    <style>
        .about-vibe{position:absolute;inset:0;pointer-events:none;overflow:hidden}
        .about-vibe::before{content:'';position:absolute;inset:0;pointer-events:none;background:radial-gradient(closest-side at 20% 20%, rgba(6,182,212,0.18), transparent 20%), radial-gradient(closest-side at 80% 80%, rgba(147,51,234,0.18), transparent 20%);mix-blend-mode:screen;opacity:0.55;animation:vibeGradient 14s ease-in-out infinite}
        @keyframes vibeMoveImg{0%{transform:scale(1) translateY(0)}50%{transform:scale(1.02) translateY(-6px)}100%{transform:scale(1) translateY(0)}}
        @keyframes vibeGradient{0%{background-position:0% 50% ,100% 50%}50%{background-position:100% 50% ,0% 50%}100%{background-position:0% 50% ,100% 50%}}
    </style>
    <div class="about-vibe" aria-hidden="true" style="<?php echo $bgStyle; ?>"></div>
    <style>
            /* Hero full-width layout: two equal panels inside a centered inner wrapper */
            .about-hero-inner{max-width:1200px;margin:0 auto;padding:0 1.5rem}
            .about-hero-box{display:flex;flex-direction:column;gap:24px}
            @media(min-width:992px){
                .about-hero-box{flex-direction:row;height:650px}
            }
            .about-hero-box > .hero-panel{flex:1;display:flex;align-items:stretch;min-height:300px}
            .hero-panel .panel-inner{padding:28px;width:100%;display:flex;flex-direction:column;justify-content:center;overflow:hidden}
            .hero-media{width:100%;height:100%;display:flex;align-items:center;justify-content:center}
            .hero-media img{width:100%;height:100%;object-fit:cover;border-radius:20px;display:block}
            .hero-outer{width:100%;padding:4rem 0}
            /* Section carousel styles (text-only slides to match hero height) */
            .section-slide{display:none;opacity:0;transform:translateX(18px);transition:opacity .45s ease, transform .45s ease;height:100%}
            .section-slide.show{display:block;opacity:1;transform:translateX(0)}
            .section-card{display:flex;flex-direction:column;gap:18px;align-items:stretch;width:100%;height:100%;box-sizing:border-box;padding:28px;border-radius:14px;background:transparent}
            .section-card .left{display:none}
            .section-card .right{flex:1;padding:24px 22px;display:flex;flex-direction:column;justify-content:center;min-width:0}
            .section-title{font-size:1.6rem;font-weight:800;margin-bottom:12px}
            .section-content{color:#cfe8ff;font-size:1rem;line-height:1.7;white-space:normal;word-break:break-word;hyphens:auto;max-width:100%;width:100%;overflow:auto;max-height:calc(100% - 40px)}
            .section-content{ -ms-overflow-style: none; scrollbar-width: none; }
            .section-content::-webkit-scrollbar{ display:none; width:0; height:0 }
            .sections-nav{position:absolute;left:8px;top:50%;transform:translateY(-50%);z-index:40}
            .sections-nav .btn, #sections-next{background:linear-gradient(135deg,#111827,#0b1220);color:#fff;border-radius:999px;padding:10px 14px;border:0;box-shadow:0 6px 18px rgba(2,6,23,0.6);font-size:22px}
            .sections-dots{position:absolute;left:50%;transform:translateX(-50%);bottom:-8px;display:flex;gap:8px;z-index:40}
            .sections-dots button{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.35);border:0;padding:0}
            @media (max-width:900px){ .section-card{flex-direction:column} .section-card .left{display:none} .sections-nav .btn, #sections-next{padding:8px 10px;font-size:18px} }
            .section-slide.no-image .left{display:none}
            .section-card .right, .section-card .right *{box-sizing:border-box}
            .section-content p{margin:0 0 0.8rem}
            .section-card, .section-card .left, .section-card .right{min-height:unset}
        </style>
    

    <div class="about-head" data-aos="fade-down" data-aos-delay="120" style="max-width:1200px;margin:0 auto 18px;padding:0 1.5rem;text-align:center">
        <style>
            /* Title wave characters */
            .about-title-char{display:inline-block;transform-origin:left center;will-change:transform}
            @keyframes aboutTitleWave{0%,100%{transform:scale(1)}40%{transform:scale(1.18) translateY(-6px)}}

            /* Title loading bar */
            .about-title-loader-wrap{width:60%;margin:.6rem auto 1.25rem}
            .about-title-loader-track{height:6px;background:linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));border-radius:999px;overflow:hidden;position:relative}
            .about-title-loader-bar{width:28%;height:100%;background:linear-gradient(90deg,#06b6d4,#9333ea);border-radius:999px;transform:translateX(-120%);animation:loaderMove 1.6s linear infinite}
            @keyframes loaderMove{0%{transform:translateX(-120%)}100%{transform:translateX(220%)}}

            /* Stats & cards animations */
            .stats-row{display:flex;gap:18px;align-items:stretch;flex-wrap:wrap}
            .stat-card{flex:1 1 260px;min-width:180px;display:flex;align-items:center;gap:18px;padding:22px;border-radius:16px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.06));box-shadow:0 14px 40px rgba(2,6,23,0.55);transition:transform .32s ease, box-shadow .32s;opacity:0;transform:translateY(8px) scale(.995);animation:statEntrance .7s forwards}
            .stats-row .stat-card:nth-child(1){animation-delay:.06s}.stats-row .stat-card:nth-child(2){animation-delay:.12s}.stats-row .stat-card:nth-child(3){animation-delay:.18s}
            .stat-card:hover{transform:translateY(-6px) scale(1.03);box-shadow:0 20px 60px rgba(2,6,23,0.6)}
            .stat-icon{width:76px;height:76px;border-radius:14px;background:linear-gradient(135deg,#06b6d4,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;flex:0 0 76px;background-size:200% 200%;animation:iconShift 6s linear infinite}
            @keyframes iconShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
            @keyframes statEntrance{to{opacity:1;transform:translateY(0) scale(1)}}
        </style>
        <?php if(!empty($data['about']->title)): ?><h1 id="about-title" class="text-4xl md:text-5xl about-title font-extrabold mb-4 tracking-tight"><?php echo htmlspecialchars($data['about']->title); ?></h1><?php endif; ?>
        <div class="about-title-loader-wrap"><div class="about-title-loader-track"><div class="about-title-loader-bar"></div></div></div>
        <?php if(!empty($data['about']->subtitle)): ?><p class="text-cyan-400 font-medium mb-6"><?php echo htmlspecialchars($data['about']->subtitle); ?></p><?php endif; ?>
        <script>
            (function(){
                var h = document.getElementById('about-title');
                if(!h) return;
                var txt = h.textContent || '';
                txt = txt.trim();
                h.textContent = '';
                for(var i=0;i<txt.length;i++){
                    var ch = txt[i];
                    var sp = document.createElement('span');
                    sp.className = 'about-title-char';
                    sp.textContent = ch;
                    sp.style.animation = 'aboutTitleWave 1.6s ease-in-out infinite';
                    sp.style.animationDelay = (i * 0.06) + 's';
                    if(ch === ' ') sp.style.display = 'inline-block';
                    h.appendChild(sp);
                }
            })();
        </script>
        <style>
            /* Fallback entrance animation in case AOS isn't available yet */
            .about-head{opacity:0;transform:translateY(-8px);animation:aboutHeadIn .64s ease-out forwards;animation-delay:.12s}
            @keyframes aboutHeadIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
        </style>
    </div>

    <div class="hero-outer">
        <div class="about-hero-inner">
            <div class="about-hero-box bg-gradient-to-tr from-gray-800/30 to-white/3 rounded-3xl p-0 shadow-2xl overflow-hidden">
                <div class="hero-panel" data-aos="fade-right">
                    <div class="panel-inner">
                        <?php
                            // Prefer explicit sections if present, otherwise split content into parts
                            $sections = [];
                            if(isset($data['about']->sections) && $data['about']->sections){
                                $sections = json_decode($data['about']->sections, true) ?: [];
                            }
                            if(empty($sections) && !empty($data['about']->content)){
                                $raw = $data['about']->content;
                                $parts = array();
                                if(stripos($raw,'</p>') !== false){
                                    $tmp = preg_split('/<\/p>\s*/i', $raw);
                                    foreach($tmp as $t){ $t = trim($t); if($t !== '') $parts[] = ['title' => '', 'content' => $t]; }
                                } else {
                                    $tmp = preg_split('/\r?\n\s*\r?\n/', strip_tags($raw));
                                    foreach($tmp as $t){ $t = trim($t); if($t !== '') $parts[] = ['title' => '', 'content' => $t]; }
                                }
                                $sections = $parts;
                            }

                            if(!empty($sections)):
                        ?>
                            <div class="mt-6 relative">
                                <!-- reuse existing section carousel markup (keeps internal styles) -->
                                <div id="sections-carousel" class="w-full max-w-none rounded-lg p-2 bg-transparent">
                                            <?php foreach($sections as $si => $sec): ?>
                                                <?php $noImageClass = (!empty($sec['content']) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $sec['content'])) ? 'has-image' : 'no-image'; ?>
                                                <div class="section-slide <?php echo $noImageClass; ?>" data-idx="<?php echo $si; ?>">
                                                    <div class="section-card bg-gradient-to-tr from-gray-800/30 to-white/3 rounded-2xl p-3">
                                                        <div class="right">
                                                            <?php if(!empty($sec['title'])): ?><div class="section-title"><?php echo htmlspecialchars($sec['title']); ?></div><?php endif; ?>
                                                            <div class="section-content"><?php echo $sec['content']; ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="sections-nav"><button id="sections-prev" class="btn">‹</button></div>
                                <div style="position:absolute;right:8px;top:50%;transform:translateY(-50%);z-index:40"><button id="sections-next" class="btn">›</button></div>
                                <div class="sections-dots" id="sections-dots"></div>
                            </div>

                        <?php endif; ?>

                        <script>
                        (function(){
                            var car = document.getElementById('sections-carousel');
                            if(!car) return;
                            var slides = Array.prototype.slice.call(car.querySelectorAll('.section-slide'));
                            var prev = document.getElementById('sections-prev');
                            var next = document.getElementById('sections-next');
                            var dotsWrap = document.getElementById('sections-dots');
                            var idx = 0;
                            if(slides.length === 0) return;

                            slides.forEach(function(s,i){
                                var d = document.createElement('button'); d.setAttribute('data-idx',i); d.addEventListener('click',function(){ show(i); }); dotsWrap.appendChild(d);
                            });

                            function activate(){
                                slides.forEach(function(s){ s.classList.remove('show'); });
                                slides[idx].classList.add('show');
                                var ds = dotsWrap.querySelectorAll('button'); ds.forEach(function(b,i){ b.style.opacity = (i===idx? '1':'0.45'); });
                                if(slides.length <= 1){ if(prev) prev.style.display='none'; if(next) next.style.display='none'; }
                            }
                            function show(i){ if(i<0) i = slides.length-1; if(i>=slides.length) i=0; idx = i; activate(); }
                            if(prev) prev.addEventListener('click', function(e){ e.preventDefault(); show(idx-1); });
                            if(next) next.addEventListener('click', function(e){ e.preventDefault(); show(idx+1); });

                            document.addEventListener('keydown', function(e){ if(e.key === 'ArrowLeft') { show(idx-1); } if(e.key === 'ArrowRight') { show(idx+1); } });

                            (function(){
                                var startX = 0, deltaX = 0, dragging = false;
                                car.addEventListener('pointerdown', function(e){ dragging = true; startX = e.clientX; car.setPointerCapture(e.pointerId); });
                                car.addEventListener('pointermove', function(e){ if(!dragging) return; deltaX = e.clientX - startX; });
                                car.addEventListener('pointerup', function(e){ dragging = false; if(Math.abs(deltaX) > 40){ if(deltaX < 0) show(idx+1); else show(idx-1); } deltaX = 0; });
                                car.addEventListener('pointercancel', function(){ dragging = false; deltaX = 0; });
                            })();

                            show(0);
                            function syncSectionHeights(){
                                try{
                                    var h = car.getBoundingClientRect().height || 0;
                                    slides.forEach(function(sl){
                                        sl.style.height = (h ? h + 'px' : 'auto');
                                        var card = sl.querySelector('.section-card');
                                        if(card){ card.style.height = (h ? h + 'px' : 'auto'); }
                                        var right = card ? card.querySelector('.right') : null;
                                        if(right) right.style.height = (h ? h + 'px' : 'auto');
                                    });
                                }catch(e){console.warn('syncSectionHeights error', e);}                            
                            }
                            window.addEventListener('load', function(){ setTimeout(syncSectionHeights, 60); });
                            window.addEventListener('resize', function(){ setTimeout(syncSectionHeights, 60); });
                            car.querySelectorAll('img').forEach(function(img){ if(!img.complete){ img.addEventListener('load', function(){ setTimeout(syncSectionHeights,30); }); } });
                        })();
                        </script>

                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="<?php echo URLROOT; ?>/products/modpacks" class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-cyan-500 to-purple-600 rounded-xl font-semibold shadow-lg hover:opacity-95">Xem gói dịch vụ <i class="fa-solid fa-arrow-right text-sm"></i></a>
                            <a href="<?php echo URLROOT; ?>/contact" class="inline-flex items-center gap-2 px-5 py-3 border border-gray-800 rounded-xl text-gray-200">Liên hệ hỗ trợ</a>
                        </div>
                    </div>
                </div>

                <div class="hero-panel" data-aos="fade-left">
                    <div class="panel-inner hero-media">
                        <?php
                            $fullGallery = isset($data['about']->gallery) && $data['about']->gallery ? json_decode($data['about']->gallery, true) : [];
                            // Main image is taken exclusively from the admin `image` field (separate from gallery)
                            $mainImage = isset($data['about']->image) && $data['about']->image ? $data['about']->image : null;
                            $displayGallery = $fullGallery; // gallery remains independent
                        ?>
                        <?php if(!empty($mainImage)): ?>
                            <div style="width:100%;height:100%">
                                <img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($mainImage); ?>" alt="Main Image" style="width:100%;height:100%;object-fit:cover;border-radius:16px">
                            </div>
                        <?php elseif(isset($data['about']->image) && $data['about']->image): ?>
                            <div style="width:100%;height:100%">
                                <img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($data['about']->image); ?>" alt="About Image" style="width:100%;height:100%;object-fit:cover;border-radius:16px">
                            </div>
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.04));border-radius:16px">
                                <svg class="mx-auto mb-4" width="160" height="160" viewBox="0 0 24 24" fill="none" stroke="url(#g)">
                                    <defs><linearGradient id="g"><stop offset="0%" stop-color="#06b6d4"/><stop offset="100%" stop-color="#9333ea"/></linearGradient></defs>
                                    <rect x="2" y="2" width="20" height="20" rx="6" stroke-width="1.5"/>
                                    <path d="M8 14s1.5-2 4-2 4 2 4 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php // Intro GIF overlay: prefer DB value, but tolerate missing files and fallback to newest uploads
        $introGif = !empty($data['about']->intro_gif) ? $data['about']->intro_gif : null;
        $introDur = isset($data['about']->intro_duration) ? $data['about']->intro_duration : null;

        // Resolve uploads directory (realpath when possible)
        $uploadsDir = realpath(APPROOT . '/../public/uploads');
        if(!$uploadsDir) $uploadsDir = APPROOT . '/../public/uploads';
        $uploadsDir = rtrim($uploadsDir, DIRECTORY_SEPARATOR);

        $introPath = null;
        $serverHasFile = false;

        if(!empty($introGif)){
            $candidate = basename($introGif);
            $possible = $uploadsDir . DIRECTORY_SEPARATOR . $candidate;
            if(file_exists($possible)){
                $introPath = $possible;
                $serverHasFile = true;
                $introGif = $candidate;
            } else {
                // try case-insensitive and url-decoded matches
                $files = glob($uploadsDir . DIRECTORY_SEPARATOR . '*');
                foreach($files as $f){
                    if(!is_file($f)) continue;
                    if(strtolower(basename($f)) === strtolower($candidate) || strtolower(basename($f)) === strtolower(rawurldecode($candidate))){
                        $introPath = $f;
                        $serverHasFile = true;
                        $introGif = basename($f);
                        break;
                    }
                }
            }
        }

        // If DB value missing or not found on disk, pick newest gif/webp in uploads
        if(!$serverHasFile){
            $found = [];
            $found = array_merge($found, glob($uploadsDir . DIRECTORY_SEPARATOR . 'img_*.gif'));
            $found = array_merge($found, glob($uploadsDir . DIRECTORY_SEPARATOR . '*.gif'));
            $found = array_merge($found, glob($uploadsDir . DIRECTORY_SEPARATOR . 'img_*.webp'));
            $found = array_merge($found, glob($uploadsDir . DIRECTORY_SEPARATOR . '*.webp'));
            // keep only files
            $found = array_values(array_filter($found, function($p){ return is_file($p); }));
            if(!empty($found)){
                usort($found, function($a,$b){ return filemtime($b) <=> filemtime($a); });
                $introPath = $found[0];
                $introGif = basename($found[0]);
                $serverHasFile = true;
            }
        }
        // Ensure we have a usable duration (default to 5s when admin didn't set)
        $introDurFloat = 5.0;
        if(isset($introDur) && is_numeric($introDur) && floatval($introDur) > 0){
            $introDurFloat = floatval($introDur);
        }
        // Small debug: expose resolved values in page source
        echo "<!-- about-intro: gif=" . htmlspecialchars($introGif) . " dur=" . htmlspecialchars((string)$introDurFloat) . " serverHasFile=" . ($serverHasFile?1:0) . " -->\n";
    ?>
    <?php if($introGif && $serverHasFile): ?>
        <?php $gifUrl = URLROOT . '/uploads/' . rawurlencode($introGif); ?>
        <div id="about-intro-overlay" aria-hidden="true" style="position:fixed;top:0;left:0;width:100vw;height:100vh;display:flex;align-items:center;justify-content:center;background-color:rgba(0,0,0,0.16);z-index:2147483647;overflow:hidden;background-image:url('<?php echo $gifUrl; ?>');background-size:cover !important;background-position:center center !important;background-repeat:no-repeat !important;">
            <img id="about-intro-gif" src="<?php echo $gifUrl; ?>" alt="Intro" style="position:absolute;top:0;left:0;width:100vw;height:100vh;object-fit:cover;display:block;max-width:none;border:0;margin:0;padding:0;">
        </div>
        <script>
            (function(){
                try{
                    // Debug: expose resolved values to console so admin can verify in browser devtools
                    console.log('about:intro debug', { gif: <?php echo json_encode($introGif); ?>, duration: <?php echo json_encode($introDurFloat); ?>, serverHasFile: <?php echo json_encode($serverHasFile ? 1 : 0); ?> });
                    var overlay = document.getElementById('about-intro-overlay');
                    var main = document.getElementById('about-section');
                    var dur = parseFloat(<?php echo json_encode($introDurFloat); ?>) || 0;
                    var gifUrl = <?php echo json_encode($gifUrl); ?>;

                    function applyStrongStyles(el, styles){
                        try{
                            for(var k in styles){ if(!styles.hasOwnProperty(k)) continue; el.style.setProperty(k, styles[k], 'important'); }
                        }catch(e){}
                    }

                    function showOverlay(){ if(overlay) overlay.style.setProperty('display','flex','important'); document.documentElement.style.overflow = 'hidden'; if(main) main.style.opacity = 0; }
                    function hideOverlay(){ if(overlay) overlay.style.setProperty('display','none','important'); document.documentElement.style.overflow = ''; if(main) main.style.opacity = 1; }

                    if(!overlay || dur <= 0){ hideOverlay(); return; }

                    // Move overlay to document.body to avoid being affected by other container CSS
                    try{ if(overlay.parentNode !== document.body) document.body.appendChild(overlay); }catch(e){}

                    // Apply high-priority styles to overlay and image to ensure visibility
                    if(overlay){
                        applyStrongStyles(overlay, {
                            'position':'fixed','top':'0','left':'0','width':'100vw','height':'100vh','display':'flex','align-items':'center','justify-content':'center','background-color':'rgba(0,0,0,0.96)','z-index':'2147483647','overflow':'hidden','pointer-events':'auto'
                        });
                    }
                    var imgEl = document.getElementById('about-intro-gif');
                    if(imgEl){
                        applyStrongStyles(imgEl, {
                            'position':'fixed','top':'0','left':'0','width':'100vw','height':'100vh','max-width':'none','object-fit':'cover','display':'block','visibility':'visible','z-index':'2147483648'
                        });
                    }

                    showOverlay();

                    // Preload probe: ensure GIF can be fetched/rendered
                    try{
                        var probe = new Image();
                        probe.onload = function(){
                            console.log('about:intro load ok', probe.naturalWidth, probe.naturalHeight);
                            if(imgEl){ try{ imgEl.src = gifUrl; }catch(e){} }
                        };
                        probe.onerror = function(err){
                            console.warn('about:intro load failed, attempting fetch status', err);
                            try{
                                fetch(gifUrl, { method: 'GET', mode: 'same-origin' }).then(function(res){
                                    console.log('about:intro fetch status', res.status);
                                    if(res.ok){
                                        res.blob().then(function(b){
                                            var u = URL.createObjectURL(b);
                                            if(imgEl){ imgEl.src = u; }
                                            else { var ni = new Image(); ni.id='about-intro-gif'; ni.src = u; applyStrongStyles(ni, {'position':'fixed','top':'0','left':'0','width':'100vw','height':'100vh','object-fit':'cover','z-index':'2147483648','display':'block'}); overlay.appendChild(ni); }
                                        }).catch(function(e){ console.warn('about:intro blob error', e); });
                                    }
                                }).catch(function(e){ console.warn('about:intro fetch error', e); });
                            }catch(e){ console.warn('about:intro probe catch', e); }
                        };
                        probe.src = gifUrl;
                    }catch(e){ console.warn('about:intro preload error', e); }

                    // Hide overlay after duration
                    setTimeout(function(){ hideOverlay(); }, Math.max(500, Math.round(dur * 1000)));

                }catch(e){ try{ document.getElementById('about-section').style.opacity = 1; }catch(_){} }
            })();
        </script>
    <?php else: ?>
        <script>
            // No intro GIF available or duration invalid — reveal page immediately and log diagnostic
            try{ console.log('about:intro no intro available', { gif: <?php echo json_encode($introGif); ?>, duration: <?php echo json_encode($introDur); ?>, serverHasFile: <?php echo json_encode($serverHasFile ? 1 : 0); ?> }); }catch(e){}
            document.getElementById('about-section').style.opacity = 1;
        </script>
    <?php endif; ?>


        <!-- Stats row (moved up under hero) -->
        <div class="mt-8">
            <style>
                .stats-row{display:flex;gap:18px;align-items:stretch;flex-wrap:wrap}
                .stat-card{flex:1 1 260px;min-width:180px;display:flex;align-items:center;gap:18px;padding:22px;border-radius:16px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.06));box-shadow:0 14px 40px rgba(2,6,23,0.55)}
                .stat-icon{width:76px;height:76px;border-radius:14px;background:linear-gradient(135deg,#06b6d4,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;flex:0 0 76px}
                .stat-value{font-size:2rem;font-weight:900;color:#fff;margin-bottom:2px}
                .stat-label{color:#9fb6cc;font-size:0.95rem}
            </style>

            <div class="stats-row">
                <?php if(!empty($data['about']->uptime)): ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="<?php echo !empty($data['about']->uptime_icon) ? htmlspecialchars($data['about']->uptime_icon) : 'fa-solid fa-clock'; ?>"></i></div>
                        <div>
                            <div class="stat-value"><?php echo htmlspecialchars($data['about']->uptime); ?></div>
                            <div class="stat-label">Uptime cam kết</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($data['about']->support)): ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="<?php echo !empty($data['about']->support_icon) ? htmlspecialchars($data['about']->support_icon) : 'fa-solid fa-headset'; ?>"></i></div>
                        <div>
                            <div class="stat-value"><?php echo htmlspecialchars($data['about']->support); ?></div>
                            <div class="stat-label">Hỗ trợ khách hàng</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($data['about']->performance)): ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="<?php echo !empty($data['about']->performance_icon) ? htmlspecialchars($data['about']->performance_icon) : 'fa-solid fa-tachometer-alt'; ?>"></i></div>
                        <div>
                            <div class="stat-value"><?php echo htmlspecialchars($data['about']->performance); ?></div>
                            <div class="stat-label">Hiệu năng</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Partners & Modpacks -->
        <div class="mt-20 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <?php if(!empty($data['about']->partners_heading)): ?><h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($data['about']->partners_heading); ?></h3><?php endif; ?>
                <?php $partners = isset($data['about']->partners) && $data['about']->partners ? json_decode($data['about']->partners, true) : []; ?>
                <?php if(!empty($partners)): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach($partners as $p): ?>
                            <a href="<?php echo htmlspecialchars($p['url'] ?? '#'); ?>" class="p-4 bg-gray-900/20 rounded-lg flex items-center justify-center hover:scale-105 transition-transform">
                                <?php if(!empty($p['logo'])): ?><img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($p['logo']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="h-16 object-contain"><?php else: ?><div class="text-gray-300"><?php echo htmlspecialchars($p['name']); ?></div><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400">Chưa có đối tác được thiết lập. Vui lòng thêm từ trang quản trị.</p>
                <?php endif; ?>
            </div>

            <div>
                <?php if(!empty($data['about']->modpacks_heading)): ?><h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($data['about']->modpacks_heading); ?></h3><?php endif; ?>
                <?php $modpacks = isset($data['about']->modpacks) && $data['about']->modpacks ? json_decode($data['about']->modpacks, true) : []; ?>
                <?php if(!empty($modpacks)): ?>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach($modpacks as $m): ?>
                            <div class="p-3 bg-gray-900/20 rounded-lg text-center">
                                <?php if(!empty($m['logo'])): ?><img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($m['logo']); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" class="mx-auto h-12 object-contain mb-2"><?php endif; ?>
                                <div class="text-white text-sm"><?php echo htmlspecialchars($m['name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400">Chưa có modpacks được thêm.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Services (clickable like partners) -->
        <?php $services = isset($data['about']->services) && $data['about']->services ? json_decode($data['about']->services, true) : []; ?>
        <?php if(!empty($services)): ?>
            <div class="mt-12">
                <?php if(!empty($data['about']->services_heading)): ?><h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($data['about']->services_heading); ?></h3><?php endif; ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach($services as $s):
                        $title = isset($s['title']) ? $s['title'] : '';
                        $desc = isset($s['description']) ? $s['description'] : '';
                        $icon = isset($s['icon']) ? $s['icon'] : 'fa-solid fa-cube';
                        $link = isset($s['url']) ? trim($s['url']) : '';
                        $card = '<div class="p-4 rounded-lg bg-gradient-to-tr from-gray-800/20 to-white/3 hover:shadow-lg transition-shadow h-full">';
                        $card .= '<div class="flex items-start gap-3">';
                        $card .= '<div class="w-12 h-12 flex items-center justify-center bg-gradient-to-br from-cyan-500 to-purple-600 text-white rounded-lg text-xl"><i class="' . htmlspecialchars($icon) . '"></i></div>';
                        $card .= '<div class="flex-1">';
                        $card .= '<div class="font-semibold text-white">' . htmlspecialchars($title) . '</div>';
                        if($desc) $card .= '<div class="text-gray-400 text-sm mt-1">' . htmlspecialchars($desc) . '</div>';
                        $card .= '</div></div></div>';
                    ?>
                    <div>
                        <?php if($link): ?>
                            <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer" class="block h-full"><?php echo $card; ?></a>
                        <?php else: ?>
                            <?php echo $card; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        

        <!-- Info rows (replaces old gallery) -->
        <?php $gallery = isset($displayGallery) ? $displayGallery : (isset($data['about']->gallery) && $data['about']->gallery ? json_decode($data['about']->gallery, true) : []); ?>
        <?php if(!empty($gallery)): ?>
            <div class="mt-16">
                <?php if(!empty($data['about']->gallery_heading)): ?><h3 class="text-3xl font-bold mb-6"><?php echo htmlspecialchars($data['about']->gallery_heading); ?></h3><?php endif; ?>
                <style>
                    .info-row{display:flex;align-items:center;gap:24px;padding:18px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.04));margin-bottom:18px}
                    .info-row img{width:100%;max-width:320px;height:auto;object-fit:cover;border-radius:10px}
                    .info-text{flex:1;color:#e6eef8}
                    .info-title{font-size:1.25rem;font-weight:700;margin-bottom:6px}
                    .info-desc{color:#bcd3ea}
                    .reveal{opacity:0;transform:translateX(18px);transition:opacity .6s ease, transform .6s ease}
                    .reveal.show{opacity:1;transform:translateX(0)}
                </style>

                    <?php foreach($gallery as $i => $g): ?>
                    <div class="info-row" data-idx="<?php echo $i; ?>">
                        <div class="info-image"><img src="<?php echo URLROOT; ?>/uploads/<?php echo htmlspecialchars($g['image']); ?>" alt="<?php echo htmlspecialchars($g['caption']); ?>"></div>
                        <div class="info-text">
                            <?php if(!empty($g['title'])): ?><div class="info-title reveal"><?php echo htmlspecialchars($g['title']); ?></div><?php endif; ?>
                            <?php if(!empty($g['caption'])): ?><div class="info-desc reveal" style="transition-delay:.12s"><?php echo htmlspecialchars($g['caption']); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <script>
                    // Reveal text when rows enter viewport
                    (function(){
                        var observer = new IntersectionObserver(function(entries){
                            entries.forEach(function(ent){
                                if(ent.isIntersecting){
                                    ent.target.querySelectorAll('.reveal').forEach(function(el,i){
                                        setTimeout(function(){ el.classList.add('show'); }, i*120);
                                    });
                                    observer.unobserve(ent.target);
                                }
                            });
                        }, {threshold:0.18});
                        document.querySelectorAll('.info-row').forEach(function(r){ observer.observe(r); });
                    })();
                </script>
            </div>
        <?php endif; ?>

<script>
// Lightbox behavior and load-more
document.addEventListener('click', function(e){
    // open lightbox
    if(e.target && (e.target.closest('.gallery-link') || e.target.classList.contains('gallery-link'))){
        e.preventDefault();
        var link = e.target.closest('.gallery-link');
        var src = link.getAttribute('data-src');
        var idx = parseInt(link.getAttribute('data-idx'));
        var caption = link.getAttribute('data-caption') || '';
        var overlay = document.getElementById('lightbox');
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox-caption').textContent = caption;
        overlay.classList.add('show');
        overlay.setAttribute('data-current', idx);
    }
    if(e.target && e.target.id === 'lightbox' ){ // click on background closes
        e.target.classList.remove('show');
    }
    if(e.target && e.target.id === 'lightbox-next'){
        var overlay = document.getElementById('lightbox');
        var cur = parseInt(overlay.getAttribute('data-current')) || 0;
        var next = cur + 1;
        var a = document.querySelector('.gallery-link[data-idx="'+next+'"]');
        if(a){
            document.getElementById('lightbox-img').src = a.getAttribute('data-src');
            document.getElementById('lightbox-caption').textContent = a.getAttribute('data-caption') || '';
            overlay.setAttribute('data-current', next);
        }
    }
    if(e.target && e.target.id === 'lightbox-prev'){
        var overlay = document.getElementById('lightbox');
        var cur = parseInt(overlay.getAttribute('data-current')) || 0;
        var prev = cur - 1;
        var a = document.querySelector('.gallery-link[data-idx="'+prev+'"]');
        if(a){
            document.getElementById('lightbox-img').src = a.getAttribute('data-src');
            document.getElementById('lightbox-caption').textContent = a.getAttribute('data-caption') || '';
            overlay.setAttribute('data-current', prev);
        }
    }
});

// close with Escape
document.addEventListener('keydown', function(e){
    var overlay = document.getElementById('lightbox');
    if(!overlay) return;
    if(e.key === 'Escape') overlay.classList.remove('show');
    if(e.key === 'ArrowRight' && overlay.classList.contains('show')) document.getElementById('lightbox-next').click();
    if(e.key === 'ArrowLeft' && overlay.classList.contains('show')) document.getElementById('lightbox-prev').click();
});

// load more gallery
var loadBtn = document.getElementById('load-more-gallery');
if(loadBtn){
    loadBtn.addEventListener('click', function(){
        var hidden = document.querySelectorAll('#gallery-grid .gallery-card[style*="display: none"]');
        for(var i=0;i<8 && i<hidden.length;i++) hidden[i].style.display = 'block';
        if(document.querySelectorAll('#gallery-grid .gallery-card[style*="display: none"]').length === 0) loadBtn.style.display = 'none';
    });
}
</script>

<script>
// Hero carousel init (dots, arrows, keyboard)
(function(){
    var carousel = document.getElementById('hero-carousel');
    if(!carousel) return;
    var slides = carousel.querySelectorAll('.carousel-slide');
    var prev = document.getElementById('hero-prev');
    var next = document.getElementById('hero-next');
    var dotsWrap = document.getElementById('hero-dots');
    var current = 0;

    // create dots
    slides.forEach(function(s, i){
        var d = document.createElement('button');
        d.className = 'w-2 h-2 rounded-full bg-white/40';
        d.setAttribute('data-idx', i);
        d.addEventListener('click', function(){ show(i); });
        dotsWrap.appendChild(d);
    });

    function activateDots(){
        var ds = dotsWrap.querySelectorAll('button');
        ds.forEach(function(b, i){ b.style.opacity = (i===current ? '1' : '.45'); });
    }

    function show(idx){
        if(idx < 0) idx = slides.length - 1;
        if(idx >= slides.length) idx = 0;
        slides.forEach(function(s){ s.style.display = 'none'; });
        slides[idx].style.display = 'block';
        current = idx;
        activateDots();
    }

    if(prev) prev.addEventListener('click', function(){ show(current-1); });
    if(next) next.addEventListener('click', function(){ show(current+1); });

    // keyboard arrows when focus inside carousel
    document.addEventListener('keydown', function(e){ if(carousel && (e.key === 'ArrowRight')) { show(current+1); } if(carousel && (e.key === 'ArrowLeft')) { show(current-1); } });

    // autoplay
    var autoplay = true; var autoplayDelay = 4500; var autoplayTimer = null;
    function startAuto(){ if(!autoplay) return; autoplayTimer = setInterval(function(){ show(current+1); }, autoplayDelay); }
    function stopAuto(){ if(autoplayTimer) clearInterval(autoplayTimer); }
    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);

    // init
    show(0);
    startAuto();
})();
</script>

        <!-- CTA (from DB) -->
        <?php if(!empty($data['about']->cta_heading) || !empty($data['about']->cta_text)): ?>
            <div class="mt-16 p-8 bg-gradient-to-r from-cyan-800 to-purple-800 rounded-3xl text-center">
                <?php if(!empty($data['about']->cta_heading)): ?><h3 class="text-2xl font-bold"><?php echo htmlspecialchars($data['about']->cta_heading); ?></h3><?php endif; ?>
                <?php if(!empty($data['about']->cta_text)): ?><p class="text-gray-200 mt-2"><?php echo htmlspecialchars($data['about']->cta_text); ?></p><?php endif; ?>
                <?php if(!empty($data['about']->cta_button_text) && !empty($data['about']->cta_button_url)): ?>
                    <div class="mt-6"><a href="<?php echo htmlspecialchars($data['about']->cta_button_url); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-900 rounded-xl font-semibold"><?php echo htmlspecialchars($data['about']->cta_button_text); ?></a></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>

<!-- Lightbox overlay (shared by hero slides) -->
<div id="lightbox" class="lightbox-overlay" role="dialog" aria-hidden="true" style="position:fixed;inset:0;background:rgba(0,0,0,0.88);display:none;align-items:center;justify-content:center;z-index:9999">
    <div style="position:absolute;left:12px;top:50%;transform:translateY(-50%)"><button id="lightbox-prev" style="background:transparent;border:0;color:#fff;font-size:42px;padding:12px;cursor:pointer">‹</button></div>
    <div style="position:absolute;right:12px;top:50%;transform:translateY(-50%)"><button id="lightbox-next" style="background:transparent;border:0;color:#fff;font-size:42px;padding:12px;cursor:pointer">›</button></div>
    <div style="max-width:1200px;max-height:90vh;padding:20px;">
        <img id="lightbox-img" src="" alt="" style="width:100%;height:auto;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.6);">
        <div id="lightbox-caption" style="color:#ddd;margin-top:8px;text-align:center"></div>
    </div>
</div>
<noscript><style>#about-section{opacity:1 !important}</style></noscript>
