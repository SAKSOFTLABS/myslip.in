<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('/admin/bill-designs') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bill Designs
        </a>
        <div>
            <h4 class="fw-bold mb-0">Admin Preview: <?= e($template['name']) ?></h4>
            <div class="small text-muted">
                Key: <code class="text-dark"><?= e($template['template_key']) ?></code> | 
                Category: <span class="badge bg-light text-dark border"><?= e($template['category']) ?></span> | 
                Pricing: <span class="badge bg-<?= $template['pricing_type'] === 'paid' ? 'warning text-dark' : 'success' ?>"><?= strtoupper($template['pricing_type']) ?></span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/bill-designs/edit/' . $template['id']) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Design
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Canvas
        </button>
    </div>
</div>

<div class="preview-bill-canvas mx-auto shadow-lg rounded-3 mb-5" style="max-width: 900px; background-color: #525659; padding: 24px 15px; display: flex; justify-content: center; overflow-x: auto; border: 1px solid #475569;">
    <?php
    \App\Core\View::partial($templateView, [
        'sale' => $sampleSale,
        'company' => $sampleCompany,
        'settings' => $settings,
        'template' => $template,
        'format' => $template['paper_size'] ?? 'A4',
        'isPreview' => true
    ]);
    ?>
</div>
