<?php
$isEdit = !empty($account);
$actionUrl = $isEdit ? url('/accounts/update/' . $account['id']) : url('/accounts/store');
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-journal-plus me-2 text-primary"></i> <?= $isEdit ? 'Edit Account: ' . e($account['account_name']) : 'Add New Ledger Account' ?></span>
                <a href="<?= url('/accounts') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Accounts
                </a>
            </div>
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" value="<?= e($account['account_name'] ?? '') ?>" placeholder="e.g. Office Rent Expense, ICICI Current A/c" required autofocus>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Account Group <span class="text-danger">*</span></label>
                            <select name="group_id" class="form-select" required>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= (isset($account['group_id']) && $account['group_id'] == $g['id']) ? 'selected' : '' ?>>
                                        <?= e($g['name']) ?> (<?= ucfirst($g['category']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Cash / Bank Options -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold small mb-2 text-dark">Account Type Classification</h6>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_cash" value="1" id="isCashCheck" <?= !empty($account['is_cash']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="isCashCheck">This is a Cash Account</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_bank" value="1" id="isBankCheck" <?= !empty($account['is_bank']) ? 'checked' : '' ?> onchange="toggleBankFields()">
                                <label class="form-check-label small" for="isBankCheck">This is a Bank Account</label>
                            </div>
                        </div>

                        <div id="bankFieldsDiv" class="row g-2" style="<?= empty($account['is_bank']) ? 'display:none;' : '' ?>">
                            <div class="col-md-6">
                                <label class="form-label small">Bank Account Number</label>
                                <input type="text" name="bank_account_no" class="form-control form-control-sm" value="<?= e($account['bank_account_no'] ?? '') ?>" placeholder="Account No">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Bank IFSC Code</label>
                                <input type="text" name="bank_ifsc" class="form-control form-control-sm text-uppercase" value="<?= e($account['bank_ifsc'] ?? '') ?>" placeholder="IFSC">
                            </div>
                        </div>
                    </div>

                    <?php if (!$isEdit): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Opening Balance (₹)</label>
                            <input type="number" name="opening_balance" class="form-control text-end" step="0.01" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Opening Balance Type</label>
                            <select name="balance_type" class="form-select">
                                <option value="DR">Debit (DR)</option>
                                <option value="CR">Credit (CR)</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/accounts') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Account' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleBankFields() {
    const isBank = document.getElementById('isBankCheck').checked;
    document.getElementById('bankFieldsDiv').style.display = isBank ? 'flex' : 'none';
}
</script>
