<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-buildings me-2 text-primary"></i> Tenant Companies Directory</h4>
        <p class="text-muted small mb-0">Manage all businesses using MYSLIP.IN SaaS, monitor sales volumes, and manage tenant status</p>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Owner Account</th>
                        <th>State / Phone</th>
                        <th>GSTIN</th>
                        <th>Plan Type</th>
                        <th class="text-end">Total Invoices</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $c): ?>
                        <tr>
                            <td>
                                <strong><?= e($c['company_name']) ?></strong>
                                <?php if (!empty($c['business_name'])): ?><div class="text-muted small"><?= e($c['business_name']) ?></div><?php endif; ?>
                            </td>
                            <td>
                                <div><?= e($c['owner_name']) ?></div>
                                <div class="text-muted small"><?= e($c['owner_email']) ?></div>
                            </td>
                            <td>
                                <div><?= e($c['state']) ?></div>
                                <div class="text-muted small"><?= e($c['mobile']) ?></div>
                            </td>
                            <td><?= e($c['gstin'] ?: 'Unregistered') ?></td>
                            <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= e($c['plan_type'] ?? 'Trial') ?></span></td>
                            <td class="text-end fw-bold"><?= number_format($c['total_sales_count']) ?></td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency($c['total_sales_val']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'danger' ?>-subtle text-<?= $c['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= url('/company/switch/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Impersonate / Switch Tenant">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </a>
                                    <form action="<?= url('/admin/companies/toggle/' . $c['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $c['status'] === 'active' ? 'danger' : 'success' ?>" title="<?= $c['status'] === 'active' ? 'Suspend Tenant' : 'Activate Tenant' ?>" onclick="return confirm('Change tenant status?')">
                                            <i class="bi bi-<?= $c['status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
