<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2 text-primary"></i> Reports & Analytics</h4>
        <p class="text-muted small mb-0">Financial, operational, tax, and inventory reporting suite</p>
    </div>
</div>

<div class="row g-4">
    <!-- Sales & Purchases -->
    <div class="col-md-6 col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold text-primary"><i class="bi bi-receipt me-2"></i> Sales & Revenue</span></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/sales') ?>" class="text-decoration-none fw-semibold">Sales Register (Bill-Wise)</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/item-profit') ?>" class="text-decoration-none fw-semibold">Item Gross Profit Report</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Accounting & Financials -->
    <div class="col-md-6 col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold text-success"><i class="bi bi-journal-check me-2"></i> Financial Books</span></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/cash-book') ?>" class="text-decoration-none fw-semibold">Cash Book (Daily Flow)</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/trial-balance') ?>" class="text-decoration-none fw-semibold">Trial Balance</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/profit-loss') ?>" class="text-decoration-none fw-semibold">Profit & Loss Statement</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- GST Compliance -->
    <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold text-warning"><i class="bi bi-shield-check me-2"></i> GST Compliance</span></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/gst/gstr1') ?>" class="text-decoration-none fw-semibold">GSTR-1 Outward Supplies</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="<?= url('/reports/gst/gstr3b') ?>" class="text-decoration-none fw-semibold">GSTR-3B Tax Summary</a>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
