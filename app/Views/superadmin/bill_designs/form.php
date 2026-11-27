<?php
$isEdit = !empty($template);
$config = $isEdit ? (json_decode($template['template_config'] ?? '', true) ?: []) : [];
$primaryColor = $config['primary_color'] ?? '#1e3a8a';
$accentColor = $config['accent_color'] ?? '#3b82f6';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi <?= $isEdit ? 'bi-pencil-square text-primary' : 'bi-plus-circle-fill text-success' ?> me-2"></i> 
            <?= $isEdit ? 'Edit Bill Design: ' . e($template['name']) : 'Add New Bill Design' ?>
        </h4>
        <p class="text-muted small mb-0">Configure template metadata, pricing type, paper format, and publishing state.</p>
    </div>
    <div>
        <a href="<?= url('/admin/bill-designs') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Bill Designs
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card-custom bg-white p-4">
            <form method="POST" action="<?= $isEdit ? url('/admin/bill-designs/update/' . $template['id']) : url('/admin/bill-designs/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- Template Name -->
                    <div class="col-md-7">
                        <label class="form-label fw-semibold small">Design Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Modern Navy Tax Invoice" value="<?= e($template['name'] ?? '') ?>" required autofocus>
                    </div>

                    <!-- Unique Template Key -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Template Key <span class="text-danger">*</span></label>
                        <input type="text" name="template_key" class="form-control font-monospace" placeholder="e.g. modern-navy" value="<?= e($template['template_key'] ?? '') ?>" <?= $isEdit ? 'readonly' : 'required' ?> pattern="[a-z0-9_-]+" title="Lowercase letters, numbers, hyphens only">
                        <div class="form-text" style="font-size: 11px;">Unique internal identifier for template rendering view file.</div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of template layout, ideal business use cases, and paper suitability..."><?= e($template['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Category & Paper Size -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <?php 
                            $categories = ['GST Invoice', 'Non-GST Invoice', 'Retail Invoice', 'Wholesale Invoice', 'POS Receipt', 'Thermal Receipt'];
                            $currentCat = $template['category'] ?? 'GST Invoice';
                            foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= $currentCat === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Paper Size <span class="text-danger">*</span></label>
                        <select name="paper_size" class="form-select" required>
                            <?php 
                            $paperSizes = ['A4', 'A5', 'Thermal 80mm', 'Thermal 58mm'];
                            $currentPaper = $template['paper_size'] ?? 'A4';
                            foreach ($paperSizes as $ps): ?>
                                <option value="<?= e($ps) ?>" <?= $currentPaper === $ps ? 'selected' : '' ?>><?= e($ps) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr class="my-2">

                    <!-- Pricing Section -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Pricing Type <span class="text-danger">*</span></label>
                        <select name="pricing_type" id="pricingTypeSelect" class="form-select" onchange="togglePriceField(this.value)" required>
                            <option value="free" <?= ($template['pricing_type'] ?? 'free') === 'free' ? 'selected' : '' ?>>Free (Instant Activation)</option>
                            <option value="paid" <?= ($template['pricing_type'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid (WhatsApp Enquiry Required)</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="priceFieldWrapper">
                        <label class="form-label fw-semibold small">Price (₹ INR)</label>
                        <input type="number" step="0.01" min="0" name="price" id="priceInput" class="form-control" placeholder="0.00" value="<?= !empty($template['price']) ? (float)$template['price'] : '' ?>">
                        <div class="form-text" style="font-size: 11px;">Optional. Leave 0 or blank if pricing is only quoted on WhatsApp.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Currency</label>
                        <input type="text" name="currency" class="form-control text-uppercase" value="<?= e($template['currency'] ?? 'INR') ?>" maxlength="5">
                    </div>

                    <hr class="my-2">

                    <!-- Styling / Colors -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Primary Brand Color</label>
                        <div class="input-group">
                            <input type="color" name="primary_color" class="form-control form-control-color" value="<?= e($primaryColor) ?>" title="Choose primary color">
                            <input type="text" class="form-control" value="<?= e($primaryColor) ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Accent Color</label>
                        <div class="input-group">
                            <input type="color" name="accent_color" class="form-control form-control-color" value="<?= e($accentColor) ?>" title="Choose accent color">
                            <input type="text" class="form-control" value="<?= e($accentColor) ?>" readonly>
                        </div>
                    </div>

                    <!-- Preview Graphic -->
                    <div class="col-md-7">
                        <label class="form-label fw-semibold small">Preview Image File (Upload)</label>
                        <input type="file" name="preview_image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                        <div class="form-text" style="font-size: 11px;">Accepts SVG, PNG, JPG, or WEBP (ideal ratio 400x260).</div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">Or Existing Image URL / Path</label>
                        <input type="text" name="preview_image_url" class="form-control" placeholder="/assets/images/templates/..." value="<?= e($template['preview_image'] ?? '') ?>">
                    </div>

                    <hr class="my-2">

                    <!-- Publishing & Status -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Publication Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="published" <?= ($template['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published (Live in Gallery)</option>
                            <option value="draft" <?= ($template['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
                            <option value="archived" <?= ($template['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="<?= (int)($template['display_order'] ?? 0) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Template Version</label>
                        <input type="text" name="version" class="form-control" value="<?= e($template['version'] ?? '1.0.0') ?>">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeaturedCheck" <?= !empty($template['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="isFeaturedCheck">
                                Mark as Featured Template (Promoted at top of gallery)
                            </label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <a href="<?= url('/admin/bill-designs') ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save Changes' : 'Publish Bill Design' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview & Guidelines Column -->
    <div class="col-lg-4">
        <div class="card-custom bg-white p-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1 text-primary"></i> Template Guidelines</h6>
            <ul class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
                <li><strong>Free Designs:</strong> Can be activated immediately by any tenant without WhatsApp contact.</li>
                <li><strong>Paid Designs:</strong> Display a <em>Contact on WhatsApp</em> button. Tenants contact your team and you manually grant access from the <strong>Company Entitlements</strong> section.</li>
                <li><strong>Publishing:</strong> When published, designs appear instantly in tenant galleries without any code redeployment.</li>
            </ul>
        </div>

        <?php if ($isEdit && !empty($template['preview_image'])): ?>
            <div class="card-custom bg-white p-3 text-center">
                <h6 class="fw-bold mb-2">Current Preview Image</h6>
                <div class="border rounded p-2 bg-light">
                    <img src="<?= asset(ltrim($template['preview_image'], '/')) ?>" alt="" class="img-fluid rounded" style="max-height: 220px;">
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePriceField(pricingType) {
    const priceWrapper = document.getElementById('priceFieldWrapper');
    if (priceWrapper) {
        if (pricingType === 'paid') {
            priceWrapper.classList.remove('opacity-50');
        } else {
            priceWrapper.classList.add('opacity-50');
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('pricingTypeSelect');
    if (sel) togglePriceField(sel.value);
});
</script>
