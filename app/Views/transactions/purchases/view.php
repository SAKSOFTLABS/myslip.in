<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Purchase: <?= e($purchase['purchase_number']) ?></h4>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-<?= $purchase['status'] === 'active' ? 'warning text-dark' : 'danger' ?> text-uppercase">
                <?= ucfirst($purchase['status']) ?>
            </span>
            <span class="text-muted small">Dated: <?= formatDate($purchase['purchase_date']) ?></span>
            <?php if ($purchase['supplier_invoice_number']): ?>
                <span class="badge bg-light text-dark border">Supplier Inv: <?= e($purchase['supplier_invoice_number']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/purchases') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bills
        </a>
        <?php if ($purchase['status'] === 'active'): ?>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPurchaseModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Purchase Bill
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold">Vendor / Supplier Details</span></div>
            <div class="card-body">
                <h5 class="fw-bold text-dark mb-1"><?= e($purchase['vendor_name']) ?></h5>
                <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i> <?= e($purchase['vendor_state']) ?> (State Code: <?= e($purchase['vendor_state_code']) ?>)</div>
                <div class="text-muted small"><i class="bi bi-shield-check me-1"></i> GSTIN: <?= e($purchase['vendor_gstin'] ?: 'Unregistered') ?></div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold">Payment & Settlement</span></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Mode:</span>
                    <span class="fw-bold text-uppercase"><?= e($purchase['payment_mode']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid Amount:</span>
                    <span class="fw-bold text-success"><?= formatCurrency($purchase['paid_amount']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Balance Payable:</span>
                    <span class="fw-bold text-danger"><?= formatCurrency($purchase['balance_amount']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Table -->
<div class="card-custom mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Item Description</th>
                        <th>HSN/SAC</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Cost Rate</th>
                        <th class="text-end">Taxable</th>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <th class="text-center">GST %</th>
                            <th class="text-end">Tax Amount</th>
                        <?php endif; ?>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    foreach ($purchase['items'] as $item): 
                        $lineTax = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
                    ?>
                        <tr>
                            <td class="text-center"><?= $count++ ?></td>
                            <td><strong><?= e($item['item_name']) ?></strong></td>
                            <td><?= e($item['hsn_sac'] ?: '-') ?></td>
                            <td class="text-center fw-bold"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                            <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                            <td class="text-end"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                            <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                <td class="text-center"><span class="badge bg-warning-subtle text-dark"><?= (float)$item['gst_rate'] ?>%</span></td>
                                <td class="text-end"><?= formatCurrency($lineTax, false) ?></td>
                            <?php endif; ?>
                            <td class="text-end fw-bold"><?= formatCurrency($item['total_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<?= \App\Core\Session::get('gst_enabled', true) ? '8' : '6' ?>" class="text-end fw-bold">Taxable Amount:</td>
                        <td class="text-end fw-bold"><?= formatCurrency($purchase['taxable_amount']) ?></td>
                    </tr>
                    <?php if (\App\Core\Session::get('gst_enabled', true) && (float)$purchase['total_tax_amount'] > 0): ?>
                        <tr>
                            <td colspan="<?= $purchase['is_interstate'] ? '8' : '8' ?>" class="text-end text-muted">Input GST Total:</td>
                            <td class="text-end text-warning fw-bold"><?= formatCurrency($purchase['total_tax_amount']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="table-dark fs-5 fw-bold">
                        <td colspan="<?= \App\Core\Session::get('gst_enabled', true) ? '8' : '6' ?>" class="text-end text-white">Grand Total:</td>
                        <td class="text-end text-success"><?= formatCurrency($purchase['grand_total']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Purchase Modal -->
<?php if ($purchase['status'] === 'active'): ?>
<div class="modal fade" id="cancelPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/purchases/cancel/' . $purchase['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Cancel Purchase Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Cancelling will reverse all added inward inventory stock and reverse vendor credit from accounting ledgers.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Cancellation <span class="text-danger">*</span></label>
                        <input type="text" name="cancelled_reason" class="form-control" placeholder="e.g. Duplicate entry, supplier cancelled invoice" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger fw-bold">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
