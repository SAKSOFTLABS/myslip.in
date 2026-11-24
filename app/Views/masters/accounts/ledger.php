<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-text me-2 text-primary"></i> General Ledger: <?= e($account['account_name']) ?></h4>
        <p class="text-muted small mb-0">Double-entry audit trail for this account</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Ledger
        </button>
        <a href="<?= url('/accounts') ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Accounts
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher Type</th>
                        <th>Voucher #</th>
                        <th>Narration / Particulars</th>
                        <th class="text-end">Debit (Dr)</th>
                        <th class="text-end">Credit (Cr)</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $runningBalance = ($account['balance_type'] === 'DR') ? (float)$account['opening_balance'] : -(float)$account['opening_balance'];
                    ?>
                    <tr class="bg-light">
                        <td colspan="4"><strong>Opening Balance</strong></td>
                        <td class="text-end"><?= ($account['balance_type'] === 'DR' && (float)$account['opening_balance'] > 0) ? formatCurrency($account['opening_balance'], false) : '-' ?></td>
                        <td class="text-end"><?= ($account['balance_type'] === 'CR' && (float)$account['opening_balance'] > 0) ? formatCurrency($account['opening_balance'], false) : '-' ?></td>
                        <td class="text-end fw-bold"><?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Dr' : 'Cr' ?></td>
                    </tr>

                    <?php if (empty($ledgerEntries)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted small">No transactions posted to this ledger account yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ledgerEntries as $entry): 
                            $debit = (float)$entry['debit'];
                            $credit = (float)$entry['credit'];
                            $runningBalance += ($debit - $credit);
                        ?>
                            <tr>
                                <td><?= formatDate($entry['voucher_date']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= ucfirst(str_replace('_', ' ', $entry['voucher_type'])) ?></span></td>
                                <td><strong><?= e($entry['voucher_number']) ?></strong></td>
                                <td><?= e($entry['narration']) ?></td>
                                <td class="text-end"><?= $debit > 0 ? formatCurrency($debit, false) : '-' ?></td>
                                <td class="text-end"><?= $credit > 0 ? formatCurrency($credit, false) : '-' ?></td>
                                <td class="text-end fw-bold text-<?= $runningBalance >= 0 ? 'dark' : 'danger' ?>">
                                    <?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Dr' : 'Cr' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="6" class="text-end">Final Closing Balance:</td>
                        <td class="text-end fs-6 text-white"><?= formatCurrency(abs($runningBalance)) ?> <?= $runningBalance >= 0 ? 'Dr' : 'Cr' ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
