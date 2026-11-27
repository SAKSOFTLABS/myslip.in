<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bag-check me-2 text-warning"></i> Purchase Bills</h4>
        <p class="text-muted small mb-0">Record inward supplier goods, inventory stock updates, and payables</p>
    </div>
    <a href="<?= url('/purchases/create') ?>" class="btn btn-warning shadow-sm fw-bold">
        <i class="bi bi-plus-lg me-1"></i> New Purchase Bill
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        <form action="<?= url('/purchases') ?>" method="GET" class="d-flex flex-wrap gap-2 w-100">
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control" placeholder="Search purchase #, vendor..." value="<?= e($search ?? '') ?>" autofocus>
            </div>

            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light">Dates</span>
                <input type="date" name="start_date" class="form-control" value="<?= e($startDate ?? '') ?>">
                <input type="date" name="end_date" class="form-control" value="<?= e($endDate ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if (!empty($search) || !empty($startDate) || !empty($endDate)): ?>
                <a href="<?= url('/purchases') ?>" class="btn btn-sm btn-light border">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Purchase #</th>
                        <th>Supplier Inv #</th>
                        <th>Date</th>
                        <th>Vendor Name</th>
                        <th class="text-end">Taxable</th>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <th class="text-end">Total Tax</th>
                        <?php endif; ?>
                        <th class="text-end">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-bag-x fs-3 d-block mb-1"></i>
                                No purchase bills recorded yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $p): ?>
                            <tr class="<?= $p['status'] === 'cancelled' ? 'table-danger opacity-75' : '' ?>">
                                <td>
                                    <a href="<?= url('/purchases/view/' . $p['id']) ?>" class="fw-bold text-warning text-decoration-none">
                                        <?= e($p['purchase_number']) ?>
                                    </a>
                                </td>
                                <td><?= e($p['supplier_invoice_number'] ?: '-') ?></td>
                                <td><?= formatDate($p['purchase_date']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($p['vendor_name']) ?></div>
                                    <?php if ($p['vendor_phone']): ?>
                                        <div class="text-muted small"><?= e($p['vendor_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= formatCurrency($p['taxable_amount']) ?></td>
                                <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                    <td class="text-end"><?= formatCurrency($p['total_tax_amount']) ?></td>
                                <?php endif; ?>
                                <td class="text-end fw-bold"><?= formatCurrency($p['grand_total']) ?></td>
                                <td class="text-center">
                                    <?php if ($p['status'] === 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-<?= $p['payment_status'] === 'paid' ? 'success' : ($p['payment_status'] === 'partial' ? 'warning' : 'danger') ?>-subtle text-<?= $p['payment_status'] === 'paid' ? 'success' : ($p['payment_status'] === 'partial' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($p['payment_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/purchases/view/' . $p['id']) ?>" class="btn btn-outline-primary" title="View Bill">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('/purchases/edit/' . $p['id']) ?>" class="btn btn-outline-success" title="Edit Bill">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Delete Bill" onclick="confirmDeletePurchase(<?= $p['id'] ?>, '<?= e($p['purchase_number']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Purchase Bill Modal -->
<div class="modal fade" id="deletePurchaseModal" tabindex="-1" aria-labelledby="deletePurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title fs-6 fw-bold" id="deletePurchaseModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Delete Purchase Bill
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deletePurchaseForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body p-3">
                    <p class="mb-2">Are you sure you want to permanently delete Purchase Bill <strong id="deletePurchaseNum" class="text-danger"></strong>?</p>
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Line items, inward stock movements, and ledger postings will be permanently removed. This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-bold px-3">
                        <i class="bi bi-trash me-1"></i> Delete Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeletePurchase(id, purchaseNumber) {
    document.getElementById('deletePurchaseNum').textContent = purchaseNumber;
    document.getElementById('deletePurchaseForm').action = '<?= url('/purchases/delete/') ?>' + id;
    new bootstrap.Modal(document.getElementById('deletePurchaseModal')).show();
}
</script>
