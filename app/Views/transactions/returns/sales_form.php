<?php
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-box-arrow-in-down-left me-1"></i> New Sales Return (Credit Note)</h4>
        <span class="text-muted small">Restores item stock and issues customer credit against sales</span>
    </div>
    <a href="<?= url('/returns') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Returns
    </a>
</div>

<form action="<?= url('/returns/sales/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="card-custom mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select form-select-sm" required autofocus>
                        <option value="">-- Select Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['mobile'] ?: 'No Phone') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Return Date <span class="text-danger">*</span></label>
                    <input type="date" name="return_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Reason for Return</label>
                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Defective piece, Customer changed mind">
                </div>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="card-custom mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Returned Items</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBillingRow()">
                <i class="bi bi-plus-lg me-1"></i> Add Item Line
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table billing-grid-table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Item</th>
                            <th class="text-center" style="width: 140px;">Quantity Returned</th>
                            <th class="text-end" style="width: 140px;">Refund Rate (₹)</th>
                            <th class="text-end" style="width: 140px;">Taxable (₹)</th>
                            <th class="text-end" style="width: 140px;">Total Refund (₹)</th>
                            <th class="text-center" style="width: 50px;"><i class="bi bi-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody id="billingItemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted">Total Refund Amount:</span>
            <span class="fs-4 fw-bold text-danger ms-2">₹ <span id="displayGrandTotal">0.00</span></span>
        </div>
        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
            <i class="bi bi-check2-circle me-1"></i> Save Sales Return
        </button>
    </div>
</form>

<script src="<?= asset('js/billing.js') ?>"></script>
<script>
window.AVAILABLE_ITEMS = <?= json_encode($items) ?>;
window.GST_ENABLED = <?= $gstEnabled ? 'true' : 'false' ?>;
window.IS_INTERSTATE = false;
window.IS_PURCHASE = false;

document.addEventListener('DOMContentLoaded', function() {
    addBillingRow();
});
</script>
