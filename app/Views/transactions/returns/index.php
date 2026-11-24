<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-arrow-left-right me-2 text-primary"></i> Returns & Credit/Debit Notes</h4>
        <p class="text-muted small mb-0">Record customer sales returns and vendor purchase returns with automated stock & tax adjustments</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/returns/sales/create') ?>" class="btn btn-primary-custom shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> New Sales Return (Credit Note)
        </a>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="returnTabs">
    <li class="nav-item">
        <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#salesReturnTab">
            <i class="bi bi-box-arrow-in-down-left text-primary me-1"></i> Sales Returns (Credit Notes)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold" data-bs-toggle="tab" href="#purchaseReturnTab">
            <i class="bi bi-box-arrow-up-right text-warning me-1"></i> Purchase Returns (Debit Notes)
        </a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="salesReturnTab">
        <div class="card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Reason</th>
                                <th class="text-end">Taxable Amount</th>
                                <th class="text-end">Total Refund / Credit</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($salesReturns)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted small">No sales returns recorded.</td></tr>
                            <?php else: ?>
                                <?php foreach ($salesReturns as $sr): ?>
                                    <tr>
                                        <td><strong><?= e($sr['return_number']) ?></strong></td>
                                        <td><?= formatDate($sr['return_date']) ?></td>
                                        <td><?= e($sr['customer_name']) ?></td>
                                        <td><?= e($sr['reason'] ?: '-') ?></td>
                                        <td class="text-end"><?= formatCurrency($sr['taxable_amount']) ?></td>
                                        <td class="text-end fw-bold text-danger"><?= formatCurrency($sr['grand_total']) ?></td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="purchaseReturnTab">
        <div class="card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Date</th>
                                <th>Vendor</th>
                                <th>Reason</th>
                                <th class="text-end">Taxable Amount</th>
                                <th class="text-end">Total Debit Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($purchaseReturns)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted small">No purchase returns recorded.</td></tr>
                            <?php else: ?>
                                <?php foreach ($purchaseReturns as $pr): ?>
                                    <tr>
                                        <td><strong><?= e($pr['return_number']) ?></strong></td>
                                        <td><?= formatDate($pr['return_date']) ?></td>
                                        <td><?= e($pr['vendor_name']) ?></td>
                                        <td><?= e($pr['reason'] ?: '-') ?></td>
                                        <td class="text-end"><?= formatCurrency($pr['taxable_amount']) ?></td>
                                        <td class="text-end fw-bold text-success"><?= formatCurrency($pr['grand_total']) ?></td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
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
