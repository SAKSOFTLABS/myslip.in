<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check text-success me-2"></i> Company Bill Design Entitlements</h4>
        <p class="text-muted small mb-0">Manually grant and revoke tenant company access to Paid bill designs after receiving WhatsApp enquiry.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/bill-designs') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-palette2 me-1"></i> Bill Designs List
        </a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#grantAccessModal">
            <i class="bi bi-plus-lg me-1"></i> Grant Company Access
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card-custom mb-4 p-3 bg-white">
    <form method="GET" action="<?= url('/admin/bill-designs/entitlements') ?>" id="entitlementsFilterForm">
        <div class="row g-2 align-items-center">
            <!-- Filter by Company -->
            <div class="col-md-4">
                <select name="company_id" class="form-select form-select-sm" onchange="document.getElementById('entitlementsFilterForm').submit()">
                    <option value="">All Companies</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>" <?= $filterCompanyId === (int)$comp['id'] ? 'selected' : '' ?>>
                            <?= e($comp['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter by Template -->
            <div class="col-md-4">
                <select name="template_id" class="form-select form-select-sm" onchange="document.getElementById('entitlementsFilterForm').submit()">
                    <option value="">All Paid Templates</option>
                    <?php foreach ($paidTemplates as $pt): ?>
                        <option value="<?= $pt['id'] ?>" <?= $filterTemplateId === (int)$pt['id'] ? 'selected' : '' ?>>
                            <?= e($pt['name']) ?> (₹<?= formatCurrency($pt['price'], false) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('entitlementsFilterForm').submit()">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Entitlement Statuses</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active Access</option>
                    <option value="revoked" <?= $filterStatus === 'revoked' ? 'selected' : '' ?>>Revoked Access</option>
                </select>
            </div>

            <div class="col-md-1">
                <a href="<?= url('/admin/bill-designs/entitlements') ?>" class="btn btn-sm btn-outline-secondary w-100" title="Reset filter">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Entitlements Table -->
<div class="card-custom bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Company Name</th>
                    <th>Licensed Paid Template</th>
                    <th>Access Status</th>
                    <th>Granted Date</th>
                    <th>Granted By</th>
                    <th>Admin Notes</th>
                    <th class="text-end" style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entitlements)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-x fs-2 d-block mb-2 opacity-50"></i>
                            No company design entitlements found. Click <strong>Grant Company Access</strong> to authorize a tenant to use a paid design.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entitlements as $ent): ?>
                        <tr>
                            <td>
                                <strong><?= e($ent['company_name']) ?></strong>
                                <div class="small text-muted font-monospace"><?= e($ent['company_phone'] ?: $ent['company_email']) ?></div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= e($ent['template_name']) ?></span>
                                <span class="badge bg-warning text-dark ms-1">PAID</span>
                                <div class="small text-muted font-monospace"><?= e($ent['template_key']) ?></div>
                            </td>
                            <td>
                                <?php if ($ent['access_status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success fs-6 fw-semibold px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger fs-6 fw-semibold px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> Revoked
                                    </span>
                                    <?php if (!empty($ent['revoked_date'])): ?>
                                        <div class="small text-muted" style="font-size: 10px;"><?= formatDate($ent['revoked_date']) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= formatDate($ent['granted_date']) ?></td>
                            <td><?= e($ent['granted_by_name'] ?? 'Super Admin') ?></td>
                            <td class="small text-muted"><?= e($ent['notes'] ?: '-') ?></td>
                            <td class="text-end">
                                <?php if ($ent['access_status'] === 'active'): ?>
                                    <form method="POST" action="<?= url('/admin/bill-designs/entitlements/revoke/' . $ent['id']) ?>" class="d-inline" onsubmit="return confirm('Revoke access to <?= e(addslashes($ent['template_name'])) ?> for <?= e(addslashes($ent['company_name'])) ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke Access">
                                            <i class="bi bi-x-octagon me-1"></i> Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Re-grant -->
                                    <form method="POST" action="<?= url('/admin/bill-designs/entitlements/grant') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="company_id" value="<?= $ent['company_id'] ?>">
                                        <input type="hidden" name="template_id" value="<?= $ent['template_id'] ?>">
                                        <input type="hidden" name="notes" value="Re-activated by SuperAdmin">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Re-grant Access">
                                            <i class="bi bi-check2 me-1"></i> Restore
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Grant Company Access -->
<div class="modal fade" id="grantAccessModal" tabindex="-1" aria-labelledby="grantAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="grantAccessModalLabel">
                    <i class="bi bi-shield-plus me-1"></i> Grant Paid Design Entitlement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/admin/bill-designs/entitlements/grant') ?>">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tenant Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select" required>
                            <option value="">-- Choose Tenant Company --</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= $comp['id'] ?>">
                                    <?= e($comp['company_name']) ?> (<?= e($comp['city'] ?? $comp['state']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Paid Bill Design <span class="text-danger">*</span></label>
                        <select name="template_id" class="form-select" required>
                            <option value="">-- Choose Paid Design --</option>
                            <?php foreach ($paidTemplates as $pt): ?>
                                <option value="<?= $pt['id'] ?>">
                                    <?= e($pt['name']) ?> — ₹<?= formatCurrency($pt['price'], false) ?> (<?= e($pt['paper_size']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Admin Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. WhatsApp enquiry received, offline bank transfer confirmed..."></textarea>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        Once granted, the selected company can immediately see and activate this design in their <strong>Settings → Bill Designs</strong> gallery.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Grant Entitlement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
