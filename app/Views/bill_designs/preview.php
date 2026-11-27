<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('/settings/bill-designs') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Gallery
        </a>
        <div>
            <h4 class="fw-bold mb-0">Preview: <?= e($template['name']) ?></h4>
            <div class="small text-muted">
                Category: <span class="badge bg-light text-dark border me-2"><?= e($template['category']) ?></span>
                Paper Size: <span class="badge bg-secondary-subtle text-secondary me-2"><?= e($template['paper_size']) ?></span>
                Version: <span class="text-muted">v<?= e($template['version']) ?></span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Sample
        </button>

        <?php if ($isActive): ?>
            <span class="btn btn-sm btn-success fw-bold disabled">
                <i class="bi bi-check2-circle me-1"></i> Active Design
            </span>
        <?php elseif ($hasAccess): ?>
            <form method="POST" action="<?= url('/settings/bill-designs/activate/' . $template['id']) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Activate This Design
                </button>
            </form>
        <?php else: ?>
            <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success fw-bold" style="background-color: #25D366; border-color: #25D366;">
                <i class="bi bi-whatsapp me-1"></i> Contact on WhatsApp for Activation
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Information Callout -->
<div class="alert alert-info d-flex align-items-center justify-content-between mb-4 no-print shadow-sm" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
        <div>
            <strong>Interactive Company Sample Preview:</strong> 
            This live preview renders your registered company name, address, phone, GSTIN, and banking information with sample line items.
        </div>
    </div>
    <div>
        <?php if ($template['pricing_type'] === 'paid'): ?>
            <span class="badge bg-warning text-dark fs-6 px-3 py-1 fw-bold">PAID (<?= ((float)$template['price'] > 0) ? formatCurrency($template['price']) : 'Contact for Price' ?>)</span>
        <?php else: ?>
            <span class="badge bg-success fs-6 px-3 py-1 fw-bold">FREE TEMPLATE</span>
        <?php endif; ?>
    </div>
</div>

<!-- Template Canvas Container -->
<div class="preview-bill-canvas mx-auto shadow-lg rounded-3 overflow-hidden bg-white mb-5" style="max-width: 900px; border: 1px solid #cbd5e1;">
    <?php
    // Inject the resolved invoice template view with sample sale and authenticated company
    echo \App\Core\View::partial(str_replace('.', '/', $templateView), [
        'sale' => $sampleSale,
        'company' => $company,
        'settings' => $settings,
        'template' => $template,
        'format' => $template['paper_size'] ?? 'A4',
        'isPreview' => true
    ]);
    ?>
</div>

<div class="text-center no-print pb-5">
    <a href="<?= url('/settings/bill-designs') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Return to Bill Designs Gallery
    </a>
</div>
