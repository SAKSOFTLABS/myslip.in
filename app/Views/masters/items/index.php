<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i> Item & Inventory Master</h4>
        <p class="text-muted small mb-0">Manage products, services, rates, GST tax slabs, and stock levels</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/units') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-rulers me-1"></i> Units & Groups
        </a>
        <a href="<?= url('/items/create') ?>" class="btn btn-primary-custom shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Item
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        <form action="<?= url('/items') ?>" method="GET" class="d-flex flex-wrap gap-2 w-100">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control" placeholder="Search by name, barcode, code, HSN..." value="<?= e($search ?? '') ?>" autofocus>
            </div>

            <select name="group_id" class="form-select form-select-sm" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">All Item Groups</option>
                <?php foreach ($groups as $grp): ?>
                    <option value="<?= $grp['id'] ?>" <?= (isset($selectedGroup) && $selectedGroup == $grp['id']) ? 'selected' : '' ?>>
                        <?= e($grp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if (!empty($search) || !empty($selectedGroup)): ?>
                <a href="<?= url('/items') ?>" class="btn btn-sm btn-light border">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Code / Barcode</th>
                        <th>Group</th>
                        <th>HSN/SAC</th>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <th class="text-center">GST Rate</th>
                        <?php endif; ?>
                        <th class="text-end">Selling Rate</th>
                        <th class="text-end">Current Stock</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                No inventory items found. Click <strong>"Add New Item"</strong> to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($item['name']) ?></div>
                                    <?php if ($item['description']): ?>
                                        <div class="text-muted small"><?= e(substr($item['description'], 0, 50)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['code']): ?><span class="badge bg-light text-dark border me-1"><?= e($item['code']) ?></span><?php endif; ?>
                                    <?php if ($item['barcode']): ?><span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-upc-scan"></i> <?= e($item['barcode']) ?></span><?php endif; ?>
                                </td>
                                <td><?= e($item['group_name'] ?: '-') ?></td>
                                <td><?= e($item['hsn_sac'] ?: '-') ?></td>
                                <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary"><?= (float)$item['gst_rate'] ?>%</span>
                                    </td>
                                <?php endif; ?>
                                <td class="text-end fw-bold"><?= formatCurrency($item['selling_rate']) ?></td>
                                <td class="text-end">
                                    <?php 
                                    $isLow = (float)$item['current_stock'] <= (float)$item['reorder_level'];
                                    ?>
                                    <span class="badge bg-<?= $isLow ? 'danger' : 'success' ?>-subtle text-<?= $isLow ? 'danger' : 'success' ?> border border-<?= $isLow ? 'danger' : 'success' ?>">
                                        <?= formatQty($item['current_stock']) ?> <?= e($item['unit_name'] ?: 'PCS') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/items/edit/' . $item['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= url('/items/delete/' . $item['id']) ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
