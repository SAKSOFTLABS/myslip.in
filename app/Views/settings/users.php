<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-primary"></i> Team & Staff Access</h4>
        <p class="text-muted small mb-0">Role-based access control (Admin, Accountant, Billing Staff, Viewer)</p>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Assigned Role</th>
                        <th class="text-center">Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted small">No staff members found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong><?= e($u['name']) ?></strong></td>
                                <td><?= e($u['email']) ?></td>
                                <td><?= e($u['mobile'] ?: '-') ?></td>
                                <td><span class="badge bg-primary-subtle text-primary"><?= e($u['role_name']) ?></span></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success"><?= ucfirst($u['status']) ?></span></td>
                                <td><?= formatDate($u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
