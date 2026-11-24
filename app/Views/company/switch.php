<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header">
                <span><i class="bi bi-building me-2 text-primary"></i> Your Registered Companies</span>
                <a href="<?= url('/company/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add New Company
                </a>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($companies as $comp): ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="fw-bold mb-1"><?= e($comp['company_name']) ?></h6>
                                <p class="text-muted small mb-0">
                                    <span class="badge bg-light text-dark border me-2"><?= ucfirst($comp['business_type']) ?></span>
                                    <span class="me-2"><i class="bi bi-geo-alt"></i> <?= e($comp['state']) ?></span>
                                    <?php if ($comp['gst_enabled']): ?>
                                        <span class="text-success"><i class="bi bi-shield-check"></i> GSTIN: <?= e($comp['gstin'] ?: 'N/A') ?></span>
                                    <?php else: ?>
                                        <span class="text-secondary">Non-GST</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <?php if ($comp['id'] == \App\Core\Session::getCompanyId()): ?>
                                    <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Currently Active</span>
                                <?php else: ?>
                                    <a href="<?= url('/company/switch/' . $comp['id']) ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                        Switch to this
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
