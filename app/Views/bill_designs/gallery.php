<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-palette text-primary me-2"></i> Predesigned Bill Designs Gallery</h4>
        <p class="text-muted small mb-0">Browse, preview, and select high-quality invoice layouts tailored for your business printing needs.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/settings') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Company Settings
        </a>
    </div>
</div>

<!-- Filters Bar & Search -->
<div class="card-custom mb-4 p-3 bg-white">
    <form method="GET" action="<?= url('/settings/bill-designs') ?>" id="galleryFilterForm">
        <div class="row g-2 align-items-center">
            <!-- Search -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search designs by name or category..." value="<?= e($filters['search'] ?? '') ?>">
                </div>
            </div>

            <!-- Pricing Filter -->
            <div class="col-md-2">
                <select name="pricing_type" class="form-select form-select-sm" onchange="document.getElementById('galleryFilterForm').submit()">
                    <option value="all" <?= ($filters['pricing_type'] ?? '') === 'all' ? 'selected' : '' ?>>All Pricing (Free & Paid)</option>
                    <option value="free" <?= ($filters['pricing_type'] ?? '') === 'free' ? 'selected' : '' ?>>Free Designs</option>
                    <option value="paid" <?= ($filters['pricing_type'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid Premium Designs</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm" onchange="document.getElementById('galleryFilterForm').submit()">
                    <option value="all" <?= ($filters['category'] ?? '') === 'all' ? 'selected' : '' ?>>All Categories</option>
                    <option value="GST Invoice" <?= ($filters['category'] ?? '') === 'GST Invoice' ? 'selected' : '' ?>>GST Tax Invoice</option>
                    <option value="Non-GST Invoice" <?= ($filters['category'] ?? '') === 'Non-GST Invoice' ? 'selected' : '' ?>>Non-GST / Bill of Supply</option>
                    <option value="Retail Invoice" <?= ($filters['category'] ?? '') === 'Retail Invoice' ? 'selected' : '' ?>>Retail Invoice (A5)</option>
                    <option value="Wholesale Invoice" <?= ($filters['category'] ?? '') === 'Wholesale Invoice' ? 'selected' : '' ?>>Wholesale & B2B Grid</option>
                    <option value="Thermal Receipt" <?= ($filters['category'] ?? '') === 'Thermal Receipt' ? 'selected' : '' ?>>Thermal Receipt (80mm)</option>
                    <option value="POS Receipt" <?= ($filters['category'] ?? '') === 'POS Receipt' ? 'selected' : '' ?>>POS Mini (58mm)</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-2">
                <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('galleryFilterForm').submit()">
                    <option value="display_order ASC" <?= $sort === 'display_order ASC' ? 'selected' : '' ?>>Featured First</option>
                    <option value="name ASC" <?= $sort === 'name ASC' ? 'selected' : '' ?>>Name: A to Z</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Added</option>
                    <option value="price ASC" <?= $sort === 'price ASC' ? 'selected' : '' ?>>Price: Low to High</option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply filter">Filter</button>
                <?php if (!empty($filters['search']) || ($filters['pricing_type'] ?? 'all') !== 'all' || ($filters['category'] ?? 'all') !== 'all'): ?>
                    <a href="<?= url('/settings/bill-designs') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Gallery Grid -->
<?php if (empty($templates)): ?>
    <div class="card-custom p-5 text-center bg-white">
        <i class="bi bi-palette text-muted fs-1 mb-2"></i>
        <h5 class="fw-bold text-dark">No bill designs found</h5>
        <p class="text-muted small">No templates match your selected filters. Try clearing the search or category filter.</p>
        <div>
            <a href="<?= url('/settings/bill-designs') ?>" class="btn btn-sm btn-outline-primary">View All Designs</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($templates as $tpl): 
            $isPaid = $tpl['pricing_type'] === 'paid';
            $isActive = !empty($tpl['is_active']);
            $hasAccess = !empty($tpl['has_access']);
        ?>
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative <?= $isActive ? 'ring-2 ring-primary border-primary border' : '' ?>" style="transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <!-- Preview Image & Badges -->
                    <div class="position-relative bg-light border-bottom text-center" style="height: 200px; overflow: hidden; background: #f8fafc;">
                        <?php if (!empty($tpl['preview_image']) && file_exists(dirname(__DIR__, 3) . '/public' . $tpl['preview_image'])): ?>
                            <img src="<?= asset(ltrim($tpl['preview_image'], '/')) ?>" alt="<?= e($tpl['name']) ?>" class="w-100 h-100 object-fit-contain p-2">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                <i class="bi bi-file-earmark-text fs-1 mb-1 opacity-50"></i>
                                <span class="small fw-semibold"><?= e($tpl['name']) ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Top Left Badges -->
                        <div class="position-absolute top-0 start-0 m-2 d-flex gap-1 flex-wrap">
                            <?php if ($isPaid): ?>
                                <span class="badge bg-warning text-dark fw-bold shadow-sm">
                                    <i class="bi bi-star-fill me-1"></i> PAID
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success fw-bold shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> FREE
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($tpl['is_featured'])): ?>
                                <span class="badge bg-primary shadow-sm">
                                    <i class="bi bi-award-fill me-1"></i> Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Top Right Active Badge -->
                        <?php if ($isActive): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success fs-6 py-1 px-3 shadow fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i> ACTIVE
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <h6 class="fw-bold mb-0 text-dark"><?= e($tpl['name']) ?></h6>
                                <span class="badge bg-secondary-subtle text-secondary small fw-normal"><?= e($tpl['paper_size']) ?></span>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-light text-muted border text-uppercase" style="font-size: 10px;"><?= e($tpl['category']) ?></span>
                                <span class="text-muted small" style="font-size: 11px;">v<?= e($tpl['version']) ?></span>
                            </div>

                            <p class="text-muted small mb-3" style="font-size: 12px; min-height: 48px; line-height: 1.4;">
                                <?= e($tpl['description'] ?? 'Professional invoice layout ready for daily billing.') ?>
                            </p>
                        </div>

                        <!-- Card Footer Action Buttons -->
                        <div class="border-top pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <?php if ($isPaid): ?>
                                        <div class="small text-muted" style="font-size: 11px;">Pricing:</div>
                                        <div class="fw-bold text-dark fs-6">
                                            <?= ((float)$tpl['price'] > 0) ? formatCurrency($tpl['price']) : '<span class="text-muted small">Contact for Pricing</span>' ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="small text-muted" style="font-size: 11px;">Pricing:</div>
                                        <div class="fw-bold text-success fs-6">₹ 0.00 (Free)</div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <a href="<?= url('/settings/bill-designs/preview/' . $tpl['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Preview
                                    </a>
                                </div>
                            </div>

                            <!-- Activation / WhatsApp Enquiry Logic -->
                            <?php if ($isActive): ?>
                                <button type="button" class="btn btn-sm btn-success w-100 fw-bold disabled" disabled>
                                    <i class="bi bi-check-circle-fill me-1"></i> Currently Active Format
                                </button>
                            <?php elseif (!$isPaid || $hasAccess): ?>
                                <!-- Free OR Unlocked Paid Template: Immediate Activation -->
                                <form method="POST" action="<?= url('/settings/bill-designs/activate/' . $tpl['id']) ?>" onsubmit="return confirm('Activate <?= e(addslashes($tpl['name'])) ?> as your company default invoice design?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Select & Activate Design
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Paid Template without active entitlement: Contact on WhatsApp -->
                                <a href="<?= e($tpl['whatsapp_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success w-100 fw-bold" style="background-color: #25D366; border-color: #25D366;">
                                    <i class="bi bi-whatsapp me-1"></i> Contact on WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
