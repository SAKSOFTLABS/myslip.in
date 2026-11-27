<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-palette2 text-primary me-2"></i> Predesigned Bill Designs Management</h4>
        <p class="text-muted small mb-0">Publish, customize, and manage Free and Paid invoice templates across your SaaS tenant network.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/bill-designs/settings') ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-whatsapp me-1"></i> WhatsApp Settings
        </a>
        <a href="<?= url('/admin/bill-designs/entitlements') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-shield-check me-1"></i> Company Entitlements
        </a>
        <a href="<?= url('/admin/bill-designs/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Bill Design
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card-custom mb-4 p-3 bg-white">
    <form method="GET" action="<?= url('/admin/bill-designs') ?>" id="adminFilterForm">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search templates by name, key, or category..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="pricing_type" class="form-select form-select-sm" onchange="document.getElementById('adminFilterForm').submit()">
                    <option value="all" <?= $pricing === 'all' ? 'selected' : '' ?>>All Pricing Types</option>
                    <option value="free" <?= $pricing === 'free' ? 'selected' : '' ?>>Free Templates</option>
                    <option value="paid" <?= $pricing === 'paid' ? 'selected' : '' ?>>Paid Templates</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('adminFilterForm').submit()">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published (Visible to Tenants)</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
                    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                <?php if ($search !== '' || $status !== 'all' || $pricing !== 'all'): ?>
                    <a href="<?= url('/admin/bill-designs') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Templates Table -->
<div class="card-custom bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th style="width: 50px;">Preview</th>
                    <th>Template Name & Key</th>
                    <th>Category</th>
                    <th>Paper Size</th>
                    <th>Pricing Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Active Companies</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No bill designs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($templates as $t): ?>
                        <tr>
                            <td>
                                <div class="rounded border p-1 bg-light text-center" style="width: 44px; height: 44px;">
                                    <?php if (!empty($t['preview_image']) && file_exists(dirname(__DIR__, 4) . '/public' . $t['preview_image'])): ?>
                                        <img src="<?= asset(ltrim($t['preview_image'], '/')) ?>" alt="" class="w-100 h-100 object-fit-contain">
                                    <?php else: ?>
                                        <i class="bi bi-file-earmark-text text-muted fs-4"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-dark"><?= e($t['name']) ?></strong>
                                    <?php if (!empty($t['is_featured'])): ?>
                                        <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 10px;">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="font-monospace text-muted small" style="font-size: 11px;">
                                    Key: <?= e($t['template_key']) ?> | v<?= e($t['version']) ?> | Order: <?= (int)$t['display_order'] ?>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($t['category']) ?></span></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= e($t['paper_size']) ?></span></td>
                            <td>
                                <?php if ($t['pricing_type'] === 'paid'): ?>
                                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-star-fill me-1"></i> PAID</span>
                                <?php else: ?>
                                    <span class="badge bg-success fw-bold"><i class="bi bi-check-circle me-1"></i> FREE</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold">
                                <?php if ($t['pricing_type'] === 'paid'): ?>
                                    <?= ((float)$t['price'] > 0) ? formatCurrency($t['price']) : '<span class="text-muted small">WhatsApp Enquiry</span>' ?>
                                <?php else: ?>
                                    <span class="text-muted">₹ 0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['status'] === 'published'): ?>
                                    <span class="badge bg-success-subtle text-success">Published</span>
                                <?php elseif ($t['status'] === 'draft'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Archived</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill"><?= (int)$t['active_companies_count'] ?> active</span>
                                <?php if ($t['pricing_type'] === 'paid'): ?>
                                    <span class="badge bg-info-subtle text-info rounded-pill ms-1"><?= (int)$t['active_licenses_count'] ?> licensed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/admin/bill-designs/preview/' . $t['id']) ?>" class="btn btn-outline-secondary" title="Preview Design">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/admin/bill-designs/edit/' . $t['id']) ?>" class="btn btn-outline-primary" title="Edit Design">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Duplicate -->
                                    <form method="POST" action="<?= url('/admin/bill-designs/duplicate/' . $t['id']) ?>" class="d-inline" onsubmit="return confirm('Duplicate this template?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-secondary" title="Duplicate Template">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>

                                    <!-- Quick Toggle Publish / Unpublish -->
                                    <?php if ($t['status'] === 'published'): ?>
                                        <form method="POST" action="<?= url('/admin/bill-designs/unpublish/' . $t['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-warning" title="Unpublish (move to draft)">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('/admin/bill-designs/publish/' . $t['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-success" title="Publish (make available to tenants)">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Archive -->
                                    <?php if ($t['status'] !== 'archived'): ?>
                                        <form method="POST" action="<?= url('/admin/bill-designs/archive/' . $t['id']) ?>" class="d-inline" onsubmit="return confirm('Archive this template?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Archive Template">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
