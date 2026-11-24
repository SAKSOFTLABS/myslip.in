<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-bookmark me-2 text-primary"></i> Chart of Accounts</h4>
        <p class="text-muted small mb-0">Assets, Liabilities, Incomes, Expenses, Cash/Bank accounts, and live ledger balances</p>
    </div>
    <a href="<?= url('/accounts/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Account
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Group / Category</th>
                        <th class="text-center">Account Type</th>
                        <th class="text-end">Total Debit (Dr)</th>
                        <th class="text-end">Total Credit (Cr)</th>
                        <th class="text-end">Net Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $a): 
                            $debit = (float)$a['total_debit'] + (($a['balance_type'] === 'DR') ? (float)$a['opening_balance'] : 0);
                            $credit = (float)$a['total_credit'] + (($a['balance_type'] === 'CR') ? (float)$a['opening_balance'] : 0);
                            $net = $debit - $credit;
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/accounts/ledger/' . $a['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($a['account_name']) ?>
                                    </a>
                                    <?php if ($a['is_cash']): ?><span class="badge bg-success-subtle text-success ms-1">Cash</span><?php endif; ?>
                                    <?php if ($a['is_bank']): ?><span class="badge bg-info-subtle text-info ms-1">Bank</span><?php endif; ?>
                                    <?php if ($a['is_system']): ?><span class="badge bg-light text-muted border ms-1">System</span><?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= e($a['group_name']) ?></div>
                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size: 0.68rem;"><?= e($a['category']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        <?= $a['is_cash'] ? 'Cash A/c' : ($a['is_bank'] ? 'Bank A/c' : 'Ledger A/c') ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= $debit > 0 ? formatCurrency($debit, false) : '-' ?></td>
                                <td class="text-end"><?= $credit > 0 ? formatCurrency($credit, false) : '-' ?></td>
                                <td class="text-end fw-bold">
                                    <span class="text-<?= $net >= 0 ? 'dark' : 'danger' ?>">
                                        <?= formatCurrency(abs($net)) ?> <?= $net >= 0 ? 'Dr' : 'Cr' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/accounts/ledger/' . $a['id']) ?>" class="btn btn-outline-info" title="Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        <a href="<?= url('/accounts/edit/' . $a['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (!$a['is_system']): ?>
                                            <a href="<?= url('/accounts/delete/' . $a['id']) ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this account?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
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
