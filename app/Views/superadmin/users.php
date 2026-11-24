<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people me-2 text-primary"></i> Registered Users Directory</h4>
        <p class="text-muted small mb-0">List of all registered system user accounts and company assignments</p>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Role / Level</th>
                        <th class="text-center">Companies</th>
                        <th class="text-center">Status</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $idx => $u): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><strong><?= e($u['full_name']) ?></strong></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['mobile'] ?: 'N/A') ?></td>
                            <td>
                                <?php if ($u['is_super_admin']): ?>
                                    <span class="badge bg-danger-subtle text-danger fw-bold"><i class="bi bi-shield-lock me-1"></i> Super Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold"><?= $u['company_count'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>-subtle text-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($u['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
