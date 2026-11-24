<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack me-2 text-success"></i> Customer Receipts</h4>
        <p class="text-muted small mb-0">Record inward payments from customers and update ledger accounts</p>
    </div>
    <a href="<?= url('/vouchers/receipts/create') ?>" class="btn btn-success fw-bold shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Receipt Entry
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Received From (Customer)</th>
                        <th>Deposited Into</th>
                        <th>Mode</th>
                        <th>Ref / UTR / Cheque</th>
                        <th class="text-end">Amount Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted small">No receipts recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($receipts as $r): ?>
                            <tr>
                                <td><strong><?= e($r['receipt_number']) ?></strong></td>
                                <td><?= formatDate($r['receipt_date']) ?></td>
                                <td><strong><?= e($r['customer_name']) ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= e($r['deposit_account_name']) ?></span></td>
                                <td><span class="badge bg-secondary-subtle text-secondary text-uppercase"><?= e($r['payment_mode']) ?></span></td>
                                <td><?= e($r['utr_no'] ?: ($r['cheque_no'] ?: '-')) ?></td>
                                <td class="text-end fw-bold text-success"><?= formatCurrency($r['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
