<?php
$isEdit = !empty($vendor);
$actionUrl = $isEdit ? url('/vendors/update/' . $vendor['id']) : url('/vendors/store');
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-truck me-2 text-primary"></i> <?= $isEdit ? 'Edit Vendor: ' . e($vendor['name']) : 'Add New Vendor' ?></span>
                <a href="<?= url('/vendors') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Directory
                </a>
            </div>
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Vendor / Supplier Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($vendor['name'] ?? '') ?>" placeholder="e.g. Reliance Electronics Wholesale" required autofocus>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= e($vendor['contact_person'] ?? '') ?>" placeholder="e.g. Manoj Joshi">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control" value="<?= e($vendor['mobile'] ?? '') ?>" placeholder="98XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= e($vendor['email'] ?? '') ?>" placeholder="vendor@supplier.com">
                        </div>
                    </div>

                    <!-- GST Details -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check me-1"></i> GST & Tax Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">GSTIN (15 Digits)</label>
                                <input type="text" name="gstin" class="form-control text-uppercase" value="<?= e($vendor['gstin'] ?? '') ?>" placeholder="27AAAAA0000A1Z5" maxlength="15">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">PAN Number</label>
                                <input type="text" name="pan" class="form-control text-uppercase" value="<?= e($vendor['pan'] ?? '') ?>" placeholder="AAAAA0000A" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <!-- Address & Location -->
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Office / Godown Address</label>
                            <input type="text" name="address" class="form-control" value="<?= e($vendor['address'] ?? '') ?>" placeholder="Street, Industrial Area">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">State (Place of Supply) <span class="text-danger">*</span></label>
                            <select name="state" class="form-select" required>
                                <?php 
                                $selectedState = $vendor['state'] ?? \App\Core\Session::get('company_state', 'Maharashtra');
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
                            <input type="text" name="city" class="form-control" value="<?= e($vendor['city'] ?? '') ?>" placeholder="City">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">PIN Code</label>
                            <input type="text" name="pin_code" class="form-control" value="<?= e($vendor['pin_code'] ?? '') ?>" placeholder="400001">
                        </div>
                    </div>

                    <!-- Credit & Opening Balance -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 me-1"></i> Credit & Opening Balance</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Payment Credit Period (Days)</label>
                                <input type="number" name="credit_days" class="form-control text-end" min="0" value="<?= e($vendor['credit_days'] ?? '30') ?>">
                            </div>
                            <?php if (!$isEdit): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Opening Payable Balance (₹)</label>
                                <div class="input-group">
                                    <input type="number" name="opening_balance" class="form-control text-end" step="0.01" value="0.00">
                                    <select name="balance_type" class="form-select" style="max-width: 80px;">
                                        <option value="CR">CR</option>
                                        <option value="DR">DR</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Notes / Payment Terms</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes..."><?= e($vendor['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/vendors') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Vendor' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
