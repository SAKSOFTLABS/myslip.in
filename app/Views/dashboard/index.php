<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Business Overview</h4>
        <p class="text-muted small mb-0">Summary of billing, inventory, payments, and profit for <?= e(\App\Core\Session::get('company_name')) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/sales/create') ?>" class="btn btn-primary-custom shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Create Sale <span class="kbd-shortcut ms-1">F2</span>
        </a>
        <a href="<?= url('/purchases/create') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-bag-plus me-1"></i> Add Purchase
        </a>
    </div>
</div>

<!-- KPI Cards Row 1 -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value text-primary"><?= formatCurrency($stats['todaySales']) ?></div>
            </div>
            <div class="stat-icon primary">
                <i class="bi bi-currency-rupee"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">This Month Sales</div>
                <div class="stat-value text-success"><?= formatCurrency($stats['monthSales']) ?></div>
            </div>
            <div class="stat-icon success">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Receivables (Outstanding)</div>
                <div class="stat-value text-danger"><?= formatCurrency($stats['receivables']) ?></div>
            </div>
            <div class="stat-icon danger">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Stock Asset Value</div>
                <div class="stat-value text-info"><?= formatCurrency($stats['stockValue']) ?></div>
            </div>
            <div class="stat-icon info">
                <i class="bi bi-boxes"></i>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Today's Purchases</div>
                <div class="stat-value"><?= formatCurrency($stats['todayPurchase']) ?></div>
            </div>
            <div class="stat-icon warning">
                <i class="bi bi-cart-check"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Month Purchases</div>
                <div class="stat-value"><?= formatCurrency($stats['monthPurchase']) ?></div>
            </div>
            <div class="stat-icon warning">
                <i class="bi bi-bag"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Payables (To Vendors)</div>
                <div class="stat-value text-warning"><?= formatCurrency($stats['payables']) ?></div>
            </div>
            <div class="stat-icon warning">
                <i class="bi bi-credit-card"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div>
                <div class="stat-label">Est. Month Gross Profit</div>
                <div class="stat-value text-success"><?= formatCurrency($stats['monthProfit']) ?></div>
            </div>
            <div class="stat-icon success">
                <i class="bi bi-piggy-bank"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Low Stock Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-custom h-100 mb-0">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Monthly Sales vs Purchases (Last 6 Months)</span>
            </div>
            <div class="card-body">
                <canvas id="salesTrendChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-custom h-100 mb-0">
            <div class="card-header">
                <span class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock Alerts</span>
                <a href="<?= url('/items') ?>" class="small text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockItems)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                        All inventory items are above reorder levels!
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush small">
                        <?php foreach ($lowStockItems as $lItem): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <div class="fw-bold"><?= e($lItem['name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Reorder Level: <?= formatQty($lItem['reorder_level']) ?> <?= e($lItem['unit_name']) ?></div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    <?= formatQty($lItem['current_stock']) ?> <?= e($lItem['unit_name']) ?> Left
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices & Purchases Tables -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-custom mb-0">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-file-earmark-text text-primary me-1"></i> Recent Sales Invoices</span>
                <a href="<?= url('/sales') ?>" class="small text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentSales)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted small">No sales invoices recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentSales as $s): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('/sales/view/' . $s['id']) ?>" class="fw-bold text-decoration-none">
                                                <?= e($s['invoice_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= e($s['customer_name']) ?></td>
                                        <td><?= formatDate($s['invoice_date']) ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($s['grand_total']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $s['payment_status'] === 'paid' ? 'success' : ($s['payment_status'] === 'partial' ? 'warning' : 'danger') ?>-subtle text-<?= $s['payment_status'] === 'paid' ? 'success' : ($s['payment_status'] === 'partial' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($s['payment_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom mb-0">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-bag-check text-warning me-1"></i> Recent Purchases</span>
                <a href="<?= url('/purchases') ?>" class="small text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Purchase #</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPurchases)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted small">No purchase bills recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentPurchases as $p): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('/purchases/view/' . $p['id']) ?>" class="fw-bold text-decoration-none">
                                                <?= e($p['purchase_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= e($p['vendor_name']) ?></td>
                                        <td><?= formatDate($p['purchase_date']) ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($p['grand_total']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $p['payment_status'] === 'paid' ? 'success' : ($p['payment_status'] === 'partial' ? 'warning' : 'danger') ?>-subtle text-<?= $p['payment_status'] === 'paid' ? 'success' : ($p['payment_status'] === 'partial' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($p['payment_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart['labels']) ?>,
            datasets: [
                {
                    label: 'Sales (₹)',
                    data: <?= json_encode($chart['sales']) ?>,
                    backgroundColor: '#1d4ed8',
                    borderRadius: 4
                },
                {
                    label: 'Purchases (₹)',
                    data: <?= json_encode($chart['purchases']) ?>,
                    backgroundColor: '#f59e0b',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '₹' + val.toLocaleString('en-IN'); }
                    }
                }
            }
        }
    });
});
</script>
