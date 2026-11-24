<?php
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
$companyStateCode = \App\Core\Session::get('company_state_code', '27');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-warning"><i class="bi bi-bag-plus me-1"></i> New Purchase Bill</h4>
        <span class="text-muted small">Record inward inventory and calculate input tax credits</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/purchases') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bills
        </a>
    </div>
</div>

<form id="purchaseBillForm" action="<?= url('/purchases/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="card-custom mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Supplier / Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" id="vendorSelect" class="form-select form-select-sm" onchange="onVendorChange(this)" required autofocus>
                        <option value="">-- Choose Vendor --</option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>" 
                                    data-state="<?= e($v['state']) ?>" 
                                    data-state-code="<?= e($v['state_code']) ?>" 
                                    data-gstin="<?= e($v['gstin']) ?>" 
                                    data-mobile="<?= e($v['mobile']) ?>" 
                                    data-outstanding="<?= (float)$v['bill_outstanding'] ?>">
                                <?= e($v['name']) ?> <?= $v['mobile'] ? "({$v['mobile']})" : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="vendorInfoBadge" class="small mt-1 text-muted"></div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Purchase #</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold text-warning" value="<?= e($nextPurchaseNumber) ?>" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Supplier Invoice No</label>
                    <input type="text" name="supplier_invoice_number" class="form-control form-control-sm" placeholder="e.g. REL-8941">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Payment Mode</label>
                    <select name="payment_mode" id="paymentModeSelect" class="form-select form-select-sm" onchange="calculateBillingTotals()">
                        <option value="credit">Credit (Pending)</option>
                        <option value="cash">Cash Payment</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2 pt-2 border-top g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-warning text-dark fw-bold"><i class="bi bi-upc-scan me-1"></i> Scan Barcode</span>
                        <input type="text" id="barcodeScannerInput" class="form-control" placeholder="Scan barcode & press Enter..." onkeydown="if(event.key==='Enter'){event.preventDefault(); handleBarcodeScan(this.value); this.value='';}">
                    </div>
                </div>
                <div class="col-md-7 text-end">
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" onclick="addBillingRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Purchase Line
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="card-custom mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table billing-grid-table table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th style="width: 40px;" class="text-center">#</th>
                            <th style="min-width: 240px;">Item Description</th>
                            <?php if ($gstEnabled): ?>
                                <th style="width: 110px;" class="text-center">HSN/SAC</th>
                            <?php endif; ?>
                            <th style="width: 130px;" class="text-center">Quantity</th>
                            <th style="width: 120px;" class="text-end">Cost Rate (₹)</th>
                            <th style="width: 100px;" class="text-end">Disc %</th>
                            <th style="width: 120px;" class="text-end">Taxable (₹)</th>
                            <?php if ($gstEnabled): ?>
                                <th style="width: 95px;" class="text-center">GST %</th>
                            <?php endif; ?>
                            <th style="width: 130px;" class="text-end">Total (₹)</th>
                            <th style="width: 50px;" class="text-center"><i class="bi bi-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody id="billingItemsBody">
                        <!-- Dynamically populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Summary -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card-custom h-100">
                <div class="card-body">
                    <label class="form-label fw-semibold small">Supplier Remarks / Notes</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Purchase notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Gross Amount:</td>
                                <td class="text-end fw-semibold">₹ <span id="displayGrossAmount">0.00</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Taxable Total:</td>
                                <td class="text-end fw-semibold">₹ <span id="displayTaxableAmount">0.00</span></td>
                            </tr>
                            <?php if ($gstEnabled): ?>
                                <tr id="cgstRow">
                                    <td class="text-muted">Input CGST:</td>
                                    <td class="text-end text-warning">₹ <span id="displayCgstAmount">0.00</span></td>
                                </tr>
                                <tr id="sgstRow">
                                    <td class="text-muted">Input SGST:</td>
                                    <td class="text-end text-warning">₹ <span id="displaySgstAmount">0.00</span></td>
                                </tr>
                                <tr id="igstRow" style="display: none;">
                                    <td class="text-muted">Input IGST (Interstate):</td>
                                    <td class="text-end text-warning">₹ <span id="displayIgstAmount">0.00</span></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="text-muted">Round Off (+/-):</td>
                                <td class="text-end">₹ <span id="displayRoundOff">0.00</span></td>
                            </tr>
                            <tr class="border-top border-2">
                                <td class="fs-5 fw-bold text-dark">Grand Total:</td>
                                <td class="text-end fs-4 fw-bold text-success">₹ <span id="displayGrandTotal">0.00</span></td>
                            </tr>
                            <tr class="bg-light">
                                <td class="align-middle fw-semibold">Paid Amount:</td>
                                <td class="text-end">
                                    <input type="number" name="paid_amount" id="inputPaidAmount" class="form-control form-control-sm text-end fw-bold d-inline-block" style="max-width: 140px;" step="0.01" value="0.00" oninput="this.dataset.manual='true'; calculateBillingTotals();">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Balance Payable:</td>
                                <td class="text-end fw-bold text-danger">₹ <span id="displayBalanceAmount">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <input type="hidden" name="gross_amount" id="inputGrossAmount" value="0.00">
                    <input type="hidden" name="taxable_amount" id="inputTaxableAmount" value="0.00">
                    <input type="hidden" name="cgst_amount" id="inputCgstAmount" value="0.00">
                    <input type="hidden" name="sgst_amount" id="inputSgstAmount" value="0.00">
                    <input type="hidden" name="igst_amount" id="inputIgstAmount" value="0.00">
                    <input type="hidden" name="total_tax_amount" id="inputTotalTaxAmount" value="0.00">
                    <input type="hidden" name="round_off" id="inputRoundOff" value="0.00">
                    <input type="hidden" name="grand_total" id="inputGrandTotal" value="0.00">
                    <input type="hidden" name="balance_amount" id="inputBalanceAmount" value="0.00">

                    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                        <button type="button" onclick="submitPurchaseBill()" class="btn btn-warning fw-bold px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Purchase Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="<?= asset('js/billing.js') ?>"></script>
