<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header">
                <span class="fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i> Record Customer Receipt Voucher</span>
                <a href="<?= url('/vouchers/receipts') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Receipts
                </a>
            </div>
            <div class="card-body">
                <form action="<?= url('/vouchers/receipts/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Received From (Customer) <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required autofocus>
                                <option value="">-- Choose Customer --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= e($c['name']) ?> (Outstanding: <?= formatCurrency($c['invoice_outstanding']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Deposit Into (Cash / Bank Account) <span class="text-danger">*</span></label>
                            <select name="deposit_account_id" class="form-select" required>
                                <?php foreach ($cashBankAccounts as $cb): ?>
                                    <option value="<?= $cb['id'] ?>">
                                        <?= e($cb['account_name']) ?> (<?= $cb['is_cash'] ? 'Cash' : 'Bank' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Amount Received (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= \App\Core\Session::get('currency_symbol', '₹') ?></span>
                                <input type="number" name="amount" class="form-control fw-bold text-success text-end" step="0.01" min="1" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Payment Mode</label>
                            <select name="payment_mode" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI (GPay/PhonePe/Paytm)</option>
                                <option value="neft_rtgs">NEFT / RTGS / IMPS</option>
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
                        <textarea name="notes" class="form-control" rows="2" placeholder="Receipt notes..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/vouchers/receipts') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
