<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-star-fill me-2 text-warning"></i> Subscription & SaaS Plans</h4>
        <p class="text-muted small mb-0">Manage your subscription, plan limits, and billing cycle</p>
    </div>
</div>

<!-- Active Subscription Status Banner -->
<div class="card-custom mb-4 bg-light border">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-success-subtle text-success text-uppercase px-2 py-1 mb-2">Active Subscription</span>
                <h4 class="fw-bold mb-1"><?= e($subscription['plan_name'] ?? '30-Day Free Trial') ?> Plan</h4>
                <div class="text-muted small">
                    Valid from <strong><?= formatDate($subscription['start_date'] ?? date('Y-m-d')) ?></strong> to <strong><?= formatDate($subscription['end_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?></strong>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="display-6 fw-bold text-primary mb-0"><?= $daysRemaining ?></div>
                <div class="small text-muted">Days Remaining in Cycle</div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Tier Cards -->
<h5 class="fw-bold mb-3">Available Upgrade Plans</h5>
<div class="row g-4">
    <?php foreach ($plans as $p): 
        $isCurrent = ($subscription['plan_id'] ?? 0) == $p['id'];
    ?>
        <div class="col-md-4">
            <div class="card-custom h-100 <?= $p['is_popular'] ? 'border-primary shadow-sm' : '' ?>">
                <?php if ($p['is_popular']): ?>
                    <div class="bg-primary text-white text-center py-1 small fw-bold">MOST POPULAR</div>
                <?php endif; ?>
                <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1"><?= e($p['name']) ?></h5>
                        <div class="my-3">
                            <span class="display-6 fw-bold">₹<?= number_format((float)$p['price']) ?></span>
                            <span class="text-muted small">/ <?= e($p['billing_cycle']) ?></span>
                        </div>
                        <hr>
                        <ul class="list-unstyled text-start small mb-4">
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> <?= !empty($p['max_invoices_month']) ? number_format((float)$p['max_invoices_month']) . ' Invoices / month' : 'Unlimited Invoices' ?></li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> <?= !empty($p['max_items']) ? number_format((float)$p['max_items']) . ' Products & Items' : 'Unlimited Products' ?></li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> <?= $p['max_users'] ?> User Accounts</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> GST & Non-GST Dynamic Mode</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> A4, A5 & 3-Inch Thermal Print</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Double-Entry Accounting Engine</li>
                        </ul>
                    </div>

                    <?php if ($isCurrent): ?>
                        <button class="btn btn-light border w-100 fw-bold" disabled>Current Active Plan</button>
                    <?php else: ?>
                        <form action="<?= url('/subscription/upgrade/' . $p['id']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="billing_cycle" value="yearly">
                            <button type="submit" class="btn btn-<?= $p['is_popular'] ? 'primary' : 'outline-primary' ?> w-100 fw-bold">
                                Upgrade to <?= e($p['name']) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
