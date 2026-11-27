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

</body>
</html>
