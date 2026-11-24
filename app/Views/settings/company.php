<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-building-gear me-2 text-primary"></i> Company Profile & Tax Settings</span>
            </div>
            <div class="card-body">
                <form action="<?= url('/settings/company/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Company / Business Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="<?= e($company['company_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Trade Name / Brand Name</label>
                            <input type="text" name="business_name" class="form-control" value="<?= e($company['business_name'] ?? '') ?>" placeholder="Optional Trade Name">
                        </div>
                    </div>

                    <!-- GST Configuration Box -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-shield-check me-1"></i> GST Registration Mode</h6>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="gst_enabled" value="1" id="gstToggle" <?= $company['gst_enabled'] ? 'checked' : '' ?> onchange="toggleGstFields()">
                                <label class="form-check-label fw-bold small text-dark" for="gstToggle">GST Billing Enabled</label>
                            </div>
                        </div>

                        <div id="gstFieldsDiv" class="row g-3" style="<?= !$company['gst_enabled'] ? 'display:none;' : '' ?>">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">GSTIN (15 Digits)</label>
                                <input type="text" name="gstin" class="form-control text-uppercase" value="<?= e($company['gstin'] ?? '') ?>" placeholder="27AAAAA0000A1Z5" maxlength="15">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">PAN Number</label>
                                <input type="text" name="pan" class="form-control text-uppercase" value="<?= e($company['pan'] ?? '') ?>" placeholder="AAAAA0000A" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <!-- Address & Location -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="<?= e($company['address_line1'] ?? '') ?>" placeholder="Shop / Office No, Street">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?= e($company['address_line2'] ?? '') ?>" placeholder="Landmark, Area">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">State (Primary Place of Business) <span class="text-danger">*</span></label>
                            <select name="state" class="form-select" required>
                                <?php foreach ($states as $code => $stName): ?>
                                    <option value="<?= $stName ?>" <?= (strcasecmp($stName, $company['state']) === 0) ? 'selected' : '' ?>>
                                        <?= $code ?> - <?= $stName ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($company['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">PIN Code</label>
                            <input type="text" name="pin_code" class="form-control" value="<?= e($company['pin_code'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Business Mobile / WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" class="form-control" value="<?= e($company['mobile']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Official Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= e($company['email'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Bank Details for Invoices -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bank me-1"></i> Bank Account & UPI Details (Printed on Invoices)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="<?= e($company['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Bank Account Number</label>
                                <input type="text" name="bank_account_no" class="form-control" value="<?= e($company['bank_account_no'] ?? '') ?>" placeholder="Account Number">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">IFSC Code</label>
                                <input type="text" name="bank_ifsc" class="form-control text-uppercase" value="<?= e($company['bank_ifsc'] ?? '') ?>" placeholder="HDFC0001234">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Branch Name</label>
                                <input type="text" name="bank_branch" class="form-control" value="<?= e($company['bank_branch'] ?? '') ?>" placeholder="Main Branch">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">UPI ID / VPA (For Dynamic QR)</label>
                                <input type="text" name="upi_id" class="form-control" value="<?= e($company['upi_id'] ?? '') ?>" placeholder="business@okhdfcbank">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Company Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleGstFields() {
    const isGst = document.getElementById('gstToggle').checked;
    document.getElementById('gstFieldsDiv').style.display = isGst ? 'flex' : 'none';
}
</script>
