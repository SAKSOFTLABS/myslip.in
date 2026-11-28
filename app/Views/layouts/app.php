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
    function requestFullscreen() {
        if (sessionStorage.getItem('myslip_fullscreen_disabled') === '1') {
            return;
        }
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
            sessionStorage.removeItem('myslip_fullscreen_disabled');
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (rfs) {
                rfs.call(el).catch(function() {});
            }
        } else {
            sessionStorage.setItem('myslip_fullscreen_disabled', '1');
            const efs = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
            if (efs) {
                efs.call(document).catch(function() {});
            }
        }
    }

    function updateIcon() {
        const icon = document.getElementById('fsIcon');
        if (icon) {
            icon.className = document.fullscreenElement ? 'bi bi-fullscreen-exit text-primary' : 'bi bi-arrows-fullscreen';
        }
        const btn = document.getElementById('btnToggleFullscreen');
        if (btn) {
            btn.title = document.fullscreenElement ? 'Exit Fullscreen' : 'Enter Fullscreen (F11)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Attempt fullscreen on page load
        requestFullscreen();

        // Listen for user interactions to enter fullscreen if not yet active
        function onUserGesture() {
            if (!document.fullscreenElement && sessionStorage.getItem('myslip_fullscreen_disabled') !== '1') {
                requestFullscreen();
            }
        }
        document.addEventListener('click', onUserGesture, { passive: true });
        document.addEventListener('keydown', onUserGesture, { passive: true });

        const btn = document.getElementById('btnToggleFullscreen');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleFullscreen();
            });
        }
    });

    document.addEventListener('fullscreenchange', updateIcon);
    document.addEventListener('webkitfullscreenchange', updateIcon);
})();
</script>
<?php endif; ?>

</body>
</html>
