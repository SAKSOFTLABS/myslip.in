<h4 class="fw-bold mb-2 text-center text-primary"><i class="bi bi-building"></i> Create Your Business Company</h4>
<p class="text-muted small text-center mb-4">Set up your company details. You can update these anytime from settings.</p>

<form action="<?= url('/company/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <label class="form-label fw-semibold small">Company / Trade Name <span class="text-danger">*</span></label>
            <input type="text" name="company_name" class="form-control" placeholder="e.g. Apex Enterprises" required autofocus>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Business Type</label>
            <select name="business_type" class="form-select">
                <?php foreach ($businessTypes as $val => $lbl): ?>
                    <option value="<?= $val ?>"><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- GST Mode Selection -->
    <div class="p-3 bg-light rounded-3 border mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="form-label fw-bold mb-0 text-dark">
                <i class="bi bi-shield-check text-primary me-1"></i> GST Registered Business?
            </label>
            <div class="form-check form-switch fs-5 mb-0">
                <input class="form-check-input" type="checkbox" name="gst_enabled" value="1" id="gstToggle" checked onchange="toggleGstFields()">
            </div>
        </div>
        <p class="text-muted small mb-2">Turn off if you run an unregistered / Non-GST business. Tax columns will be hidden automatically.</p>
        
        <div id="gstFieldsRow" class="row g-2 mt-1">
            <div class="col-md-6">
                <label class="form-label fw-semibold small">GSTIN (15 Digits)</label>
                <input type="text" name="gstin" id="gstinInput" class="form-control text-uppercase" placeholder="27AAAAA0000A1Z5" maxlength="15" oninput="autoExtractPanAndState(this.value)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small">PAN Number</label>
                <input type="text" name="pan" id="panInput" class="form-control text-uppercase" placeholder="AAAAA0000A" maxlength="10">
            </div>
        </div>
    </div>

    <!-- Contact & State -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">State (Place of Business) <span class="text-danger">*</span></label>
            <select name="state" id="stateSelect" class="form-select" required>
                <?php foreach ($states as $code => $stName): ?>
                    <option value="<?= $stName ?>" data-code="<?= $code ?>" <?= $code === '27' ? 'selected' : '' ?>>
                        <?= $code ?> - <?= $stName ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">City</label>
            <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai">
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Business Mobile <span class="text-danger">*</span></label>
            <input type="tel" name="mobile" class="form-control" placeholder="98XXXXXXXX" pattern="[0-9]{10}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Business Email</label>
            <input type="email" name="email" class="form-control" placeholder="sales@business.com">
        </div>
    </div>

    <!-- Bank Details for Invoices -->
    <div class="accordion mb-4" id="optionalAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#bankCollapse">
                    <i class="bi bi-bank me-2 text-primary"></i> Bank & Payment Details for Invoices (Optional)
                </button>
            </h2>
            <div id="bankCollapse" class="accordion-collapse collapse" data-bs-parent="#optionalAccordion">
                <div class="accordion-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="e.g. HDFC Bank">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Account Number</label>
                            <input type="text" name="bank_account_no" class="form-control form-control-sm" placeholder="Account No">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control form-control-sm text-uppercase" placeholder="HDFC0001234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">UPI ID (for QR Code on Bill)</label>
                            <input type="text" name="upi_id" class="form-control form-control-sm" placeholder="business@upi">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Invoice Number Prefix <span class="text-muted fw-normal">(Optional)</span></label>
            <input type="text" name="invoice_prefix" class="form-control text-uppercase" placeholder="e.g. INV (Leave blank if not needed)" maxlength="10">
            <div class="form-text small">Optional custom prefix for sales invoices. Defaults to INV.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Initial Financial Year</label>
            <input type="text" class="form-control bg-light" value="2026-2027 (01-Apr to 31-Mar)" readonly>
        </div>
    </div>

    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
        <i class="bi bi-check2-circle me-1"></i> Save Company & Open Dashboard
    </button>
</form>

<script>
function toggleGstFields() {
    const isChecked = document.getElementById('gstToggle').checked;
    const row = document.getElementById('gstFieldsRow');
    if (isChecked) {
        row.style.display = 'flex';
    } else {
        row.style.display = 'none';
        document.getElementById('gstinInput').value = '';
    }
}

function autoExtractPanAndState(gstin) {
    gstin = gstin.trim().toUpperCase();
    if (gstin.length >= 2) {
        const stateCode = gstin.substring(0, 2);
        const stateSelect = document.getElementById('stateSelect');
        for (let opt of stateSelect.options) {
            if (opt.getAttribute('data-code') === stateCode) {
                stateSelect.value = opt.value;
                break;
            }
        }
    }
    if (gstin.length >= 12) {
        // Characters 3 to 12 in GSTIN are the 10-digit PAN
        const pan = gstin.substring(2, 12);
        document.getElementById('panInput').value = pan;
    }
}
</script>