<script>
window.AVAILABLE_ITEMS = <?= json_encode($items) ?>;
window.COMPANY_STATE_CODE = "<?= e($companyStateCode) ?>";
window.IS_INTERSTATE = false;
window.IS_PURCHASE = true; // Use purchase_rate when populating rows

function onVendorChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('vendorInfoBadge').innerHTML = '';
        window.IS_INTERSTATE = false;
        calculateBillingTotals();
        return;
    }

    const state = opt.getAttribute('data-state') || '';
    const stateCode = opt.getAttribute('data-state-code') || '27';
    const gstin = opt.getAttribute('data-gstin') || '';
    const outstanding = parseFloat(opt.getAttribute('data-outstanding') || 0);

    window.IS_INTERSTATE = (stateCode !== window.COMPANY_STATE_CODE);

    const isGst = window.GST_ENABLED;
    if (isGst) {
        const cgstEl = document.getElementById('cgstRow');
        const sgstEl = document.getElementById('sgstRow');
        const igstEl = document.getElementById('igstRow');
        if (cgstEl) cgstEl.style.display = window.IS_INTERSTATE ? 'none' : 'table-row';
        if (sgstEl) sgstEl.style.display = window.IS_INTERSTATE ? 'none' : 'table-row';
        if (igstEl) igstEl.style.display = window.IS_INTERSTATE ? 'table-row' : 'none';
    }

    document.getElementById('vendorInfoBadge').innerHTML = `
        <span class="me-2"><i class="bi bi-geo-alt"></i> ${state} (${stateCode})</span>
        <span class="me-2"><i class="bi bi-shield-check"></i> ${gstin || 'Unregistered'}</span>
        <span class="text-warning fw-bold">Payable: ₹${outstanding.toLocaleString('en-IN')}</span>
    `;

    calculateBillingTotals();
}

function submitPurchaseBill() {
    const form = document.getElementById('purchaseBillForm');
    const vendorSelect = document.getElementById('vendorSelect');

    if (!vendorSelect || !vendorSelect.value) {
        alert('Please select a supplier / vendor.');
        if (vendorSelect) vendorSelect.focus();
        return;
    }

    const rows = document.querySelectorAll('#billingItemsBody tr');
    let validCount = 0;

    rows.forEach(r => {
        const itemSelect = r.querySelector('.item-select');
        const qtyInput = r.querySelector('.item-qty');
        const qty = parseFloat(qtyInput?.value || 0);

        if (itemSelect && itemSelect.value && qty > 0) {
            validCount++;
            r.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else {
            r.querySelectorAll('input, select').forEach(el => el.disabled = true);
        }
    });

    if (validCount === 0) {
        rows.forEach(r => r.querySelectorAll('input, select').forEach(el => el.disabled = false));
        alert('Please select at least one item with a valid quantity.');
        return;
    }

    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    addBillingRow();
});
</script>
