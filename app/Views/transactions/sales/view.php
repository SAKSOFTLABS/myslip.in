<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Invoice: <?= e($sale['invoice_number']) ?></h4>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-<?= $sale['status'] === 'active' ? 'success' : 'danger' ?> text-uppercase">
                <?= ucfirst($sale['status']) ?>
            </span>
            <span class="text-muted small">Dated: <?= formatDate($sale['invoice_date']) ?></span>
            <?php if ($sale['status'] === 'cancelled'): ?>
                <span class="text-danger small fw-bold">(Reason: <?= e($sale['cancelled_reason']) ?>)</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
        <a href="<?= url('/invoices/print/' . $sale['id']) ?>" class="btn btn-dark" target="_blank">
            <i class="bi bi-printer me-1"></i> Print / PDF
        </a>
        <a href="https://api.whatsapp.com/send?phone=91<?= e($sale['customer_mobile']) ?>&text=<?= urlencode("Hello " . $sale['customer_name'] . ", please find your Invoice " . $sale['invoice_number'] . " for amount " . formatCurrency($sale['grand_total']) . ". Thank you!") ?>" target="_blank" class="btn btn-success">
            <i class="bi bi-whatsapp me-1"></i> WhatsApp
        </a>
        <?php if ($sale['status'] === 'active'): ?>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Invoice
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold">Billed To (Customer)</span></div>
            <div class="card-body">
                <h5 class="fw-bold text-dark mb-1"><?= e($sale['customer_name']) ?></h5>
                <div class="text-muted small mb-1"><i class="bi bi-phone me-1"></i> <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i> <?= e($sale['place_of_supply']) ?> (State Code: <?= e($sale['customer_state_code']) ?>)</div>
                <div class="text-muted small"><i class="bi bi-shield-check me-1"></i> GSTIN: <?= e($sale['customer_gstin'] ?: 'Unregistered') ?></div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header"><span class="fw-bold">Payment & Settlement Status</span></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Mode:</span>
                    <span class="fw-bold text-uppercase"><?= e($sale['payment_mode']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid Amount:</span>
                    <span class="fw-bold text-success"><?= formatCurrency($sale['paid_amount']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Outstanding Balance:</span>
                    <span class="fw-bold text-danger"><?= formatCurrency($sale['balance_amount']) ?></span>
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
                        <th class="text-end">Rate</th>
                        <th class="text-end">Discount</th>
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
                    foreach ($sale['items'] as $item): 
                        $lineTax = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
                    ?>
                        <tr>
                            <td class="text-center"><?= $count++ ?></td>
                            <td>
                                <div class="fw-bold"><?= e($item['item_name']) ?></div>
                                <?php if ($item['barcode']): ?><span class="badge bg-light text-muted border"><?= e($item['barcode']) ?></span><?php endif; ?>
                            </td>
                            <td><?= e($item['hsn_sac'] ?: '-') ?></td>
                            <td class="text-center fw-bold"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                            <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                            <td class="text-end"><?= (float)$item['discount_amount'] > 0 ? formatCurrency($item['discount_amount'], false) : '-' ?></td>
                            <td class="text-end"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                            <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                <td class="text-center"><span class="badge bg-primary-subtle text-primary"><?= (float)$item['gst_rate'] ?>%</span></td>
                                <td class="text-end"><?= formatCurrency($lineTax, false) ?></td>
                            <?php endif; ?>
                            <td class="text-end fw-bold"><?= formatCurrency($item['total_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<?= \App\Core\Session::get('gst_enabled', true) ? '9' : '7' ?>" class="text-end fw-bold">Taxable Total:</td>
                        <td class="text-end fw-bold"><?= formatCurrency($sale['taxable_amount']) ?></td>
                    </tr>
                    <?php if (\App\Core\Session::get('gst_enabled', true) && (float)$sale['total_tax_amount'] > 0): ?>
                        <?php if ($sale['is_interstate']): ?>
                            <tr>
                                <td colspan="9" class="text-end text-muted">Integrated GST (IGST):</td>
                                <td class="text-end text-primary fw-bold"><?= formatCurrency($sale['igst_amount']) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-end text-muted">Central GST (CGST):</td>
                                <td class="text-end text-primary"><?= formatCurrency($sale['cgst_amount']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end text-muted">State GST (SGST):</td>
                                <td class="text-end text-primary"><?= formatCurrency($sale['sgst_amount']) ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="<?= \App\Core\Session::get('gst_enabled', true) ? '9' : '7' ?>" class="text-end text-muted">Round Off:</td>
                        <td class="text-end"><?= formatCurrency($sale['round_off']) ?></td>
                    </tr>
                    <tr class="table-dark fs-5 fw-bold">
                        <td colspan="<?= \App\Core\Session::get('gst_enabled', true) ? '9' : '7' ?>" class="text-end text-white">Grand Total:</td>
                        <td class="text-end text-success"><?= formatCurrency($sale['grand_total']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Invoice Modal -->
<?php if ($sale['status'] === 'active'): ?>
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/sales/cancel/' . $sale['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Cancel Sales Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Cancelling will reverse all deducted inventory stock movements and mark ledger entries as cancelled.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Cancellation <span class="text-danger">*</span></label>
                        <input type="text" name="cancelled_reason" class="form-control" placeholder="e.g. Customer cancelled order, wrong price billed" required>
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
