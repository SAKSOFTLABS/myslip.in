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
                                    <a href="<?= url('/company/switch/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Impersonate / Switch Tenant (View App as this company)">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                    </a>
                                    <form action="<?= url('/admin/companies/toggle/' . $c['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $c['status'] === 'active' ? 'warning' : 'success' ?>" title="<?= $c['status'] === 'active' ? 'Suspend Tenant' : 'Activate Tenant' ?>" onclick="return confirm('Change tenant status?')">
                                            <i class="bi bi-<?= $c['status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                        </button>
                                    </form>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger btn-delete-company" 
                                            data-company-id="<?= $c['id'] ?>" 
                                            data-company-name="<?= e($c['company_name']) ?>" 
                                            title="Permanently Delete Tenant">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Company Security Confirmation Modal -->
<div class="modal fade" id="deleteCompanyModal" tabindex="-1" aria-labelledby="deleteCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteCompanyModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Permanent Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteCompanyForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="text-danger fw-semibold mb-2">
                        ⚠️ Warning: You are about to permanently delete <strong id="deleteTargetCompanyName" class="text-dark"></strong>.
                    </p>
                    <p class="text-muted small mb-3">
                        This action will immediately erase all company invoices, items, ledger entries, customers, vendors, and transactions. This action cannot be reversed.
                    </p>

                    <div class="mb-3">
                        <label for="confirmWordInput" class="form-label fw-bold small text-dark">
                            Enter the confirmation word to proceed <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="confirmation_word" id="confirmWordInput" class="form-control" autocomplete="off" required>
                        <div id="spellingMistakeAlert" class="text-danger small mt-2 d-none fw-bold">
                            <i class="bi bi-x-circle-fill me-1"></i> Spelling mistake.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm">
                        <i class="bi bi-trash3-fill me-1"></i> Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('deleteCompanyModal');
    const deleteModal = modalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;
    const formEl = document.getElementById('deleteCompanyForm');
    const targetNameEl = document.getElementById('deleteTargetCompanyName');
    const inputEl = document.getElementById('confirmWordInput');
    const errorEl = document.getElementById('spellingMistakeAlert');
    const baseUrl = "<?= url('/admin/companies/delete/') ?>";

    document.querySelectorAll('.btn-delete-company').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const compId = this.getAttribute('data-company-id');
            const compName = this.getAttribute('data-company-name');

            if (formEl) formEl.action = baseUrl + compId;
            if (targetNameEl) targetNameEl.textContent = compName;
            if (inputEl) {
                inputEl.value = '';
                inputEl.classList.remove('is-invalid');
            }
            if (errorEl) errorEl.classList.add('d-none');

            if (deleteModal) {
                deleteModal.show();
                setTimeout(function() {
                    if (inputEl) inputEl.focus();
                }, 300);
            }
        });
    });

    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            const typed = inputEl ? inputEl.value.trim().toLowerCase() : '';
            if (typed !== 'delete') {
                e.preventDefault();
                if (inputEl) {
                    inputEl.classList.add('is-invalid');
                    inputEl.focus();
                    inputEl.select();
                }
                if (errorEl) {
                    errorEl.classList.remove('d-none');
                }
                return false;
            }
        });
    }

    if (inputEl) {
        inputEl.addEventListener('input', function() {
            inputEl.classList.remove('is-invalid');
            if (errorEl) errorEl.classList.add('d-none');
        });
    }
});
</script>
