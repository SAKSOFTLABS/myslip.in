<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-text me-2 text-primary"></i> Cash Book</h4>
        <p class="text-muted small mb-0">Record of daily cash inflows (receipts) and cash outflows (payments)</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Cash Book
        </button>
    </div>
</div>

<div class="card-custom mb-4 no-print">
    <div class="card-body p-3">
        <form action="<?= url('/reports/cash-book') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary w-100">Load Cash Book</button>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Voucher #</th>
                        <th>Type</th>
                        <th>Narration / Particulars</th>
                        <th class="text-end">Receipt (Dr Inflow)</th>
                        <th class="text-end">Payment (Cr Outflow)</th>
                        <th class="text-end">Closing Cash Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $runningBalance = $openingBalance;
                    ?>
                    <tr class="bg-light">
                        <td colspan="4"><strong>Opening Cash-in-Hand Balance</strong></td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold"><?= formatCurrency($runningBalance) ?></td>
                    </tr>

                    <?php if (empty($entries)): ?>
                        <tr><td colspan="7" class="text-center py-3 text-muted small">No cash movements in selected period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($entries as $e): 
                            $debit = (float)$e['debit'];
                            $credit = (float)$e['credit'];
                            $runningBalance += ($debit - $credit);
                        ?>
                            <tr>
                                <td><?= formatDate($e['voucher_date']) ?></td>
                                <td><strong><?= e($e['voucher_number']) ?></strong></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= ucfirst($e['voucher_type']) ?></span></td>
                                <td><?= e($e['narration']) ?></td>
                                <td class="text-end text-success fw-bold"><?= $debit > 0 ? formatCurrency($debit, false) : '-' ?></td>
                                <td class="text-end text-danger fw-bold"><?= $credit > 0 ? formatCurrency($credit, false) : '-' ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($runningBalance) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="6" class="text-end text-white">Closing Cash-in-Hand Balance:</td>
                        <td class="text-end text-white fs-6"><?= formatCurrency($runningBalance) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
