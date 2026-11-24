<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-tags me-2 text-primary"></i> SaaS Subscription Plans</h4>
        <p class="text-muted small mb-0">Manage plan pricing, feature limits, and invoice allowances</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($plans as $p): ?>
        <div class="col-md-6">
            <div class="card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5 text-primary"><?= e($p['name']) ?> Plan</span>
                    <span class="badge bg-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= $p['status'] === 'active' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($p['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/plans/update/' . $p['id']) ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Price (₹)</label>
                                <input type="number" name="price" class="form-control" step="0.01" value="<?= e($p['price']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Billing Cycle</label>
                                <input type="text" class="form-control bg-light" value="<?= ucfirst($p['billing_cycle']) ?>" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Max Invoices / Month (0 = Unlimited)</label>
                                <input type="number" name="max_invoices_month" class="form-control" value="<?= e($p['max_invoices_month'] ?? 0) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Max Catalog Items (0 = Unlimited)</label>
                                <input type="number" name="max_items" class="form-control" value="<?= e($p['max_items'] ?? 0) ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="bi bi-save me-1"></i> Update Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
