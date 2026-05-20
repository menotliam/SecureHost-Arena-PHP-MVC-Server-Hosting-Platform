/**
 * Trang Liên hệ — Support console (GSAP topology, tilt, Typed / fallback).
 */
(function () {
    'use strict';

    var cfgEl = document.getElementById('contact-support-config');
    var cfg = {};
    try {
        cfg = cfgEl ? JSON.parse(cfgEl.textContent || '{}') : {};
    } catch (e) {
        cfg = {};
    }

    var gate = document.getElementById('support-step-gate');
    var main = document.getElementById('support-step-main');
    var btnOpen = document.getElementById('support-open-console');
    var form = document.getElementById('support-ticket-form');
    var catInput = document.getElementById('ticket_category');
    var catCards = document.querySelectorAll('[data-support-category]');
    var panels = document.querySelectorAll('[data-support-panel]');
    var consoleWrap = document.querySelector('.support-console-wrap');
    var terminalTa = document.querySelector('.support-terminal-field');
    var metricsEls = {
        healthy: document.querySelector('[data-metric="nodes-healthy"]'),
        latency: document.querySelector('[data-metric="vn-latency"]'),
        engineers: document.querySelector('[data-metric="engineers"]')
    };
    var topoLatencyEl = document.querySelector('[data-topology-latency]');

    function setStep(step) {
        if (!gate || !main) return;
        if (step === 'main') {
            gate.classList.add('support-hidden-step');
            main.classList.remove('support-hidden-step');
        } else {
            main.classList.add('support-hidden-step');
            gate.classList.remove('support-hidden-step');
        }
    }

    if (cfg.initialStep === 'main') {
        setStep('main');
    }

    if (btnOpen) {
        btnOpen.addEventListener('click', function () {
            setStep('main');
            try {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {
                window.scrollTo(0, 0);
            }
        });
    }

    var labelEl = document.getElementById('support-category-label');

    function resetForgotPasswordVisibility() {
        var pwInput = document.getElementById('support_previous_password');
        var pwToggle = document.querySelector('[data-support-pw-toggle="forgot"]');
        if (pwInput) {
            pwInput.type = 'password';
        }
        if (pwToggle) {
            pwToggle.setAttribute('aria-pressed', 'false');
            pwToggle.setAttribute('aria-label', 'Hiện mật khẩu');
            pwToggle.setAttribute('title', 'Hiện mật khẩu');
        }
    }

    function setCategory(key) {
        if (!catInput) return;
        if (key !== 'forgot_password') {
            resetForgotPasswordVisibility();
        }
        catInput.value = key;
        if (labelEl && cfg.categoryLabels && cfg.categoryLabels[key]) {
            labelEl.textContent = cfg.categoryLabels[key];
        }
        panels.forEach(function (p) {
            p.classList.toggle('hidden', p.getAttribute('data-support-panel') !== key);
        });
        catCards.forEach(function (c) {
            var active = c.getAttribute('data-support-category') === key;
            c.classList.toggle('ring-2', active);
            c.classList.toggle('ring-cyan-400/60', active);
            c.classList.toggle('border-cyan-500/50', active);
        });
    }

    catCards.forEach(function (card) {
        function activateCard() {
            var key = card.getAttribute('data-support-category');
            if (!key) return;
            if (key === 'purchase_issue' && !cfg.isLoggedIn) {
                return;
            }
            setCategory(key);
            card.classList.add('is-glow');
            window.setTimeout(function () {
                card.classList.remove('is-glow');
            }, 450);
        }
        card.addEventListener('click', function (e) {
            if (e.target.closest('a[href]')) return;
            activateCard();
        });
        card.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('a[href]')) return;
            e.preventDefault();
            activateCard();
        });
    });

    if (catInput && catInput.value) {
        setCategory(catInput.value);
    }

    (function initForgotPasswordToggle() {
        var pwInput = document.getElementById('support_previous_password');
        var pwToggle = document.querySelector('[data-support-pw-toggle="forgot"]');
        if (!pwInput || !pwToggle) {
            return;
        }
        pwToggle.addEventListener('click', function () {
            var show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            pwToggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            pwToggle.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            pwToggle.setAttribute('title', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    })();

    if (typeof gsap !== 'undefined') {
        catCards.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = e.clientX - r.left;
                var y = e.clientY - r.top;
                var rx = ((y / r.height) - 0.5) * -10;
                var ry = ((x / r.width) - 0.5) * 10;
                gsap.to(card, { duration: 0.25, rotateX: rx, rotateY: ry, y: -6, ease: 'power2.out' });
            });
            card.addEventListener('mouseleave', function () {
                gsap.to(card, { duration: 0.45, rotateX: 0, rotateY: 0, y: 0, ease: 'power2.out' });
            });
        });
    } else {
        catCards.forEach(function (card) {
            card.addEventListener('mouseenter', function () {
                card.style.transform = 'translateY(-6px)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    }

    function removeTypedCursors(el) {
        if (!el) return;
        el.querySelectorAll('.typed-cursor').forEach(function (n) {
            if (n.parentNode) {
                n.parentNode.removeChild(n);
            }
        });
    }

    function appendDiscordLinkLine(el) {
        if (!el) return;
        var href = typeof cfg.discordInviteUrl === 'string' ? cfg.discordInviteUrl : '';
        var label = typeof cfg.discordAnchorText === 'string' ? cfg.discordAnchorText : '';
        if (!href || !label) return;
        el.appendChild(document.createTextNode('\n'));
        var a = document.createElement('a');
        a.href = href;
        a.textContent = label;
        a.className = 'support-discord-terminal-link';
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        el.appendChild(a);
    }

    function initDiscordTyped() {
        var el = document.getElementById('support-discord-typed');
        if (!el) return;
        var body = typeof cfg.discordTypeText === 'string' ? cfg.discordTypeText : '';
        var href = typeof cfg.discordInviteUrl === 'string' ? cfg.discordInviteUrl : '';
        var anchorText = typeof cfg.discordAnchorText === 'string' ? cfg.discordAnchorText : '';

        if (body === '' && href && anchorText) {
            appendDiscordLinkLine(el);
            return;
        }

        if (typeof Typed !== 'undefined' && body !== '') {
            el.textContent = '';
            try {
                // eslint-disable-next-line no-new
                new Typed('#support-discord-typed', {
                    strings: [body],
                    typeSpeed: 14,
                    backSpeed: 0,
                    loop: false,
                    showCursor: true,
                    cursorChar: '▌',
                    onComplete: function () {
                        removeTypedCursors(el);
                        appendDiscordLinkLine(el);
                    }
                });
                return;
            } catch (err) {
                /* fall through */
            }
        }

        if (body === '') {
            return;
        }

        var ci = 0;
        var timer = window.setInterval(function () {
            if (ci < body.length) {
                el.textContent = body.slice(0, ci + 1) + '▌';
                ci++;
            } else {
                window.clearInterval(timer);
                el.textContent = body;
                appendDiscordLinkLine(el);
            }
        }, 42);
    }

    initDiscordTyped();

    function loopPacketOnPath(circle, pathEl, durationSec) {
        if (!circle || !pathEl || typeof pathEl.getTotalLength !== 'function') return;
        var len = pathEl.getTotalLength();
        if (!len || typeof gsap === 'undefined') return;
        var state = { t: 0 };
        gsap.to(state, {
            t: len,
            duration: durationSec,
            repeat: -1,
            ease: 'none',
            onUpdate: function () {
                var pt = pathEl.getPointAtLength(state.t % len);
                circle.setAttribute('cx', pt.x);
                circle.setAttribute('cy', pt.y);
            }
        });
    }

    function initSupportTopology() {
        var svg = document.getElementById('support-topology-svg');
        if (!svg || typeof gsap === 'undefined') return;

        var p1 = document.getElementById('support-path-sg-edge');
        var p2 = document.getElementById('support-path-tk-edge');
        var p3 = document.getElementById('support-path-vn-edge');
        var packs = svg.querySelectorAll('.support-topo-packet');
        if (packs[0] && p1) loopPacketOnPath(packs[0], p1, 3.2);
        if (packs[1] && p2) loopPacketOnPath(packs[1], p2, 2.8);
        if (packs[2] && p3) loopPacketOnPath(packs[2], p3, 2.2);

        var nodes = svg.querySelectorAll('.support-topo-node');
        if (nodes.length) {
            gsap.to(nodes, {
                opacity: 0.55,
                duration: 1.3,
                yoyo: true,
                repeat: -1,
                ease: 'sine.inOut',
                stagger: 0.18
            });
        }
    }

    initSupportTopology();

    function jitter(el, base, amp, decimals, suffix) {
        if (!el) return;
        var v = base + (Math.random() * 2 - 1) * amp;
        var t = decimals > 0 ? v.toFixed(decimals) : String(Math.round(v));
        el.textContent = t + (suffix || '');
    }

    window.setInterval(function () {
        jitter(metricsEls.healthy, 98, 0.35, 1, '%');
        var ms = 12 + Math.round((Math.random() * 2 - 1) * 2);
        if (metricsEls.latency) metricsEls.latency.textContent = ms + 'ms';
        if (topoLatencyEl) topoLatencyEl.textContent = ms + 'ms';
        var eng = metricsEls.engineers;
        if (eng && Math.random() > 0.92) {
            eng.textContent = String(2 + Math.round(Math.random()));
        }
    }, 1800);

    if (consoleWrap && form) {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('focus', function () {
                consoleWrap.classList.add('is-focused');
            });
            el.addEventListener('blur', function () {
                consoleWrap.classList.remove('is-focused');
            });
        });
    }

    if (terminalTa) {
        function syncCaret() {
            terminalTa.classList.toggle('is-idle', document.activeElement !== terminalTa);
        }
        terminalTa.addEventListener('focus', syncCaret);
        terminalTa.addEventListener('blur', syncCaret);
        syncCaret();
    }

    function isValidSupportEmail(em) {
        if (!em) {
            return false;
        }
        if (!/^[a-zA-Z0-9@.]+$/.test(em)) {
            return false;
        }
        return /^[^@]+@[^@]+\.[^@]+$/.test(em);
    }

    function markSupportFieldBorder(input, hasErr) {
        if (!input || input.readOnly) {
            return;
        }
        if (input.id === 'support-field-message' || (input.classList && input.classList.contains('support-terminal-field'))) {
            if (hasErr) {
                input.classList.add('border-red-500');
                input.classList.remove('border-emerald-500/30');
            } else {
                input.classList.remove('border-red-500');
                input.classList.add('border-emerald-500/30');
            }
            return;
        }
        if (hasErr) {
            input.classList.add('border-red-500');
            input.classList.remove('border-white/10');
        } else {
            input.classList.remove('border-red-500');
            input.classList.add('border-white/10');
        }
    }

    function clearSupportTicketClientErrors(formEl) {
        var f = formEl || document.getElementById('support-ticket-form');
        ['support-err-name', 'support-err-email', 'support-err-ticket-cat', 'support-err-order', 'support-err-prevpw', 'support-err-banned', 'support-err-message'].forEach(function (id) {
            var n = document.getElementById(id);
            if (n) {
                n.textContent = '';
            }
        });
        var ban = document.getElementById('support-client-error-banner');
        if (ban) {
            ban.textContent = '';
            ban.classList.add('hidden');
        }
        if (!f) {
            return;
        }
        markSupportFieldBorder(f.querySelector('#support-field-name'), false);
        markSupportFieldBorder(f.querySelector('#support-field-email'), false);
        markSupportFieldBorder(f.querySelector('#support-field-banned-username'), false);
        markSupportFieldBorder(f.querySelector('#support-field-message'), false);
        markSupportFieldBorder(document.getElementById('support_previous_password'), false);
        var ord = f.querySelector('select[name="order_id"]');
        if (ord) {
            ord.classList.remove('border-red-500');
        }
    }

    function validateSupportTicketForm(formEl, cfgObj) {
        clearSupportTicketClientErrors(formEl);
        var hp = formEl.querySelector('input[name="website"]');
        if (hp && String(hp.value || '').trim() !== '') {
            return true;
        }

        var catInputEl = document.getElementById('ticket_category');
        var cat = catInputEl ? String(catInputEl.value || '').trim() : '';
        var ok = true;
        var firstBad = null;

        function lineErr(id, msg, input) {
            var span = document.getElementById(id);
            if (span) {
                span.textContent = msg;
            }
            if (input) {
                markSupportFieldBorder(input, true);
                if (!firstBad) {
                    firstBad = input;
                }
            }
            ok = false;
        }

        if (!cfgObj.isLoggedIn) {
            var nameI = document.getElementById('support-field-name');
            var nameV = nameI ? nameI.value.trim() : '';
            if (nameV === '') {
                lineErr('support-err-name', 'Vui lòng nhập họ tên.', nameI);
            } else if (nameV.length > 100) {
                lineErr('support-err-name', 'Họ tên tối đa 100 ký tự.', nameI);
            }
            var emI = document.getElementById('support-field-email');
            var emV = emI ? emI.value.trim() : '';
            if (emV === '') {
                lineErr('support-err-email', 'Vui lòng nhập email.', emI);
            } else if (emV.length > 100) {
                lineErr('support-err-email', 'Email tối đa 100 ký tự.', emI);
            } else if (!isValidSupportEmail(emV)) {
                lineErr('support-err-email', 'Email không đúng định dạng.', emI);
            }
        }

        if (cat === 'purchase_issue' && !cfgObj.isLoggedIn) {
            lineErr('support-err-ticket-cat', 'Vấn đề đơn hàng yêu cầu đăng nhập.', null);
        }

        if (cat === 'purchase_issue' && cfgObj.isLoggedIn) {
            var orderSel = formEl.querySelector('select[name="order_id"]');
            if (orderSel && (!orderSel.value || String(orderSel.value).trim() === '')) {
                orderSel.classList.add('border-red-500');
                lineErr('support-err-order', 'Vui lòng chọn đơn hàng liên quan.', orderSel);
            }
        }

        if (cat === 'banned') {
            var banI = document.getElementById('support-field-banned-username');
            var banV = banI ? banI.value.trim() : '';
            if (banV === '') {
                lineErr('support-err-banned', 'Vui lòng nhập tên đăng nhập (username) cần hỗ trợ.', banI);
            } else if (banV.length > 50 || !/^[a-zA-Z0-9._-]+$/.test(banV)) {
                lineErr('support-err-banned', 'Username không hợp lệ (tối đa 50 ký tự, chỉ chữ, số, . _ -).', banI);
            }
        }

        if (cat === 'forgot_password') {
            var prevI = document.getElementById('support_previous_password');
            var prevV = prevI ? String(prevI.value || '') : '';
            if (prevV !== '') {
                if (prevV.length < 6) {
                    lineErr('support-err-prevpw', 'Mật khẩu trước đó tối thiểu 6 ký tự (nếu nhập).', prevI);
                } else if (prevV.length > 128) {
                    lineErr('support-err-prevpw', 'Mật khẩu trước đó tối đa 128 ký tự.', prevI);
                }
            }
        }

        var msgI = document.getElementById('support-field-message');
        var msgV = msgI ? msgI.value.trim() : '';
        if (msgV === '') {
            lineErr('support-err-message', 'Vui lòng nhập nội dung.', msgI);
        } else if (msgV.length < 10) {
            lineErr('support-err-message', 'Nội dung tối thiểu 10 ký tự.', msgI);
        } else if (msgV.length > 5000) {
            lineErr('support-err-message', 'Nội dung tối đa 5000 ký tự.', msgI);
        }

        if (!ok && firstBad && typeof firstBad.focus === 'function') {
            try {
                firstBad.focus();
            } catch (ignoreFocus) { /* empty */ }
        }
        return ok;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateSupportTicketForm(form, cfg)) {
                e.preventDefault();
                return;
            }
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('support-btn-loading');
                submitBtn.setAttribute('disabled', 'disabled');
            }
        });

        var resetBtn = document.getElementById('support-form-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                form.reset();
                clearSupportTicketClientErrors(form);
                var orderSel = form.querySelector('select[name="order_id"]');
                if (orderSel) {
                    orderSel.dispatchEvent(new Event('change', { bubbles: true }));
                }
                resetForgotPasswordVisibility();
                var def = cfg.defaultCategory || 'bugs_technical';
                if (catInput) catInput.value = def;
                setCategory(def);
            });
        }
    }
})();
