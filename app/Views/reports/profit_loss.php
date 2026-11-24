<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-pie-chart me-2 text-primary"></i> Profit & Loss Statement</h4>
        <p class="text-muted small mb-0">Financial statement showing business revenues, expenses, and net profit</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print P&L
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Incomes (Right Side / Revenue) -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header bg-success-subtle"><span class="fw-bold text-success"><i class="bi bi-arrow-down-left-circle me-1"></i> Income & Revenues</span></div>
            <div class="card-body p-0">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Particulars</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totIncome = 0;
                        if (empty($incomes)): ?>
                            <tr><td colspan="2" class="text-center py-3 text-muted small">No income recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($incomes as $inc): 
                                $val = (float)$inc['balance'];
                                if ($val <= 0) continue;
                                $totIncome += $val;
                            ?>
                                <tr>
                                    <td><?= e($inc['account_name']) ?></td>
                                    <td class="text-end fw-semibold"><?= formatCurrency($val, false) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Income:</td>
                            <td class="text-end text-success fs-6"><?= formatCurrency($totIncome) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Expenses (Left Side / Costs) -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header bg-danger-subtle"><span class="fw-bold text-danger"><i class="bi bi-arrow-up-right-circle me-1"></i> Expenses & Costs</span></div>
            <div class="card-body p-0">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Particulars</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totExpense = 0;
                        if (empty($expenses)): ?>
                            <tr><td colspan="2" class="text-center py-3 text-muted small">No expenses recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $exp): 
                                $val = (float)$exp['balance'];
                                if ($val <= 0) continue;
                                $totExpense += $val;
                            ?>
                                <tr>
                                    <td><?= e($exp['account_name']) ?></td>
                                    <td class="text-end fw-semibold"><?= formatCurrency($val, false) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Expenses:</td>
                            <td class="text-end text-danger fs-6"><?= formatCurrency($totExpense) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Net Profit / Loss Banner -->
<div class="mt-4 p-4 rounded-3 border text-center <?= ($totIncome - $totExpense) >= 0 ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger' ?>">
    <h5 class="fw-bold mb-1">
        <?= ($totIncome - $totExpense) >= 0 ? '<i class="bi bi-check-circle-fill me-1"></i> Net Business Profit' : '<i class="bi bi-exclamation-octagon-fill me-1"></i> Net Business Loss' ?>
    </h5>
    <h2 class="display-6 fw-bold mb-0"><?= formatCurrency(abs($totIncome - $totExpense)) ?></h2>
</div>
