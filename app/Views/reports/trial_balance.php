<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-scale me-2 text-primary"></i> Trial Balance</h4>
        <p class="text-muted small mb-0">Double-entry statement verifying Total Debits equal Total Credits</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Trial Balance
        </button>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Account Name</th>
                        <th>Category</th>
                        <th class="text-end" style="width: 220px;">Debit Balance (Dr) ₹</th>
                        <th class="text-end" style="width: 220px;">Credit Balance (Cr) ₹</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalDr = 0.00;
                    $totalCr = 0.00;

                    foreach ($accounts as $a):
                        $openingDr = ($a['balance_type'] === 'DR') ? (float)$a['opening_balance'] : 0.00;
                        $openingCr = ($a['balance_type'] === 'CR') ? (float)$a['opening_balance'] : 0.00;

                        $totDr = $openingDr + (float)$a['period_debit'];
                        $totCr = $openingCr + (float)$a['period_credit'];

                        $net = $totDr - $totCr;
                        $displayDr = 0.00;
                        $displayCr = 0.00;

                        if ($net > 0) {
                            $displayDr = $net;
                        } elseif ($net < 0) {
                            $displayCr = abs($net);
                        }

                        // Skip zero accounts
                        if ($displayDr == 0 && $displayCr == 0) continue;

                        $totalDr += $displayDr;
                        $totalCr += $displayCr;
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($a['account_name']) ?></strong>
                                <span class="text-muted small ms-2">(<?= e($a['group_name']) ?>)</span>
                            </td>
                            <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;"><?= e($a['category']) ?></span></td>
                            <td class="text-end"><?= $displayDr > 0 ? formatCurrency($displayDr, false) : '-' ?></td>
                            <td class="text-end"><?= $displayCr > 0 ? formatCurrency($displayCr, false) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-dark fw-bold fs-6">
                    <tr>
                        <td colspan="2" class="text-end text-white">Trial Balance Total:</td>
                        <td class="text-end text-white"><?= formatCurrency($totalDr) ?></td>
                        <td class="text-end text-white"><?= formatCurrency($totalCr) ?></td>
                    </tr>
                    <tr class="<?= abs($totalDr - $totalCr) < 0.02 ? 'table-success text-success' : 'table-danger text-danger' ?>">
                        <td colspan="4" class="text-center py-2">
                            <?php if (abs($totalDr - $totalCr) < 0.02): ?>
                                <i class="bi bi-check2-circle me-1"></i> Trial Balance is Perfectly Balanced! (Total Debit == Total Credit)
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Difference: <?= formatCurrency(abs($totalDr - $totalCr)) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
