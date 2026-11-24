<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-text me-2 text-primary"></i> Vendor Statement: <?= e($vendor['name']) ?></h4>
        <p class="text-muted small mb-0">Purchase bills, debit notes, payment vouchers, and running payable balance</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Statement
        </button>
        <a href="<?= url('/vendors') ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Directory
        </a>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small">Vendor Name</div>
                <div class="fw-bold fs-6"><?= e($vendor['name']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Mobile & Email</div>
                <div><?= e($vendor['mobile'] ?: '-') ?> | <?= e($vendor['email'] ?: '-') ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">GSTIN / State</div>
                <div><?= e($vendor['gstin'] ?: 'Unregistered') ?> (<?= e($vendor['state']) ?>)</div>
            </div>
            <div class="col-md-3 text-md-end">
                <div class="text-muted small">Credit Period</div>
                <div><?= $vendor['credit_days'] ?> Days</div>
            </div>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        <span class="fw-bold"><i class="bi bi-list-ul me-1"></i> General Ledger Entries</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher Type</th>
                        <th>Voucher #</th>
                        <th>Narration / Particulars</th>
                        <th class="text-end">Debit (Dr - Paid)</th>
                        <th class="text-end">Credit (Cr - Billed)</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $runningBalance = ($vendor['balance_type'] === 'CR') ? (float)$vendor['opening_balance'] : -(float)$vendor['opening_balance'];
                    ?>
                    <tr class="bg-light">
                        <td colspan="4"><strong>Opening Balance</strong></td>
                        <td class="text-end"><?= ($vendor['balance_type'] === 'DR' && (float)$vendor['opening_balance'] > 0) ? formatCurrency($vendor['opening_balance'], false) : '-' ?></td>
                        <td class="text-end"><?= ($vendor['balance_type'] === 'CR' && (float)$vendor['opening_balance'] > 0) ? formatCurrency($vendor['opening_balance'], false) : '-' ?></td>
                        <td class="text-end fw-bold"><?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Cr (Payable)' : 'Dr (Advance)' ?></td>
                    </tr>

                    <?php if (empty($ledgerEntries)): ?>
                        <tr><td colspan="7" class="text-center py-3 text-muted small">No financial ledger entries recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ledgerEntries as $entry): 
                            $debit = (float)$entry['debit'];
                            $credit = (float)$entry['credit'];
                            $runningBalance += ($credit - $debit);
                        ?>
                            <tr>
                                <td><?= formatDate($entry['voucher_date']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= ucfirst(str_replace('_', ' ', $entry['voucher_type'])) ?></span></td>
                                <td><strong><?= e($entry['voucher_number']) ?></strong></td>
                                <td><?= e($entry['narration']) ?></td>
                                <td class="text-end"><?= $debit > 0 ? formatCurrency($debit, false) : '-' ?></td>
                                <td class="text-end"><?= $credit > 0 ? formatCurrency($credit, false) : '-' ?></td>
                                <td class="text-end fw-bold text-<?= $runningBalance > 0 ? 'warning' : 'success' ?>">
                                    <?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Cr' : 'Dr' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="6" class="text-end">Final Closing Payable:</td>
                        <td class="text-end fs-6 text-white"><?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Cr (Payable)' : 'Dr (Advance)' ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
