<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Contact Settings</h4>
        <p class="text-muted small mb-0">Configure the WhatsApp phone number and prefilled enquiry template used across all Paid Bill Designs.</p>
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
            <form method="POST" action="<?= url('/admin/bill-designs/settings') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">WhatsApp Contact Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="whatsapp_number" class="form-control" placeholder="e.g. 919876543210" value="<?= e($settings['whatsapp_number'] ?? '919876543210') ?>" required>
                    </div>
                    <div class="form-text" style="font-size: 11px;">
                        Enter your full international WhatsApp number without spaces, brackets, or the plus (+) sign. (e.g. <code>919876543210</code> for India +91).
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Default WhatsApp Enquiry Message <span class="text-danger">*</span></label>
                    <textarea name="default_enquiry_message" class="form-control" rows="5" required><?= e($settings['default_enquiry_message'] ?? "Hello, I am interested in the bill design \"{{design_name}}\" in your billing software.\n\nCompany: {{company_name}}\n\nPlease provide the pricing and activation details.") ?></textarea>
                    <div class="form-text" style="font-size: 11px;">
                        Available dynamic tags: <code>{{design_name}}</code>, <code>{{company_name}}</code>, <code>{{template_id}}</code>.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Business / Brand Name</label>
                        <input type="text" name="business_name" class="form-control" value="<?= e($settings['business_name'] ?? 'MYSLIP.IN') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Support Email (Optional)</label>
                        <input type="email" name="support_email" class="form-control" value="<?= e($settings['support_email'] ?? 'support@myslip.in') ?>">
                    </div>
                </div>

                <div class="text-end border-top pt-3">
                    <button type="submit" class="btn btn-success fw-bold px-4" style="background-color: #25D366; border-color: #25D366;">
                        <i class="bi bi-check2-circle me-1"></i> Save WhatsApp Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Box -->
    <div class="col-lg-4">
        <div class="card-custom bg-white p-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-chat-dots me-1 text-success"></i> How It Works</h6>
            <div class="small text-muted" style="line-height: 1.6;">
                <p>When a tenant views a <strong>Paid Bill Design</strong> in the gallery, they see a <strong>Contact on WhatsApp</strong> button.</p>
                <p>Clicking this button automatically opens WhatsApp (or WhatsApp Web on desktop) with your configured international number and prefilled enquiry text.</p>
                <p class="mb-0">You communicate directly with the customer, discuss pricing/licensing, and then grant them access in <strong>Company Entitlements</strong>.</p>
            </div>
        </div>

        <div class="card-custom bg-white p-3">
            <h6 class="fw-bold mb-2">WhatsApp Click-to-Chat Test</h6>
            <?php
            $sampleUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '919876543210') . '?text=' . rawurlencode("Hello, I am testing the WhatsApp enquiry button for MYSLIP.IN");
            ?>
            <a href="<?= e($sampleUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success w-100 fw-semibold">
                <i class="bi bi-box-arrow-up-right me-1"></i> Test WhatsApp Link Now
            </a>
        </div>
    </div>
</div>
