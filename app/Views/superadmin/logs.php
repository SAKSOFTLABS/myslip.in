<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-activity me-2 text-primary"></i> System Audit Logs</h4>
        <p class="text-muted small mb-0">Trace activity, security logs, and record modifications</p>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Tenant Company</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No audit logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="small text-muted"><?= e($l['created_at']) ?></td>
                                <td><strong><?= e($l['user_name'] ?: 'System / Guest') ?></strong></td>
                                <td><?= e($l['company_name'] ?: 'System Wide') ?></td>
                                <td><span class="badge bg-primary-subtle text-primary"><?= e($l['action']) ?></span></td>
                                <td><?= e($l['module']) ?></td>
                                <td><?= e($l['record_id'] ?: '-') ?></td>
                                <td class="small text-muted font-monospace"><?= e($l['ip_address'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
