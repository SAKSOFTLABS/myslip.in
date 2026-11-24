<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people me-2 text-primary"></i> Customer Directory</h4>
        <p class="text-muted small mb-0">Manage customer contacts, GSTIN, credit limits, and view live ledger balances</p>
    </div>
    <a href="<?= url('/customers/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Add New Customer
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Mobile / Email</th>
                        <th>State / City</th>
                        <th>GSTIN</th>
                        <th class="text-end">Credit Limit</th>
                        <th class="text-end">Outstanding Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-3 d-block mb-1"></i>
                                No customers registered yet. Click <strong>"Add New Customer"</strong> to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/customers/ledger/' . $c['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($c['name']) ?>
                                    </a>
                                    <?php if ($c['contact_person']): ?>
                                        <div class="text-muted small">Attn: <?= e($c['contact_person']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted me-1"></i> <?= e($c['mobile'] ?: '-') ?></div>
                                    <?php if ($c['email']): ?>
                                        <div class="text-muted small"><i class="bi bi-envelope text-muted me-1"></i> <?= e($c['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= e($c['city'] ? $c['city'] . ', ' : '') ?><?= e($c['state']) ?>
                                </td>
                                <td>
                                    <?php if ($c['gstin']): ?>
                                        <span class="badge bg-success-subtle text-success"><?= e($c['gstin']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Unregistered</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= (float)$c['credit_limit'] > 0 ? formatCurrency($c['credit_limit']) : '-' ?></td>
                                <td class="text-end">
                                    <?php 
                                    $outstanding = (float)$c['invoice_outstanding'] + (($c['balance_type'] === 'DR') ? (float)$c['opening_balance'] : -(float)$c['opening_balance']);
                                    ?>
                                    <span class="fw-bold text-<?= $outstanding > 0 ? 'danger' : 'success' ?>">
                                        <?= formatCurrency($outstanding) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/customers/ledger/' . $c['id']) ?>" class="btn btn-outline-info" title="Statement / Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        <a href="<?= url('/customers/edit/' . $c['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('/customers/delete/' . $c['id']) ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this customer?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
