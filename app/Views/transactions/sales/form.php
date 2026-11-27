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
                <!-- Customer Selection (Type & Select with Quick Shortcut) -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small mb-0">Customer <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-semibold rounded-pill" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" title="Add New Customer">
                            <i class="bi bi-plus-circle-fill text-primary me-1"></i> New Customer
                        </button>
                    </div>
                    <div class="type-select-wrapper position-relative">
                        <input type="hidden" name="customer_id" id="customerSelect" value="" required>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   id="customerSearchInput" 
                                   class="form-control form-control-sm type-select-input" 
                                   placeholder="Type customer name, phone, or GSTIN..." 
                                   autocomplete="off" 
                                   autofocus 
                                   required>
                            <button type="button" class="btn btn-outline-secondary btn-clear-selection d-none" id="btnClearCustomer" title="Clear customer">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button type="button" class="btn btn-primary fw-bold px-2" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" title="Quick Add Customer">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <div class="type-select-dropdown shadow-lg rounded-2 border d-none" id="customerDropdownList"></div>
                    </div>
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

<!-- Quick Add Customer Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1" aria-labelledby="quickCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="quickCustomerModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickCustomerForm">
                <?= csrf_field() ?>
                <div class="modal-body p-3">
                    <div id="quickCustomerAlert" class="alert alert-danger d-none py-2 small"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Customer / Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="quickCustomerName" class="form-control form-control-sm" placeholder="e.g. Acme Corp / Rahul Sharma" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile Number</label>
                            <input type="tel" name="mobile" id="quickCustomerMobile" class="form-control form-control-sm" placeholder="10-digit mobile" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">GSTIN (Optional)</label>
                            <input type="text" name="gstin" id="quickCustomerGstin" class="form-control form-control-sm text-uppercase" placeholder="27ABCDE1234F1Z5" maxlength="15">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">State <span class="text-danger">*</span></label>
                            <select name="state" id="quickCustomerState" class="form-select form-select-sm" required>
                                <?php foreach (INDIAN_STATES as $code => $stName): ?>
                                    <option value="<?= e($stName) ?>" <?= (strcasecmp($stName, $companyState) === 0) ? 'selected' : '' ?>>
                                        <?= e($stName) ?> (<?= $code ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email (Optional)</label>
                            <input type="email" name="email" id="quickCustomerEmail" class="form-control form-control-sm" placeholder="customer@example.com">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Billing Address / City (Optional)</label>
                        <input type="text" name="address" id="quickCustomerAddress" class="form-control form-control-sm" placeholder="Street / Area / City">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitQuickCustomer" class="btn btn-sm btn-primary fw-bold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/billing.js') ?>"></script>
<script>
window.AVAILABLE_CUSTOMERS = <?= json_encode($customers) ?>;
window.AVAILABLE_ITEMS = <?= json_encode($items) ?>;
window.COMPANY_STATE_CODE = "<?= e($companyStateCode) ?>";
window.IS_INTERSTATE = false;
window.IS_PURCHASE = false;

function onCustomerSelected(customer) {
    if (!customer) return;
    const state = customer.state || '';
    const stateCode = customer.state_code || '27';
    const gstin = customer.gstin || '';
    const outstanding = parseFloat(customer.invoice_outstanding || 0);

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
        <span class="text-${outstanding > 0 ? 'danger' : 'success'} fw-bold">Due: ₹${outstanding.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
    `;

    calculateBillingTotals();
}

function onCustomerCleared() {
    document.getElementById('customerInfoBadge').innerHTML = '';
    window.IS_INTERSTATE = false;
    const isGst = window.GST_ENABLED;
    if (isGst) {
        const cgstEl = document.getElementById('cgstRow');
        const sgstEl = document.getElementById('sgstRow');
        const igstEl = document.getElementById('igstRow');
        if (cgstEl) cgstEl.style.display = 'table-row';
        if (sgstEl) sgstEl.style.display = 'table-row';
        if (igstEl) igstEl.style.display = 'none';
    }
    calculateBillingTotals();
}

function submitSalesInvoice(actionType) {
    const form = document.getElementById('salesInvoiceForm');
    const customerSelect = document.getElementById('customerSelect');

    if (!customerSelect || !customerSelect.value) {
        alert('Please type and select a customer from the suggestions.');
        const searchInput = document.getElementById('customerSearchInput');
        if (searchInput) searchInput.focus();
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

// Auto-focus first input when quick customer modal opens
const quickCustomerModalEl = document.getElementById('quickCustomerModal');
if (quickCustomerModalEl) {
    quickCustomerModalEl.addEventListener('shown.bs.modal', function () {
        document.getElementById('quickCustomerName')?.focus();
    });
}

// Quick Customer Form Submit via AJAX
const quickCustomerForm = document.getElementById('quickCustomerForm');
if (quickCustomerForm) {
    quickCustomerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const alertEl = document.getElementById('quickCustomerAlert');
        const submitBtn = document.getElementById('btnSubmitQuickCustomer');
        const originalBtnHtml = submitBtn.innerHTML;

        alertEl.classList.add('d-none');
        alertEl.textContent = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...';

        try {
            const formData = new FormData(this);
            const response = await fetch('<?= url('/customers/quick-store') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const text = await response.text();
            let res;
            try {
                res = JSON.parse(text);
            } catch (jsonErr) {
                console.error('Non-JSON response:', text);
                alertEl.textContent = 'Server response error: ' + (text.substring(0, 100) || 'Unknown response');
                alertEl.classList.remove('d-none');
                return;
            }

            if (!res.success) {
                alertEl.textContent = res.error || res.message || 'Failed to create customer.';
                alertEl.classList.remove('d-none');
                return;
            }

            const customer = res.data;
            if (!customer) {
                alertEl.textContent = 'Unexpected server response.';
                alertEl.classList.remove('d-none');
                return;
            }

            // Add new customer to available list
            if (Array.isArray(window.AVAILABLE_CUSTOMERS)) {
                window.AVAILABLE_CUSTOMERS.unshift(customer);
            }

            // Auto-select in sales form
            const customerSelect = document.getElementById('customerSelect');
            const customerSearchInput = document.getElementById('customerSearchInput');
            const btnClear = document.getElementById('btnClearCustomer');

            if (customerSelect) customerSelect.value = customer.id;
            if (customerSearchInput) customerSearchInput.value = customer.name + (customer.mobile ? ' (' + customer.mobile + ')' : '');
            if (btnClear) btnClear.classList.remove('d-none');

            // Trigger selection logic (interstate tax calculations, badge update, etc.)
            onCustomerSelected(customer);

            // Hide modal & reset form
            const modalInstance = window.bootstrap?.Modal?.getInstance(quickCustomerModalEl) || (window.bootstrap?.Modal ? new bootstrap.Modal(quickCustomerModalEl) : null);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                quickCustomerModalEl.querySelector('[data-bs-dismiss="modal"]')?.click();
            }
            quickCustomerForm.reset();

            // Notify user
            if (typeof showToast === 'function') {
                showToast(`Customer "${customer.name}" created and selected!`, 'success');
            }
        } catch (err) {
            console.error('Quick customer creation error:', err);
            alertEl.textContent = 'Network or server error occurred. Please try again.';
            alertEl.classList.remove('d-none');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
}

// Initialize customer type & select and 1 clean row on load
document.addEventListener('DOMContentLoaded', function() {
    initEntitySearch({
        inputId: 'customerSearchInput',
        hiddenId: 'customerSelect',
        dropdownId: 'customerDropdownList',
        clearBtnId: 'btnClearCustomer',
        dataList: window.AVAILABLE_CUSTOMERS,
        entityType: 'customer',
        onSelect: onCustomerSelected,
        onClear: onCustomerCleared
    });

    addBillingRow();
});
</script>
