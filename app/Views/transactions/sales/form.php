<?php
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
$companyState = \App\Core\Session::get('company_state', 'Maharashtra');
$companyStateCode = \App\Core\Session::get('company_state_code', '27');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-receipt-cutoff me-1"></i> New Sales Invoice</h4>
        <span class="text-muted small">Place of Supply: <strong><?= e($companyState) ?></strong> (State Code: <?= e($companyStateCode) ?>)</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/sales') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
</div>

<form id="salesInvoiceForm" action="<?= url('/sales/store') ?>" method="POST">
    <?= csrf_field() ?>

    <!-- Invoice Header Box -->
    <div class="card-custom mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <!-- Customer Selection -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select form-select-sm" onchange="onCustomerChange(this)" required autofocus>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" 
                                    data-state="<?= e($c['state']) ?>" 
                                    data-state-code="<?= e($c['state_code']) ?>" 
                                    data-gstin="<?= e($c['gstin']) ?>" 
                                    data-mobile="<?= e($c['mobile']) ?>" 
                                    data-outstanding="<?= (float)$c['invoice_outstanding'] ?>">
                                <?= e($c['name']) ?> <?= $c['mobile'] ? "({$c['mobile']})" : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="customerInfoBadge" class="small mt-1 text-muted"></div>
                </div>

                <!-- Invoice Details -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Invoice Number</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold text-primary" value="<?= e($nextInvoiceNumber) ?>" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Payment Mode</label>
                    <select name="payment_mode" id="paymentModeSelect" class="form-select form-select-sm" onchange="calculateBillingTotals()">
                        <option value="credit">Credit (Pending)</option>
                        <option value="cash" selected>Cash Payment</option>
                        <option value="upi">UPI / Online QR</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Due Date</label>
                    <input type="date" name="due_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+15 days')) ?>">
                </div>
            </div>

            <!-- Barcode Scanner Quick Input -->
            <div class="row mt-2 pt-2 border-top g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-primary text-white"><i class="bi bi-upc-scan me-1"></i> Scan Barcode</span>
                        <input type="text" id="barcodeScannerInput" class="form-control" placeholder="Scan item barcode & press Enter..." onkeydown="if(event.key==='Enter'){event.preventDefault(); handleBarcodeScan(this.value); this.value='';}">
                    </div>
                </div>
                <div class="col-md-7 text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBillingRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Item Line
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Billing Grid Table -->
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
                            <th style="width: 120px;" class="text-end">Rate (₹)</th>
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
                        <!-- Rows dynamically rendered via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Summary & Totals -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card-custom h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Customer Notes / Payment Terms</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Thank you for your business..."></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Reference / PO Number</label>
                            <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="Ref #">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sales Type</label>
                            <select name="sales_type" class="form-select form-select-sm">
                                <option value="tax_invoice">Tax Invoice</option>
                                <option value="bill_of_supply">Bill of Supply</option>
                                <option value="retail_invoice">Retail Invoice</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Sub-Total (Gross):</td>
                                <td class="text-end fw-semibold">₹ <span id="displayGrossAmount">0.00</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Taxable Amount:</td>
                                <td class="text-end fw-semibold">₹ <span id="displayTaxableAmount">0.00</span></td>
                            </tr>
                            <?php if ($gstEnabled): ?>
                                <tr id="cgstRow">
                                    <td class="text-muted">Output CGST:</td>
                                    <td class="text-end text-primary">₹ <span id="displayCgstAmount">0.00</span></td>
                                </tr>
                                <tr id="sgstRow">
                                    <td class="text-muted">Output SGST:</td>
                                    <td class="text-end text-primary">₹ <span id="displaySgstAmount">0.00</span></td>
                                </tr>
                                <tr id="igstRow" style="display: none;">
                                    <td class="text-muted">Output IGST (Interstate):</td>
                                    <td class="text-end text-primary">₹ <span id="displayIgstAmount">0.00</span></td>
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
                                <td class="text-muted fw-semibold">Balance Due:</td>
                                <td class="text-end fw-bold text-danger">₹ <span id="displayBalanceAmount">0.00</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Hidden inputs for calculated totals -->
                    <input type="hidden" name="gross_amount" id="inputGrossAmount" value="0.00">
                    <input type="hidden" name="taxable_amount" id="inputTaxableAmount" value="0.00">
                    <input type="hidden" name="cgst_amount" id="inputCgstAmount" value="0.00">
                    <input type="hidden" name="sgst_amount" id="inputSgstAmount" value="0.00">
                    <input type="hidden" name="igst_amount" id="inputIgstAmount" value="0.00">
                    <input type="hidden" name="total_tax_amount" id="inputTotalTaxAmount" value="0.00">
                    <input type="hidden" name="round_off" id="inputRoundOff" value="0.00">
                    <input type="hidden" name="grand_total" id="inputGrandTotal" value="0.00">
                    <input type="hidden" name="balance_amount" id="inputBalanceAmount" value="0.00">
                    <input type="hidden" name="action" id="formSubmitAction" value="save">

                    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                        <button type="button" onclick="submitSalesInvoice('save')" class="btn btn-outline-primary fw-bold px-3">
                            <i class="bi bi-save me-1"></i> Save Invoice (F8)
                        </button>
                        <button type="button" onclick="submitSalesInvoice('save_and_print')" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="bi bi-printer-fill me-1"></i> Save & Print (F9)
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
window.IS_PURCHASE = false;

function onCustomerChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('customerInfoBadge').innerHTML = '';
        window.IS_INTERSTATE = false;
        calculateBillingTotals();
        return;
    }

    const state = opt.getAttribute('data-state') || '';
    const stateCode = opt.getAttribute('data-state-code') || '27';
    const gstin = opt.getAttribute('data-gstin') || '';
    const outstanding = parseFloat(opt.getAttribute('data-outstanding') || 0);

    window.IS_INTERSTATE = (stateCode !== window.COMPANY_STATE_CODE);

    // Toggle CGST/SGST vs IGST display rows
    const isGst = window.GST_ENABLED;
    if (isGst) {
        const cgstEl = document.getElementById('cgstRow');
        const sgstEl = document.getElementById('sgstRow');
        const igstEl = document.getElementById('igstRow');
        if (cgstEl) cgstEl.style.display = window.IS_INTERSTATE ? 'none' : 'table-row';
        if (sgstEl) sgstEl.style.display = window.IS_INTERSTATE ? 'none' : 'table-row';
        if (igstEl) igstEl.style.display = window.IS_INTERSTATE ? 'table-row' : 'none';
    }

    document.getElementById('customerInfoBadge').innerHTML = `
        <span class="me-2"><i class="bi bi-geo-alt"></i> ${state} (${stateCode})</span>
        <span class="me-2"><i class="bi bi-shield-check"></i> ${gstin || 'Unregistered'}</span>
        <span class="text-${outstanding > 0 ? 'danger' : 'success'} fw-bold">Due: ₹${outstanding.toLocaleString('en-IN')}</span>
    `;

    calculateBillingTotals();
}

function submitSalesInvoice(actionType) {
    const form = document.getElementById('salesInvoiceForm');
    const customerSelect = document.getElementById('customerSelect');

    if (!customerSelect || !customerSelect.value) {
        alert('Please select a customer.');
        if (customerSelect) customerSelect.focus();
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
            // Disable blank rows from sending empty items payload
            r.querySelectorAll('input, select').forEach(el => el.disabled = true);
        }
    });

    if (validCount === 0) {
        // Re-enable in case of validation failure
        rows.forEach(r => r.querySelectorAll('input, select').forEach(el => el.disabled = false));
        alert('Please select at least one item with a valid quantity.');
        return;
    }

    document.getElementById('formSubmitAction').value = actionType;
    form.submit();
}

// Shortcut Listeners: F8 = Save, F9 = Save & Print
document.addEventListener('keydown', function(e) {
    if (e.key === 'F8') {
        e.preventDefault();
        submitSalesInvoice('save');
    }
    if (e.key === 'F9') {
        e.preventDefault();
        submitSalesInvoice('save_and_print');
    }
});

// Initialize with 1 clean row on load
document.addEventListener('DOMContentLoaded', function() {
    addBillingRow();
});
</script>
