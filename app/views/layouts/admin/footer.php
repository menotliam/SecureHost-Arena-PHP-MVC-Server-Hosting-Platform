            </main>
            <footer class="admin-footer">
                <p class="mb-0 admin-footer-note">© <?php echo date('Y'); ?> <?php echo SITENAME; ?>. Khu vực quản trị của Cloud Arena.</p>
            </footer>
        </div>
    </div>

    <script src="<?php echo URLROOT; ?>/admin_assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="<?php echo URLROOT; ?>/admin_assets/js/popper.min.js"></script>
    <script src="<?php echo URLROOT; ?>/admin_assets/js/bootstrap.min.js"></script>
    <script src="<?php echo URLROOT; ?>/admin_assets/js/jquery.slimscroll.min.js"></script>
    <script src="<?php echo URLROOT; ?>/admin_assets/js/plugins.js"></script>
    <script src="<?php echo URLROOT; ?>/admin_assets/js/scripts.js"></script>
    <script>
        window.URLROOT = '<?php echo URLROOT; ?>';
    </script>
    <script src="<?php echo URLROOT; ?>/js/main.js?v=<?php echo filemtime(APPROOT . '/../public/js/main.js'); ?>"></script>
    <script>
        (function() {
            if (!document.body.classList.contains('admin-modern')) {
                return;
            }

            // Fallback binder when cached/conflicting scripts block admin interactions.
            if (document.body.getAttribute('data-admin-ui-bound') !== '1') {
                document.body.setAttribute('data-admin-ui-bound', '1');

                var sidebarToggle = document.getElementById('adminSidebarToggle');
                var sidebarOverlay = document.getElementById('adminOverlay');
                var themeToggle = document.getElementById('adminThemeToggle');
                var isDesktop = function() { return window.innerWidth >= 992; };
                var syncToggleState = function() {
                    if (!sidebarToggle) { return; }
                    if (isDesktop()) {
                        sidebarToggle.setAttribute('aria-expanded', document.body.classList.contains('admin-sidebar-hidden') ? 'false' : 'true');
                    } else {
                        sidebarToggle.setAttribute('aria-expanded', document.body.classList.contains('admin-sidebar-open') ? 'true' : 'false');
                    }
                };
                var setTheme = function(toDark) {
                    document.body.classList.toggle('admin-theme-dark', toDark);
                    if (themeToggle) {
                        themeToggle.innerHTML = toDark ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
                    }
                    try { localStorage.setItem('admin_theme_mode', toDark ? 'dark' : 'light'); } catch (e) {}
                };

                try {
                    setTheme(localStorage.getItem('admin_theme_mode') === 'dark');
                } catch (e) {
                    setTheme(false);
                }

                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function() {
                        if (isDesktop()) {
                            var hidden = !document.body.classList.contains('admin-sidebar-hidden');
                            document.body.classList.toggle('admin-sidebar-hidden', hidden);
                            try { localStorage.setItem('admin_sidebar_hidden', hidden ? '1' : '0'); } catch (e) {}
                        } else {
                            document.body.classList.toggle('admin-sidebar-open');
                        }
                        syncToggleState();
                    });
                }

                if (sidebarOverlay) {
                    sidebarOverlay.addEventListener('click', function() {
                        document.body.classList.remove('admin-sidebar-open');
                        syncToggleState();
                    });
                }

                if (themeToggle) {
                    themeToggle.addEventListener('click', function() {
                        setTheme(!document.body.classList.contains('admin-theme-dark'));
                    });
                }

                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 992) {
                        document.body.classList.remove('admin-sidebar-open');
                    } else {
                        document.body.classList.remove('admin-sidebar-hidden');
                    }
                    syncToggleState();
                });
                syncToggleState();
            }
        })();
    </script>
</body>
</html>
