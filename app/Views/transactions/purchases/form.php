<?php
$gstEnabled = \App\Core\Session::get('gst_enabled', true);
$companyState = \App\Core\Session::get('company_state', 'Maharashtra');
$companyStateCode = \App\Core\Session::get('company_state_code', '27');
$availableUnits = $units ?? (new \App\Models\Unit())->getAvailableUnits(\App\Core\Session::getCompanyId() ?? 0);
$availableGroups = $groups ?? (new \App\Models\ItemGroup())->all([], 'name ASC');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-warning">
            <i class="bi bi-bag-plus me-1"></i> <?= !empty($purchase) ? 'Edit Purchase Bill: ' . e($purchase['purchase_number']) : 'New Purchase Bill' ?>
        </h4>
        <span class="text-muted small">Record inward inventory and calculate input tax credits</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/purchases') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bills
        </a>
    </div>
</div>

<form id="purchaseBillForm" action="<?= !empty($purchase) ? url('/purchases/update/' . $purchase['id']) : url('/purchases/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="card-custom mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <!-- Supplier / Vendor (Type & Select with Quick Shortcut) -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small mb-0">Supplier / Vendor <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-xs btn-outline-warning text-dark py-0 px-2 fw-semibold rounded-pill" data-bs-toggle="modal" data-bs-target="#quickVendorModal" title="Add New Supplier / Vendor">
                            <i class="bi bi-plus-circle-fill text-warning me-1"></i> New Vendor
                        </button>
                    </div>
                    <div class="type-select-wrapper position-relative">
                        <input type="hidden" name="vendor_id" id="vendorSelect" value="<?= !empty($purchase) ? (int)$purchase['vendor_id'] : '' ?>" required>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   id="vendorSearchInput" 
                                   class="form-control form-control-sm type-select-input" 
                                   placeholder="Type vendor name, phone, or GSTIN..." 
                                   value="<?= !empty($purchase) ? e($purchase['vendor_name'] . ($purchase['vendor_gstin'] ? ' (' . $purchase['vendor_gstin'] . ')' : '')) : '' ?>"
                                   autocomplete="off" 
                                   autofocus 
                                   required>
                            <button type="button" class="btn btn-outline-secondary btn-clear-selection <?= !empty($purchase) ? '' : 'd-none' ?>" id="btnClearVendor" title="Clear vendor">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button type="button" class="btn btn-warning text-dark fw-bold px-2" data-bs-toggle="modal" data-bs-target="#quickVendorModal" title="Quick Add Vendor">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <div class="type-select-dropdown shadow-lg rounded-2 border d-none" id="vendorDropdownList"></div>
                    </div>
                    <div id="vendorInfoBadge" class="small mt-1 text-muted"></div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Purchase #</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold text-warning" value="<?= !empty($purchase) ? e($purchase['purchase_number']) : e($nextPurchaseNumber) ?>" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Supplier Invoice No</label>
                    <input type="text" name="supplier_invoice_number" class="form-control form-control-sm" placeholder="e.g. REL-8941" value="<?= !empty($purchase) ? e($purchase['supplier_invoice_number']) : '' ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="form-control form-control-sm" value="<?= !empty($purchase) ? e($purchase['purchase_date']) : date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Payment Mode</label>
                    <select name="payment_mode" id="paymentModeSelect" class="form-select form-select-sm" onchange="calculateBillingTotals()">
                        <?php $pMode = $purchase['payment_mode'] ?? 'credit'; ?>
                        <option value="credit" <?= $pMode === 'credit' ? 'selected' : '' ?>>Credit (Pending)</option>
                        <option value="cash" <?= $pMode === 'cash' ? 'selected' : '' ?>>Cash Payment</option>
                        <option value="bank" <?= $pMode === 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="cheque" <?= $pMode === 'cheque' ? 'selected' : '' ?>>Cheque</option>
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
                <div class="col-md-7 text-end d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#quickProductModal" title="Quick Add New Product">
                        <i class="bi bi-box-seam me-1"></i> + Add Product
                    </button>
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
                    <textarea name="notes" class="form-control" rows="4" placeholder="Purchase notes..."><?= !empty($purchase) ? e($purchase['notes']) : '' ?></textarea>
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
                                    <input type="number" name="paid_amount" id="inputPaidAmount" class="form-control form-control-sm text-end fw-bold d-inline-block" style="max-width: 140px;" step="0.01" value="<?= !empty($purchase) ? (float)$purchase['paid_amount'] : '0.00' ?>" <?= !empty($purchase) ? 'data-manual="true"' : '' ?> oninput="this.dataset.manual='true'; calculateBillingTotals();">
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
                            <?= !empty($purchase) ? '<i class="bi bi-check-circle me-1"></i> Update Purchase Bill' : '<i class="bi bi-check2-circle me-1"></i> Save Purchase Bill' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Quick Add Vendor Modal -->
