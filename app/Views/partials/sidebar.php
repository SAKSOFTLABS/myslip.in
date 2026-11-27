<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
$isSuperAdmin = \App\Core\Session::isSuperAdmin();
?>
<aside class="app-sidebar">
    <a href="<?= url('/dashboard') ?>" class="sidebar-brand">
        <i class="bi bi-receipt text-primary"></i>
        MY<span>SLIP</span>
    </a>

    <ul class="sidebar-menu">
        <li class="menu-item <?= str_contains($currentUri, '/dashboard') ? 'active' : '' ?>">
            <a href="<?= url('/dashboard') ?>" class="menu-link">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-header">Masters</li>
        <li class="menu-item <?= str_contains($currentUri, '/items') ? 'active' : '' ?>">
            <a href="<?= url('/items') ?>" class="menu-link">
                <i class="bi bi-box-seam"></i>
                <span>Items & Inventory</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/customers') ? 'active' : '' ?>">
            <a href="<?= url('/customers') ?>" class="menu-link">
                <i class="bi bi-people"></i>
                <span>Customers</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/vendors') ? 'active' : '' ?>">
            <a href="<?= url('/vendors') ?>" class="menu-link">
                <i class="bi bi-truck"></i>
                <span>Vendors</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/accounts') ? 'active' : '' ?>">
            <a href="<?= url('/accounts') ?>" class="menu-link">
                <i class="bi bi-journal-bookmark"></i>
                <span>Chart of Accounts</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/units') ? 'active' : '' ?>">
            <a href="<?= url('/units') ?>" class="menu-link">
                <i class="bi bi-rulers"></i>
                <span>Units & Groups</span>
            </a>
        </li>

        <li class="menu-header">Transactions</li>
        <li class="menu-item <?= (str_contains($currentUri, '/sales') && !str_contains($currentUri, '/returns')) ? 'active' : '' ?>">
            <a href="<?= url('/sales') ?>" class="menu-link">
                <i class="bi bi-file-earmark-text"></i>
                <span>Sales Invoices</span>
            </a>
        </li>
        <li class="menu-item <?= (str_contains($currentUri, '/purchases') && !str_contains($currentUri, '/returns')) ? 'active' : '' ?>">
            <a href="<?= url('/purchases') ?>" class="menu-link">
                <i class="bi bi-bag-check"></i>
                <span>Purchase Bills</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/returns') ? 'active' : '' ?>">
            <a href="<?= url('/returns') ?>" class="menu-link">
                <i class="bi bi-arrow-left-right"></i>
                <span>Returns (Credit/Debit)</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/vouchers/receipts') ? 'active' : '' ?>">
            <a href="<?= url('/vouchers/receipts') ?>" class="menu-link">
                <i class="bi bi-cash-stack"></i>
                <span>Receipts</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/vouchers/payments') ? 'active' : '' ?>">
            <a href="<?= url('/vouchers/payments') ?>" class="menu-link">
                <i class="bi bi-credit-card-2-back"></i>
                <span>Payments</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/vouchers/journals') ? 'active' : '' ?>">
            <a href="<?= url('/vouchers/journals') ?>" class="menu-link">
                <i class="bi bi-journal-check"></i>
                <span>Journal Entries</span>
            </a>
        </li>

        <li class="menu-header">Reports</li>
        <li class="menu-item <?= (str_contains($currentUri, '/reports') && !str_contains($currentUri, '/gst')) ? 'active' : '' ?>">
            <a href="<?= url('/reports') ?>" class="menu-link">
                <i class="bi bi-bar-chart-line"></i>
                <span>Financial & Sales</span>
            </a>
        </li>
        <?php if ($gstEnabled): ?>
        <li class="menu-item <?= str_contains($currentUri, '/reports/gst') ? 'active' : '' ?>">
            <a href="<?= url('/reports/gst') ?>" class="menu-link text-warning">
                <i class="bi bi-shield-check"></i>
                <span>GST Reports (GSTR-1/3B)</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-header">Settings & SaaS</li>
        <li class="menu-item <?= ($currentUri === '/settings' || str_contains($currentUri, '/settings/invoice') || str_contains($currentUri, '/settings/users')) ? 'active' : '' ?>">
            <a href="<?= url('/settings') ?>" class="menu-link">
                <i class="bi bi-gear"></i>
                <span>Company & Print Setup</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/settings/bill-designs') ? 'active' : '' ?>">
            <a href="<?= url('/settings/bill-designs') ?>" class="menu-link">
                <i class="bi bi-palette text-primary"></i>
                <span>Bill Designs</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/subscription') ? 'active' : '' ?>">
            <a href="<?= url('/subscription/plans') ?>" class="menu-link">
                <i class="bi bi-award"></i>
                <span>Subscription & License</span>
            </a>
        </li>
        <li class="menu-item <?= str_contains($currentUri, '/backup') ? 'active' : '' ?>">
            <a href="<?= url('/backup') ?>" class="menu-link">
                <i class="bi bi-cloud-arrow-down"></i>
                <span>Backup & Restore</span>
            </a>
        </li>

        <?php if ($isSuperAdmin): ?>
        <li class="menu-header text-danger">Super Admin</li>
        <li class="menu-item <?= str_contains($currentUri, '/admin') ? 'active' : '' ?>">
            <a href="<?= url('/admin') ?>" class="menu-link text-danger">
                <i class="bi bi-shield-lock"></i>
                <span>Super Admin Panel</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>
