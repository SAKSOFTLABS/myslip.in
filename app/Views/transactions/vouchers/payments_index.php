<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-credit-card-2-back me-2 text-primary"></i> Payment Vouchers</h4>
        <p class="text-muted small mb-0">Record outward payments to suppliers or expense accounts</p>
    </div>
    <a href="<?= url('/vouchers/payments/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Payment Entry
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Payment #</th>
                        <th>Date</th>
                        <th>Paid To (Party / Account)</th>
                        <th>Paid From (Cash / Bank)</th>
                        <th>Mode</th>
                        <th>Ref / UTR / Cheque</th>
                        <th class="text-end">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted small">No payment vouchers recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><strong><?= e($p['payment_number']) ?></strong></td>
                                <td><?= formatDate($p['payment_date']) ?></td>
                                <td><strong><?= e($p['vendor_name'] ?: $p['expense_account_name']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= e($p['paid_from_name']) ?></span></td>
                                <td><span class="badge bg-secondary-subtle text-secondary text-uppercase"><?= e($p['payment_mode']) ?></span></td>
                                <td><?= e($p['utr_no'] ?: ($p['cheque_no'] ?: '-')) ?></td>
                                <td class="text-end fw-bold text-danger"><?= formatCurrency($p['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