<div class="modal fade" id="quickVendorModal" tabindex="-1" aria-labelledby="quickVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-warning text-dark py-3">
                <h5 class="modal-title fw-bold" id="quickVendorModalLabel">
                    <i class="bi bi-building-add me-2"></i> Add New Supplier / Vendor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickVendorForm">
                <?= csrf_field() ?>
                <div class="modal-body p-3">
                    <div id="quickVendorAlert" class="alert alert-danger d-none py-2 small"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Supplier / Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="quickVendorName" class="form-control form-control-sm" placeholder="e.g. Reliable Traders Pvt Ltd" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile Number</label>
                            <input type="tel" name="mobile" id="quickVendorMobile" class="form-control form-control-sm" placeholder="10-digit mobile" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">GSTIN (Optional)</label>
                            <input type="text" name="gstin" id="quickVendorGstin" class="form-control form-control-sm text-uppercase" placeholder="27ABCDE1234F1Z5" maxlength="15">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">State <span class="text-danger">*</span></label>
                            <select name="state" id="quickVendorState" class="form-select form-select-sm" required>
                                <?php foreach (INDIAN_STATES as $code => $stName): ?>
                                    <option value="<?= e($stName) ?>" <?= (strcasecmp($stName, $companyState) === 0) ? 'selected' : '' ?>>
                                        <?= e($stName) ?> (<?= $code ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email (Optional)</label>
                            <input type="email" name="email" id="quickVendorEmail" class="form-control form-control-sm" placeholder="supplier@example.com">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Address / City (Optional)</label>
                        <input type="text" name="address" id="quickVendorAddress" class="form-control form-control-sm" placeholder="Shop / Office address">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitQuickVendor" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Create Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Add Product Modal -->
<div class="modal fade" id="quickProductModal" tabindex="-1" aria-labelledby="quickProductModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title fs-6 fw-bold" id="quickProductModalLabel">
                    <i class="bi bi-box-seam me-2"></i> Add New Product / Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickProductForm">
                <?= csrf_field() ?>
                <div class="modal-body p-3">
                    <div id="quickProductAlert" class="alert alert-danger d-none py-2 small"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Product / Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="quickProductName" class="form-control form-control-sm" placeholder="e.g. Wireless Mouse / Basmati Rice 1kg" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Item Code / SKU</label>
                            <input type="text" name="code" id="quickProductCode" class="form-control form-control-sm text-uppercase" placeholder="e.g. ITM-001">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Primary Unit <span class="text-danger">*</span></label>
                            <select name="unit_id" id="quickProductUnit" class="form-select form-select-sm" required>
                                <?php foreach ($availableUnits as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= (strtoupper($u['short_name'] ?? '') === 'PCS' || strtoupper($u['name'] ?? '') === 'PIECES') ? 'selected' : '' ?>>
                                        <?= e($u['name']) ?> (<?= e($u['short_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Category / Group</label>
                            <select name="group_id" id="quickProductGroup" class="form-select form-select-sm">
                                <option value="">-- General / No Group --</option>
                                <?php foreach ($availableGroups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Barcode</label>
                            <input type="text" name="barcode" id="quickProductBarcode" class="form-control form-control-sm" placeholder="Scan or type barcode">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Purchase / Cost Rate (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_rate" id="quickProductPurchaseRate" class="form-control form-control-sm text-end fw-bold" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Selling Rate (₹)</label>
                            <input type="number" step="0.01" min="0" name="selling_rate" id="quickProductSellingRate" class="form-control form-control-sm text-end" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Opening Stock Qty</label>
                            <input type="number" step="any" min="0" name="opening_stock" id="quickProductOpeningStock" class="form-control form-control-sm text-end" value="0">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" id="quickProductHsn" class="form-control form-control-sm" placeholder="e.g. 84713010">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">GST Rate (%)</label>
                            <select name="gst_rate" id="quickProductGstRate" class="form-select form-select-sm">
                                <option value="0">0% (Exempt / Nil)</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18" selected>18%</option>
                                <option value="28">28%</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitQuickProduct" class="btn btn-sm btn-success fw-bold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/billing.js') ?>"></script>
<script>
window.AVAILABLE_VENDORS = <?= json_encode($vendors) ?>;
window.AVAILABLE_ITEMS = <?= json_encode($items) ?>;
window.COMPANY_STATE_CODE = "<?= e($companyStateCode) ?>";
window.IS_INTERSTATE = false;
window.IS_PURCHASE = true; // Use purchase_rate when populating rows

function onVendorSelected(vendor) {
    if (!vendor) return;
    const state = vendor.state || '';
    const stateCode = vendor.state_code || '27';
    const gstin = vendor.gstin || '';
    const outstanding = parseFloat(vendor.bill_outstanding || 0);

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
        <span class="text-warning fw-bold">Payable: ₹${outstanding.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
    `;

    calculateBillingTotals();
}

function onVendorCleared() {
    document.getElementById('vendorInfoBadge').innerHTML = '';
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

function submitPurchaseBill() {
    const form = document.getElementById('purchaseBillForm');
    const vendorSelect = document.getElementById('vendorSelect');

    if (!vendorSelect || !vendorSelect.value) {
        alert('Please type and select a supplier / vendor from the suggestions.');
        const searchInput = document.getElementById('vendorSearchInput');
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

// Auto-focus first input when quick vendor modal opens
const quickVendorModalEl = document.getElementById('quickVendorModal');
if (quickVendorModalEl) {
    quickVendorModalEl.addEventListener('shown.bs.modal', function () {
        document.getElementById('quickVendorName')?.focus();
    });
}

// Quick Vendor Form Submit via AJAX
const quickVendorForm = document.getElementById('quickVendorForm');
if (quickVendorForm) {
    quickVendorForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const alertEl = document.getElementById('quickVendorAlert');
        const submitBtn = document.getElementById('btnSubmitQuickVendor');
        const originalBtnHtml = submitBtn.innerHTML;

        alertEl.classList.add('d-none');
        alertEl.textContent = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...';

        try {
            const formData = new FormData(this);
            const response = await fetch('<?= url('/vendors/quick-store') ?>', {
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
                alertEl.textContent = res.error || res.message || 'Failed to create vendor.';
                alertEl.classList.remove('d-none');
                return;
            }

            const vendor = res.data;
            if (!vendor) {
                alertEl.textContent = 'Unexpected server response.';
                alertEl.classList.remove('d-none');
                return;
            }

            // Add new vendor to available list
            if (Array.isArray(window.AVAILABLE_VENDORS)) {
                window.AVAILABLE_VENDORS.unshift(vendor);
            }

            // Auto-select in purchase form
            const vendorSelect = document.getElementById('vendorSelect');
            const vendorSearchInput = document.getElementById('vendorSearchInput');
            const btnClear = document.getElementById('btnClearVendor');

            if (vendorSelect) vendorSelect.value = vendor.id;
            if (vendorSearchInput) vendorSearchInput.value = vendor.name + (vendor.mobile ? ' (' + vendor.mobile + ')' : '');
            if (btnClear) btnClear.classList.remove('d-none');

            // Trigger selection logic (interstate tax calculations, badge update, etc.)
            onVendorSelected(vendor);

            // Hide modal & reset form
            const modalInstance = window.bootstrap?.Modal?.getInstance(quickVendorModalEl) || (window.bootstrap?.Modal ? new bootstrap.Modal(quickVendorModalEl) : null);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                quickVendorModalEl.querySelector('[data-bs-dismiss="modal"]')?.click();
            }
            quickVendorForm.reset();

            // Notify user
            if (typeof showToast === 'function') {
                showToast(`Vendor "${vendor.name}" created and selected!`, 'success');
            }
        } catch (err) {
            console.error('Quick vendor creation error:', err);
            alertEl.textContent = 'Network or server error occurred. Please try again.';
            alertEl.classList.remove('d-none');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
}

// Quick Product Submission Handler
const quickProductModalEl = document.getElementById('quickProductModal');
const quickProductForm = document.getElementById('quickProductForm');
if (quickProductModalEl && quickProductForm) {
    quickProductModalEl.addEventListener('shown.bs.modal', function() {
        document.getElementById('quickProductName')?.focus();
    });

    quickProductForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const alertEl = document.getElementById('quickProductAlert');
        const submitBtn = document.getElementById('btnSubmitQuickProduct');
        alertEl.classList.add('d-none');
        alertEl.textContent = '';

        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

        try {
            const formData = new FormData(quickProductForm);
            const response = await fetch('<?= url('/items/quick-store') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const res = await response.json();
            if (!response.ok || !res.success) {
                alertEl.textContent = res.message || 'Failed to create product.';
                alertEl.classList.remove('d-none');
                return;
            }

            const item = res.data;
            if (!item) {
                alertEl.textContent = 'Unexpected server response.';
                alertEl.classList.remove('d-none');
                return;
            }

            // Add new item to available items list
            if (Array.isArray(window.AVAILABLE_ITEMS)) {
                window.AVAILABLE_ITEMS.unshift(item);
            }

            // Find an empty row in the billing grid or add a new one
            let targetRowId = null;
            const rows = document.querySelectorAll('#billingItemsBody tr.billing-row');
            for (let i = 0; i < rows.length; i++) {
                const idInput = rows[i].querySelector('.item-id-input');
                const searchInput = rows[i].querySelector('.item-search-input');
                if ((!idInput || !idInput.value) && (!searchInput || !searchInput.value.trim())) {
                    const rowIdAttr = rows[i].id.replace('row_', '');
                    targetRowId = parseInt(rowIdAttr);
                    break;
                }
            }

            if (!targetRowId) {
                addBillingRow();
                targetRowId = itemRowCount;
            }

            // Auto-select newly created item in the grid row
            selectRowItem(targetRowId, item.id);

            // Hide modal & reset form
            const modalInstance = window.bootstrap?.Modal?.getInstance(quickProductModalEl) || (window.bootstrap?.Modal ? new bootstrap.Modal(quickProductModalEl) : null);
            if (modalInstance) {
                modalInstance.hide();
            } else {
                quickProductModalEl.querySelector('[data-bs-dismiss="modal"]')?.click();
            }
            quickProductForm.reset();

            // Notify user
            if (typeof showToast === 'function') {
                showToast(`Product "${item.name}" created and selected in billing grid!`, 'success');
            }
        } catch (err) {
            console.error('Quick product creation error:', err);
            alertEl.textContent = 'Network or server error occurred. Please try again.';
            alertEl.classList.remove('d-none');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
}

window.EXISTING_ITEMS = <?= !empty($purchase['items']) ? json_encode($purchase['items']) : '[]' ?>;
window.SELECTED_VENDOR = <?= !empty($purchase) ? json_encode([
    'id' => $purchase['vendor_id'],
    'name' => $purchase['vendor_name'],
    'state' => $purchase['vendor_state'] ?? '',
    'state_code' => $purchase['vendor_state_code'] ?? '27',
    'gstin' => $purchase['vendor_gstin'] ?? '',
    'bill_outstanding' => 0
]) : 'null' ?>;

document.addEventListener('DOMContentLoaded', function() {
    initEntitySearch({
        inputId: 'vendorSearchInput',
        hiddenId: 'vendorSelect',
        dropdownId: 'vendorDropdownList',
        clearBtnId: 'btnClearVendor',
        dataList: window.AVAILABLE_VENDORS,
        entityType: 'vendor',
        onSelect: onVendorSelected,
        onClear: onVendorCleared
    });

    if (window.SELECTED_VENDOR) {
        onVendorSelected(window.SELECTED_VENDOR);
    }

    if (Array.isArray(window.EXISTING_ITEMS) && window.EXISTING_ITEMS.length > 0) {
        window.EXISTING_ITEMS.forEach(it => {
            addBillingRow(it);
        });
    } else {
        addBillingRow();
    }
});
</script>
