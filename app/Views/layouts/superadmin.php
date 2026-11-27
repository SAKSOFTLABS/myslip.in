<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>Super Admin | MYSLIP.IN</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Super Admin Sidebar -->
    <aside class="app-sidebar" style="background-color: #1e1b4b !important;">
        <a href="<?= url('/admin') ?>" class="sidebar-brand text-danger">
            <i class="bi bi-shield-lock-fill"></i>
            SUPER<span>ADMIN</span>
        </a>

        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="<?= url('/admin') ?>" class="menu-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>Overview & KPIs</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('/admin/companies') ?>" class="menu-link">
                    <i class="bi bi-buildings"></i>
                    <span>Tenant Companies</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('/admin/users') ?>" class="menu-link">
                    <i class="bi bi-people"></i>
                    <span>Registered Users</span>
                </a>
            </li>
            <li class="menu-item <?= str_contains($currentUri ?? '', '/admin/plans') ? 'active' : '' ?>">
                <a href="<?= url('/admin/plans') ?>" class="menu-link">
                    <i class="bi bi-award"></i>
                    <span>SaaS Plans & Pricing</span>
                </a>
            </li>
            <li class="menu-item <?= str_contains($currentUri ?? '', '/admin/bill-designs') && !str_contains($currentUri ?? '', '/admin/bill-designs/settings') ? 'active' : '' ?>">
                <a href="<?= url('/admin/bill-designs') ?>" class="menu-link">
                    <i class="bi bi-palette2"></i>
                    <span>Bill Designs</span>
                </a>
            </li>
            <li class="menu-item <?= str_contains($currentUri ?? '', '/admin/bill-designs/settings') ? 'active' : '' ?>">
                <a href="<?= url('/admin/bill-designs/settings') ?>" class="menu-link">
                    <i class="bi bi-whatsapp text-success"></i>
                    <span>WhatsApp Settings</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('/admin/logs') ?>" class="menu-link">
                    <i class="bi bi-activity"></i>
                    <span>System Audit Logs</span>
                </a>
            </li>
            <li class="menu-header">Tenant View</li>
            <li class="menu-item">
                <a href="<?= url('/dashboard') ?>" class="menu-link">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Return to App</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="app-main">
        <header class="app-navbar bg-white border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger fs-6"><i class="bi bi-shield-lock-fill me-1"></i> Developer Super Admin</span>
            </div>
            <div>
                <a href="<?= url('/logout') ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </a>
            </div>
        </header>

        <main class="content-body">
            <?php \App\Core\View::partial('alerts'); ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
