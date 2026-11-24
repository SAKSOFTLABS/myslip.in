<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-truck me-2 text-primary"></i> Vendor Directory</h4>
        <p class="text-muted small mb-0">Manage supplier contacts, GSTIN, credit days, and view live payables</p>
    </div>
    <a href="<?= url('/vendors/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Vendor
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Vendor Name</th>
                        <th>Mobile / Email</th>
                        <th>State / City</th>
                        <th>GSTIN</th>
                        <th class="text-end">Credit Days</th>
                        <th class="text-end">Outstanding Payable</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vendors)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-truck fs-3 d-block mb-1"></i>
                                No vendors registered yet. Click <strong>"Add New Vendor"</strong> to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vendors as $v): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/vendors/ledger/' . $v['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($v['name']) ?>
                                    </a>
                                    <?php if ($v['contact_person']): ?>
                                        <div class="text-muted small">Attn: <?= e($v['contact_person']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted me-1"></i> <?= e($v['mobile'] ?: '-') ?></div>
                                    <?php if ($v['email']): ?>
                                        <div class="text-muted small"><i class="bi bi-envelope text-muted me-1"></i> <?= e($v['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($v['city'] ? $v['city'] . ', ' : '') ?><?= e($v['state']) ?></td>
                                <td>
                                    <?php if ($v['gstin']): ?>
                                        <span class="badge bg-success-subtle text-success"><?= e($v['gstin']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Unregistered</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= $v['credit_days'] ?> Days</td>
                                <td class="text-end">
                                    <?php 
                                    $payable = (float)$v['bill_outstanding'] + (($v['balance_type'] === 'CR') ? (float)$v['opening_balance'] : -(float)$v['opening_balance']);
                                    ?>
                                    <span class="fw-bold text-<?= $payable > 0 ? 'warning' : 'success' ?>">
                                        <?= formatCurrency($payable) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/vendors/ledger/' . $v['id']) ?>" class="btn btn-outline-info" title="Statement / Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        <a href="<?= url('/vendors/edit/' . $v['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('/vendors/delete/' . $v['id']) ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this vendor?')">
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
