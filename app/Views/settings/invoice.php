<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="alert alert-primary d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-palette-fill fs-4 text-primary"></i>
                <div>
                    <strong>Looking for ready-made professional bill designs?</strong>
                    <div class="small text-muted">Browse our gallery of Free and Paid templates (GST, Retail A5, Thermal POS, Minimalist, Luxury).</div>
                </div>
            </div>
            <a href="<?= url('/settings/bill-designs') ?>" class="btn btn-sm btn-primary fw-bold text-nowrap">
                <i class="bi bi-palette me-1"></i> Open Bill Design Gallery
            </a>
        </div>

        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-printer-fill me-2 text-primary"></i> Invoice Customization & Printing Settings</span>
            </div>
            <div class="card-body">
                <form action="<?= url('/settings/invoice/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Default Template -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Default Printing Format</label>
                            <select name="default_format" class="form-select">
                                <option value="a4" <?= ($settings['default_format'] ?? 'a4') === 'a4' ? 'selected' : '' ?>>A4 Full Page (Standard Laser/Inkjet)</option>
                                <option value="a5" <?= ($settings['default_format'] ?? '') === 'a5' ? 'selected' : '' ?>>A5 Half Page (Compact Laser)</option>
                                <option value="thermal" <?= ($settings['default_format'] ?? '') === 'thermal' ? 'selected' : '' ?>>3-Inch / 80mm Thermal Receipt (POS Roll)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Invoice Theme Brand Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" value="<?= e($settings['primary_color'] ?? '#1e3a8a') ?>" title="Choose theme color">
                        </div>
                    </div>

                    <!-- Visibility Toggles -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-2">Display Elements</h6>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_bank_details" value="1" id="showBank" <?= !empty($settings['show_bank_details']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="showBank">Print Bank Details Box on Invoices</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_upi_qr" value="1" id="showUpi" <?= !empty($settings['show_upi_qr']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="showUpi">Print Dynamic UPI QR Code</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Terms & Conditions (Printed on footer)</label>
                        <textarea name="terms_conditions" class="form-control" rows="3"><?= e($settings['terms_conditions'] ?? "1. Goods once sold will not be taken back or exchanged.\n2. Interest @ 18% p.a. will be charged if payment is not made within credit period.\n3. Subject to local jurisdiction only.") ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Declaration Text</label>
                            <input type="text" name="declaration" class="form-control form-control-sm" value="<?= e($settings['declaration'] ?? 'We declare that this invoice shows actual price of the goods described and that all particulars are true and correct.') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Authorized Signatory Label</label>
                            <input type="text" name="signature_label" class="form-control form-control-sm" value="<?= e($settings['signature_label'] ?? 'Authorized Signatory') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Thermal Receipt Footer Note</label>
                        <input type="text" name="thermal_footer_note" class="form-control form-control-sm" value="<?= e($settings['thermal_footer_note'] ?? 'Thank you for your visit! Please visit again.') ?>">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Invoice Design
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
