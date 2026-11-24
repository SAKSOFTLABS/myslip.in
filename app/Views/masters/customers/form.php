<?php
$isEdit = !empty($customer);
$actionUrl = $isEdit ? url('/customers/update/' . $customer['id']) : url('/customers/store');
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-person-fill me-2 text-primary"></i> <?= $isEdit ? 'Edit Customer: ' . e($customer['name']) : 'Add New Customer' ?></span>
                <a href="<?= url('/customers') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Directory
                </a>
            </div>
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Customer / Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($customer['name'] ?? '') ?>" placeholder="e.g. Ramesh Hardware Store" required autofocus>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= e($customer['contact_person'] ?? '') ?>" placeholder="e.g. Mr. Ramesh Kumar">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control" value="<?= e($customer['mobile'] ?? '') ?>" placeholder="98XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= e($customer['email'] ?? '') ?>" placeholder="customer@email.com">
                        </div>
                    </div>

                    <!-- GST & Tax Information -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check me-1"></i> GST & Tax Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">GSTIN (15 Digits)</label>
                                <input type="text" name="gstin" id="custGstin" class="form-control text-uppercase" value="<?= e($customer['gstin'] ?? '') ?>" placeholder="27AAAAA0000A1Z5" maxlength="15" oninput="autoExtractPan(this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">PAN Number</label>
                                <input type="text" name="pan" id="custPan" class="form-control text-uppercase" value="<?= e($customer['pan'] ?? '') ?>" placeholder="AAAAA0000A" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <!-- Address & Location -->
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Billing Address</label>
                            <input type="text" name="billing_address" class="form-control" value="<?= e($customer['billing_address'] ?? '') ?>" placeholder="Shop / Building, Street, Area">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">State (Place of Supply) <span class="text-danger">*</span></label>
                            <select name="state" id="custState" class="form-select" required>
                                <?php 
                                $selectedState = $customer['state'] ?? \App\Core\Session::get('company_state', 'Maharashtra');
                                foreach ($states as $code => $stName): 
                                ?>
                                    <option value="<?= $stName ?>" <?= (strcasecmp($stName, $selectedState) === 0) ? 'selected' : '' ?>>
                                        <?= $code ?> - <?= $stName ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">City / District</label>
                            <input type="text" name="city" class="form-control" value="<?= e($customer['city'] ?? '') ?>" placeholder="City">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">PIN Code</label>
                            <input type="text" name="pin_code" class="form-control" value="<?= e($customer['pin_code'] ?? '') ?>" placeholder="400001">
                        </div>
                    </div>

                    <!-- Credit & Opening Balance -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 me-1"></i> Credit & Opening Balance</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Credit Limit (₹)</label>
                                <input type="number" name="credit_limit" class="form-control text-end" step="0.01" min="0" value="<?= e($customer['credit_limit'] ?? '0.00') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Credit Period (Days)</label>
                                <input type="number" name="credit_days" class="form-control text-end" min="0" value="<?= e($customer['credit_days'] ?? '0') ?>">
                            </div>
                            <?php if (!$isEdit): ?>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Opening Balance (₹)</label>
                                <div class="input-group">
                                    <input type="number" name="opening_balance" class="form-control text-end" step="0.01" value="0.00">
                                    <select name="balance_type" class="form-select" style="max-width: 80px;">
                                        <option value="DR">DR</option>
                                        <option value="CR">CR</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Notes / Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes about this customer..."><?= e($customer['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/customers') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Customer' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function autoExtractPan(gstin) {
    gstin = gstin.trim().toUpperCase();
    if (gstin.length >= 12) {
        document.getElementById('custPan').value = gstin.substring(2, 12);
    }
}
</script>
