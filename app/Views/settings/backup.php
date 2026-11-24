<div class="row g-4">
    <!-- Download Backup Column -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header bg-primary-subtle">
                <span class="fw-bold text-primary"><i class="bi bi-cloud-arrow-down-fill me-2"></i> Download Database Backup</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Download a secure, complete snapshot of your company's data including products, customers, vendors, sales, purchases, general ledger, and invoices in portable JSON format.
                </p>
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill text-success fs-3 me-3"></i>
                        <div class="small text-muted">
                            <strong>100% Tenant Isolated:</strong> Only data belonging to your company is exported. No other tenant data is ever included.
                        </div>
                    </div>
                </div>
                <a href="<?= url('/settings/backup/download') ?>" class="btn btn-primary fw-bold w-100 py-2">
                    <i class="bi bi-download me-1"></i> Download Company Backup (.json)
                </a>
            </div>
        </div>
    </div>

    <!-- Restore Backup Column -->
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header bg-warning-subtle">
                <span class="fw-bold text-dark"><i class="bi bi-cloud-arrow-up-fill me-2 text-warning"></i> Restore Company Data</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Caution:</strong> Restoring will replace existing records for this company with data from the backup file.
                </div>
                <form action="<?= url('/settings/backup/restore') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Upload Backup File (.json)</label>
                        <input type="file" name="backup_file" class="form-control" accept=".json,application/json" required>
                    </div>
                    <button type="submit" class="btn btn-danger fw-bold w-100 py-2" onclick="return confirm('Are you sure you want to restore? Current company data will be overwritten.')">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Upload & Restore Database
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
