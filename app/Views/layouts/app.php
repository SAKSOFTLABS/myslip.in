<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>MYSLIP.IN Billing & Accounting</title>
    
    <!-- Bootstrap 5 CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    
    <!-- Application CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/print.css') ?>" rel="stylesheet">
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <script>
        window.APP_URL = "<?= url() ?>";
        window.GST_ENABLED = <?= \App\Core\Session::get('gst_enabled', true) ? 'true' : 'false' ?>;
        window.CURRENCY_SYMBOL = "<?= \App\Core\Session::get('currency_symbol', '₹') ?>";
    </script>
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar -->
    <?php \App\Core\View::partial('sidebar'); ?>

    <!-- Main Content -->
    <div class="app-main">
        <!-- Top Navbar -->
        <?php \App\Core\View::partial('navbar'); ?>

        <!-- Content Area -->
        <main class="content-body">
            <!-- Flash Messages -->
            <?php \App\Core\View::partial('alerts'); ?>

            <!-- Subscription Expiry Warning Banner -->
            <?php if (\App\Core\Session::get('is_subscription_expired')): ?>
                <div class="alert alert-danger d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                        <div>
                            <strong>Subscription Expired!</strong>
                            <span>Your company license has expired. New transactions are locked. Please renew to continue seamless operations.</span>
                        </div>
                    </div>
                    <a href="<?= url('/subscription/plans') ?>" class="btn btn-sm btn-danger fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Renew Plan
                    </a>
                </div>
            <?php endif; ?>

            <!-- View Injected Content -->
            <?= $content ?>
        </main>

        <!-- Footer -->
        <?php \App\Core\View::partial('footer'); ?>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS & Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= asset('js/app.js') ?>"></script>

<?php if (!\App\Core\Session::isSuperAdmin()): ?>
<script>
(function() {
    // 1. Fullscreen Persistence Engine
    function isFsLocked() {
        return localStorage.getItem('myslip_fullscreen_locked') !== '0';
    }

    function requestFullscreen() {
        if (!isFsLocked()) return;
        if (!document.fullscreenElement) {
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (rfs) {
                rfs.call(el).catch(function() {});
            }
        }
    }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            localStorage.setItem('myslip_fullscreen_locked', '1');
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (rfs) {
                rfs.call(el).catch(function() {});
            }
        } else {
            localStorage.setItem('myslip_fullscreen_locked', '0');
            const efs = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (efs) {
                efs.call(document).catch(function() {});
            }
        }
    }

    function updateIcon() {
        const isFs = !!document.fullscreenElement;
        const icon = document.getElementById('fsIcon');
        if (icon) {
            icon.className = isFs ? 'bi bi-fullscreen-exit text-primary' : 'bi bi-arrows-fullscreen';
        }
        const btn = document.getElementById('btnToggleFullscreen');
        if (btn) {
            btn.title = isFs ? 'Exit Fullscreen' : 'Enter Fullscreen (F11)';
        }
    }

    // Default lock to active after login
    if (localStorage.getItem('myslip_fullscreen_locked') === null) {
        localStorage.setItem('myslip_fullscreen_locked', '1');
    }

    // 2. Seamless In-App Sidebar Navigation
    // Keeps document alive so browser NEVER drops out of fullscreen when clicking sidebar menus
    function navigateSeamless(url) {
        const mainContent = document.querySelector('.content-body');
        if (mainContent) {
            mainContent.style.opacity = '0.5';
            mainContent.style.transition = 'opacity 0.15s ease';
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) {
            if (!res.ok) {
                window.location.href = url;
                return null;
            }
            if (res.url && res.url.includes('/login')) {
                window.location.href = res.url;
                return null;
            }
            return res.text();
        })
        .then(function(html) {
            if (!html) return;
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // 1. Update Title
            if (doc.title) {
                document.title = doc.title;
            }

            // 2. Update Sidebar (active state)
            const newMenu = doc.querySelector('.sidebar-menu');
            const curMenu = document.querySelector('.sidebar-menu');
            if (newMenu && curMenu) {
                curMenu.innerHTML = newMenu.innerHTML;
            }

            // 3. Update Navbar Content
            const newNavbar = doc.querySelector('.app-navbar');
            const curNavbar = document.querySelector('.app-navbar');
            if (newNavbar && curNavbar) {
                curNavbar.innerHTML = newNavbar.innerHTML;
            }

            // 4. Update Main Content & Execute Scripts
            const newBody = doc.querySelector('.content-body');
            const curBody = document.querySelector('.content-body');
            if (newBody && curBody) {
                curBody.innerHTML = newBody.innerHTML;
                curBody.style.opacity = '1';

                const scripts = Array.from(curBody.querySelectorAll('script'));
                scripts.forEach(function(oldScript) {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(function(attr) {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.text = oldScript.innerHTML;
                    }
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }

            // 5. Update browser history
            window.history.pushState({ url: url }, '', url);

            // 6. Scroll to top
            window.scrollTo(0, 0);

            // 7. Re-initialize Global Datepickers
            if (typeof initGlobalDatepickers === 'function') {
                initGlobalDatepickers();
            }

            // 8. Close mobile sidebar if open
            const sidebar = document.querySelector('.app-sidebar');
            if (sidebar && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }

            // 9. Dispatch DOMContentLoaded for newly loaded page listeners
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // 10. Maintain fullscreen
            requestFullscreen();
            updateIcon();
        })
        .catch(function(err) {
            console.error('Seamless load fallback:', err);
            window.location.href = url;
        });
    }

    // Listen to browser Back/Forward navigation
    window.addEventListener('popstate', function() {
        if (window.location.href) {
            navigateSeamless(window.location.href);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        requestFullscreen();

        // Ensure user gestures anywhere on the screen enforce fullscreen if locked
        function onUserInteraction() {
            if (isFsLocked() && !document.fullscreenElement) {
                requestFullscreen();
            }
        }
        document.addEventListener('pointerdown', onUserInteraction, { passive: true });
        document.addEventListener('click', onUserInteraction, { passive: true });
        document.addEventListener('keydown', onUserInteraction, { passive: true });

        // Delegate Click on Sidebar Menu Links & Navbar Links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.app-sidebar a, .navbar-brand-mobile, a.seamless-link');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (href.includes('/logout') || href.includes('/invoices/print') || href.includes('/invoices/view') || href.includes('/export')) return;

            // Internal link -> navigate seamlessly
            if (href.startsWith('/') || href.startsWith(window.location.origin)) {
                e.preventDefault();
                navigateSeamless(href);
            }
        });

        // Fullscreen Toggle Button in Navbar (delegated)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('#btnToggleFullscreen');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                toggleFullscreen();
            }
        });
    });

    document.addEventListener('fullscreenchange', updateIcon);
    document.addEventListener('webkitfullscreenchange', updateIcon);
})();
</script>
<?php endif; ?>

</body>
</html>
