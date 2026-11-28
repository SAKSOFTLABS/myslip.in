<?php
$companyName = \App\Core\Session::get('company_name', 'My Company');
$fyName = \App\Core\Session::get('financial_year_name', '2026-2027');
$userName = \App\Core\Session::get('user_name', 'User');
$userEmail = \App\Core\Session::get('user_email', '');
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
?>
<header class="app-navbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-sm btn-light border d-lg-none" id="sidebarToggle">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="navbar-brand-mobile">MYSLIP</span>

        <!-- Company & FY Badge -->
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-dark dropdown-toggle fw-bold d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-building text-primary"></i>
                    <span><?= e($companyName) ?></span>
                </button>
                <ul class="dropdown-menu shadow">
                    <li><h6 class="dropdown-header">Switch Company</h6></li>
                    <li><a class="dropdown-item" href="<?= url('/company/switch') ?>"><i class="bi bi-arrow-repeat me-2"></i> Select Another Company</a></li>
                    <li><a class="dropdown-item" href="<?= url('/company/create') ?>"><i class="bi bi-plus-circle me-2"></i> Add New Company</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('/settings') ?>"><i class="bi bi-gear me-2"></i> Company Settings</a></li>
                </ul>
            </div>

            <span class="badge bg-secondary-subtle text-secondary border d-none d-md-inline-block">
                <i class="bi bi-calendar3 me-1"></i> FY: <?= e($fyName) ?>
            </span>

            <?php if ($gstEnabled): ?>
                <span class="badge bg-success-subtle text-success border d-none d-sm-inline-block">
                    <i class="bi bi-check2-circle me-1"></i> GST ON
                </span>
            <?php else: ?>
                <span class="badge bg-secondary-subtle text-muted border d-none d-sm-inline-block">
                    NON-GST Mode
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Quick Action: New Sale (F2) -->
        <a href="<?= url('/sales/create') ?>" class="btn btn-sm btn-primary-custom d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span class="d-none d-md-inline">New Sale</span>
            <span class="kbd-shortcut d-none d-lg-inline ms-1">F2</span>
        </a>

        <?php if (!\App\Core\Session::isSuperAdmin()): ?>
        <!-- Fullscreen Toggle Button -->
        <button type="button" class="btn btn-sm btn-light border d-flex align-items-center justify-content-center" id="btnToggleFullscreen" title="Toggle Fullscreen" style="width: 32px; height: 32px;">
            <i class="bi bi-arrows-fullscreen" id="fsIcon"></i>
        </button>
        <?php endif; ?>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.75rem; font-weight: 700;">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </div>
                <span class="d-none d-md-inline fw-semibold"><?= e($userName) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <div class="px-3 py-2">
                        <div class="fw-bold"><?= e($userName) ?></div>
                        <div class="text-muted small"><?= e($userEmail) ?></div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= url('/subscription/plans') ?>"><i class="bi bi-award me-2"></i> My Subscription</a></li>
                <li><a class="dropdown-item" href="<?= url('/settings') ?>"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
