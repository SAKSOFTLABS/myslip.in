<?php
$isEdit = !empty($item);
$actionUrl = $isEdit ? url('/items/update/' . $item['id']) : url('/items/store');
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i> <?= $isEdit ? 'Edit Item: ' . e($item['name']) : 'Add New Item' ?></span>
                <a href="<?= url('/items') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Items
                </a>
            </div>
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Item / Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" placeholder="e.g. Wireless Optical Mouse" required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Item Code / SKU</label>
                            <input type="text" name="code" class="form-control" value="<?= e($item['code'] ?? '') ?>" placeholder="e.g. ITM-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Barcode (EAN/UPC)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" class="form-control" value="<?= e($item['barcode'] ?? '') ?>" placeholder="Scan barcode">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Item Group / Category</label>
                            <select name="group_id" class="form-select">
                                <option value="">-- None / General --</option>
                                <?php foreach ($groups as $grp): ?>
                                    <option value="<?= $grp['id'] ?>" <?= (isset($item['group_id']) && $item['group_id'] == $grp['id']) ? 'selected' : '' ?>>
                                        <?= e($grp['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Base Measurement Unit</label>
                            <select name="unit_id" class="form-select">
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= (isset($item['unit_id']) && $item['unit_id'] == $u['id']) ? 'selected' : ($u['short_name'] === 'PCS' ? 'selected' : '') ?>>
                                        <?= e($u['name']) ?> (<?= e($u['short_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($gstEnabled): ?>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" class="form-control text-center" value="<?= e($item['hsn_sac'] ?? '') ?>" placeholder="e.g. 8471">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">GST Tax Rate</label>
                            <select name="gst_rate" class="form-select text-center">
                                <?php foreach (GST_RATES as $rate => $label): ?>
                                    <option value="<?= $rate ?>" <?= (isset($item['gst_rate']) && (float)$item['gst_rate'] == $rate) ? 'selected' : ($rate == 18.00 ? 'selected' : '') ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pricing & Rates -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-tag-fill me-1"></i> Pricing & Rates</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Purchase Rate (Cost Price)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= \App\Core\Session::get('currency_symbol', '₹') ?></span>
                                    <input type="number" name="purchase_rate" class="form-control text-end" step="0.01" min="0" value="<?= e($item['purchase_rate'] ?? '0.00') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Selling Rate (Retail Price)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= \App\Core\Session::get('currency_symbol', '₹') ?></span>
                                    <input type="number" name="selling_rate" class="form-control text-end fw-bold" step="0.01" min="0" value="<?= e($item['selling_rate'] ?? '0.00') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Maximum Retail Price (MRP)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= \App\Core\Session::get('currency_symbol', '₹') ?></span>
                                    <input type="number" name="mrp" class="form-control text-end" step="0.01" min="0" value="<?= e($item['mrp'] ?? '0.00') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Levels -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-stack me-1"></i> Stock Settings & Alerts</h6>
                        <div class="row g-3">
                            <?php if (!$isEdit): ?>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Initial Opening Stock</label>
                                <input type="number" name="opening_stock" class="form-control text-end" step="0.001" min="0" value="0">
                            </div>
                            <?php endif; ?>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Low Stock Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control text-end" step="0.001" min="0" value="<?= e($item['reorder_level'] ?? '5') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Minimum Stock</label>
                                <input type="number" name="min_stock" class="form-control text-end" step="0.001" min="0" value="<?= e($item['min_stock'] ?? '0') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Description / Item Specifications</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description printed on invoice lines..."><?= e($item['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/items') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Item' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
