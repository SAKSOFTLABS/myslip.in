<!-- KPI Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Tenants</div>
                        <h3 class="fw-bold mb-0 text-primary"><?= $totalCompanies ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-buildings fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-custom border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Active Companies</div>
                        <h3 class="fw-bold mb-0 text-success"><?= $activeCompanies ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-custom border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Registered Users</div>
                        <h3 class="fw-bold mb-0 text-info"><?= $totalUsers ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card-custom border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">SaaS Revenue</div>
                        <h3 class="fw-bold mb-0 text-warning"><?= formatCurrency($totalRevenue) ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-currency-rupee fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Onboarded Companies -->
<div class="card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-building me-1"></i> Recently Registered Companies</span>
        <a href="<?= url('/admin/companies') ?>" class="btn btn-sm btn-outline-primary">View All Tenants</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Owner</th>
                        <th>State</th>
                        <th>GST Mode</th>
                        <th>Plan Type</th>
                        <th class="text-center">Status</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCompanies as $c): ?>
                        <tr>
                            <td><strong><?= e($c['company_name']) ?></strong></td>
                            <td>
                                <div><?= e($c['owner_name']) ?></div>
                                <div class="text-muted small"><?= e($c['owner_email']) ?></div>
                            </td>
                            <td><?= e($c['state']) ?></td>
                            <td>
                                <span class="badge bg-<?= $c['gst_enabled'] ? 'success' : 'secondary' ?>-subtle text-<?= $c['gst_enabled'] ? 'success' : 'secondary' ?>">
                                    <?= $c['gst_enabled'] ? 'GST Registered' : 'NON-GST' ?>
                                </span>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= e($c['plan_type'] ?? 'Trial') ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'danger' ?>-subtle text-<?= $c['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($c['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
