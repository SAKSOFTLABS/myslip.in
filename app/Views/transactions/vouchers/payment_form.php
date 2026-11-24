<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-credit-card-2-back me-2 text-primary"></i> Record Payment Voucher</span>
                <a href="<?= url('/vouchers/payments') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Payments
                </a>
            </div>
            <div class="card-body">
                <form action="<?= url('/vouchers/payments/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Paid To: Select Vendor</label>
                            <select name="vendor_id" id="vendorSelect" class="form-select">
                                <option value="">-- Choose Vendor (or select Expense below) --</option>
                                <?php foreach ($vendors as $v): ?>
                                    <option value="<?= $v['id'] ?>">
                                        <?= e($v['name']) ?> (Payable: <?= formatCurrency($v['bill_outstanding']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Or Paid To: Expense / Direct Account</label>
                            <select name="expense_account_id" id="expenseSelect" class="form-select">
                                <option value="">-- Choose Expense Account --</option>
                                <?php foreach ($expenseAccounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['account_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Paid From (Cash / Bank) <span class="text-danger">*</span></label>
                            <select name="paid_from_account_id" class="form-select" required>
                                <?php foreach ($cashBankAccounts as $cb): ?>
                                    <option value="<?= $cb['id'] ?>">
                                        <?= e($cb['account_name']) ?> (<?= $cb['is_cash'] ? 'Cash' : 'Bank' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Amount Paid (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= \App\Core\Session::get('currency_symbol', '₹') ?></span>
                                <input type="number" name="amount" class="form-control fw-bold text-danger text-end" step="0.01" min="1" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Payment Mode</label>
                            <select name="payment_mode" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer / NEFT</option>
                                <option value="upi">UPI / Online</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Cheque Number</label>
                            <input type="text" name="cheque_no" class="form-control" placeholder="Cheque # if any">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">UTR / Transaction Reference</label>
                            <input type="text" name="utr_no" class="form-control" placeholder="Bank UTR #">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Narration / Remarks</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Payment notes..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/vouchers/payments') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
