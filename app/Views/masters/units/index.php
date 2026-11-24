<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-rulers me-2 text-primary"></i> Measurement Units & Item Groups</h4>
        <p class="text-muted small mb-0">Configure UQC units of measurement and category taxonomy</p>
    </div>
    <a href="<?= url('/items') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Items
    </a>
</div>

<div class="row g-4">
    <!-- Measurement Units Column -->
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-aspect-ratio text-primary me-1"></i> Measurement Units</span>
            </div>
            <div class="card-body">
                <!-- Add Unit Form -->
                <form action="<?= url('/units/store') ?>" method="POST" class="mb-4 p-3 bg-light rounded-3 border">
                    <?= csrf_field() ?>
                    <h6 class="fw-bold small mb-2">Add New Unit</h6>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Unit Full Name (e.g. Bundle)" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="short_name" class="form-control form-control-sm text-uppercase" placeholder="Symbol (BDL)" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-plus"></i> Add</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-custom table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th class="text-center">Decimals</th>
                                <th class="text-center">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($units as $u): ?>
                                <tr>
                                    <td><strong><?= e($u['name']) ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($u['short_name']) ?></span></td>
                                    <td class="text-center"><?= $u['decimal_places'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $u['is_system'] ? 'secondary' : 'primary' ?>-subtle text-<?= $u['is_system'] ? 'secondary' : 'primary' ?>">
                                            <?= $u['is_system'] ? 'Standard' : 'Custom' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Groups Column -->
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-tags text-primary me-1"></i> Item Groups / Categories</span>
            </div>
            <div class="card-body">
                <!-- Add Group Form -->
                <form action="<?= url('/groups/store') ?>" method="POST" class="mb-4 p-3 bg-light rounded-3 border">
                    <?= csrf_field() ?>
                    <h6 class="fw-bold small mb-2">Add Item Group</h6>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Group Name (e.g. Electronics)" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Description">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-plus"></i> Add</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-custom table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Description</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($groups)): ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted small">No custom groups created yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($groups as $g): ?>
                                    <tr>
                                        <td><strong><?= e($g['name']) ?></strong></td>
                                        <td><?= e($g['description'] ?: '-') ?></td>
                                        <td class="text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
