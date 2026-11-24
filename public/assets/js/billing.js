/**
 * MYSLIP.IN — Dynamic Billing Grid & Real-time Tax Calculator
 */

let itemRowCount = 0;

function addBillingRow(itemData = null) {
    itemRowCount++;
    const tbody = document.getElementById('billingItemsBody');
    if (!tbody) return;

    const row = document.createElement('tr');
    row.id = `row_${itemRowCount}`;
    row.className = 'billing-row align-middle';

    const isGst = window.GST_ENABLED === true;
    const isInterstate = window.IS_INTERSTATE === true;

    row.innerHTML = `
        <td class="text-center row-index">${tbody.children.length + 1}</td>
        <td>
            <select name="items[${itemRowCount}][item_id]" class="form-select form-select-sm item-select" onchange="onItemSelectChange(this, ${itemRowCount})">
                <option value="">-- Select or Scan Item --</option>
                ${window.AVAILABLE_ITEMS ? window.AVAILABLE_ITEMS.map(i => `
                    <option value="${i.id}" 
                            data-code="${i.code || ''}" 
                            data-barcode="${i.barcode || ''}" 
                            data-hsn="${i.hsn_sac || ''}" 
                            data-unit="${i.unit_name || 'PCS'}" 
                            data-rate="${window.IS_PURCHASE ? i.purchase_rate : i.selling_rate}" 
                            data-gst="${i.gst_rate || 0}" 
                            data-stock="${i.current_stock || 0}">
                        ${i.name} (Stock: ${i.current_stock || 0})
                    </option>
                `).join('') : ''}
            </select>
            <input type="hidden" name="items[${itemRowCount}][item_name]" class="item-name-input">
            <input type="hidden" name="items[${itemRowCount}][barcode]" class="item-barcode-input">
        </td>
        ${isGst ? `
        <td>
            <input type="text" name="items[${itemRowCount}][hsn_sac]" class="form-control form-control-sm text-center item-hsn" placeholder="HSN/SAC">
        </td>` : ''}
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="items[${itemRowCount}][quantity]" class="form-control text-center item-qty" step="any" min="0" value="1" oninput="calculateBillingTotals()">
                <span class="input-group-text item-unit-label">PCS</span>
                <input type="hidden" name="items[${itemRowCount}][unit_name]" class="item-unit-input" value="PCS">
            </div>
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][rate]" class="form-control form-control-sm text-end item-rate" step="0.01" min="0" value="0.00" oninput="calculateBillingTotals()">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="items[${itemRowCount}][discount_percent]" class="form-control text-end item-disc-pct" step="0.01" min="0" max="100" value="0" oninput="calculateBillingTotals()">
                <span class="input-group-text">%</span>
            </div>
            <input type="hidden" name="items[${itemRowCount}][discount_amount]" class="item-disc-amt" value="0.00">
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][taxable_amount]" class="form-control form-control-sm text-end item-taxable bg-light" step="0.01" value="0.00" readonly>
        </td>
        ${isGst ? `
        <td>
            <select name="items[${itemRowCount}][gst_rate]" class="form-select form-select-sm text-center item-gst-rate" onchange="calculateBillingTotals()">
                <option value="0">0%</option>
                <option value="5">5%</option>
                <option value="12">12%</option>
                <option value="18" selected>18%</option>
                <option value="28">28%</option>
            </select>
            <input type="hidden" name="items[${itemRowCount}][cgst_rate]" class="item-cgst-rate" value="0">
            <input type="hidden" name="items[${itemRowCount}][cgst_amount]" class="item-cgst-amt" value="0.00">
            <input type="hidden" name="items[${itemRowCount}][sgst_rate]" class="item-sgst-rate" value="0">
            <input type="hidden" name="items[${itemRowCount}][sgst_amount]" class="item-sgst-amt" value="0.00">
            <input type="hidden" name="items[${itemRowCount}][igst_rate]" class="item-igst-rate" value="0">
            <input type="hidden" name="items[${itemRowCount}][igst_amount]" class="item-igst-amt" value="0.00">
        </td>` : ''}
        <td>
            <input type="number" name="items[${itemRowCount}][total_amount]" class="form-control form-control-sm text-end item-total fw-bold bg-light" step="0.01" value="0.00" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeBillingRow(${itemRowCount})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);

    if (itemData) {
        populateRowWithItem(row, itemData);
    }

    calculateBillingTotals();
}

function onItemSelectChange(selectElem, rowId) {
    const option = selectElem.options[selectElem.selectedIndex];
    const row = document.getElementById(`row_${rowId}`);
    if (!option || !option.value || !row) return;

    row.querySelector('.item-name-input').value = option.text.split(' (Stock:')[0].trim();
    row.querySelector('.item-barcode-input').value = option.getAttribute('data-barcode') || '';
    
    const hsnInput = row.querySelector('.item-hsn');
    if (hsnInput) hsnInput.value = option.getAttribute('data-hsn') || '';

    const unit = option.getAttribute('data-unit') || 'PCS';
    row.querySelector('.item-unit-label').innerText = unit;
    row.querySelector('.item-unit-input').value = unit;

    const rate = parseFloat(option.getAttribute('data-rate') || 0);
    row.querySelector('.item-rate').value = rate.toFixed(2);

    const gstSelect = row.querySelector('.item-gst-rate');
    if (gstSelect) {
        const gst = parseFloat(option.getAttribute('data-gst') || 0);
        gstSelect.value = gst;
    }

    calculateBillingTotals();
}

function removeBillingRow(rowId) {
    const row = document.getElementById(`row_${rowId}`);
    if (row) {
        row.remove();
        updateRowIndices();
        calculateBillingTotals();
    }
}

function updateRowIndices() {
    const rows = document.querySelectorAll('#billingItemsBody tr');
    rows.forEach((r, idx) => {
        const indexCol = r.querySelector('.row-index');
        if (indexCol) indexCol.innerText = idx + 1;
    });
}

function calculateBillingTotals() {
    const rows = document.querySelectorAll('#billingItemsBody tr');
    const isGst = window.GST_ENABLED === true;
    const isInterstate = window.IS_INTERSTATE === true;

    let grossTotal = 0.00;
    let itemDiscountTotal = 0.00;
    let taxableTotal = 0.00;
    let cgstTotal = 0.00;
    let sgstTotal = 0.00;
    let igstTotal = 0.00;

    rows.forEach(r => {
        const qty = parseFloat(r.querySelector('.item-qty')?.value || 0);
        const rate = parseFloat(r.querySelector('.item-rate')?.value || 0);
        const discPct = parseFloat(r.querySelector('.item-disc-pct')?.value || 0);
        const gstRate = parseFloat(r.querySelector('.item-gst-rate')?.value || 0);

        const lineGross = qty * rate;
        let lineDisc = 0.00;
        if (discPct > 0) {
            lineDisc = (lineGross * discPct) / 100;
        }

        const lineTaxable = Math.max(0, lineGross - lineDisc);

        let lineCgstRate = 0, lineCgstAmt = 0;
        let lineSgstRate = 0, lineSgstAmt = 0;
        let lineIgstRate = 0, lineIgstAmt = 0;
        let lineTaxAmt = 0;

        if (isGst && gstRate > 0) {
            if (isInterstate) {
                lineIgstRate = gstRate;
                lineIgstAmt = (lineTaxable * gstRate) / 100;
                lineTaxAmt = lineIgstAmt;
            } else {
                lineCgstRate = gstRate / 2;
                lineCgstAmt = (lineTaxable * lineCgstRate) / 100;
                lineSgstRate = gstRate / 2;
                lineSgstAmt = (lineTaxable * lineSgstRate) / 100;
                lineTaxAmt = lineCgstAmt + lineSgstAmt;
            }
        }

        const lineTotal = lineTaxable + lineTaxAmt;

        // Update row DOM
        if (r.querySelector('.item-disc-amt')) r.querySelector('.item-disc-amt').value = lineDisc.toFixed(2);
        if (r.querySelector('.item-taxable')) r.querySelector('.item-taxable').value = lineTaxable.toFixed(2);
        if (r.querySelector('.item-cgst-rate')) r.querySelector('.item-cgst-rate').value = lineCgstRate.toFixed(2);
        if (r.querySelector('.item-cgst-amt')) r.querySelector('.item-cgst-amt').value = lineCgstAmt.toFixed(2);
        if (r.querySelector('.item-sgst-rate')) r.querySelector('.item-sgst-rate').value = lineSgstRate.toFixed(2);
        if (r.querySelector('.item-sgst-amt')) r.querySelector('.item-sgst-amt').value = lineSgstAmt.toFixed(2);
        if (r.querySelector('.item-igst-rate')) r.querySelector('.item-igst-rate').value = lineIgstRate.toFixed(2);
        if (r.querySelector('.item-igst-amt')) r.querySelector('.item-igst-amt').value = lineIgstAmt.toFixed(2);
        if (r.querySelector('.item-total')) r.querySelector('.item-total').value = lineTotal.toFixed(2);

        grossTotal += lineGross;
        itemDiscountTotal += lineDisc;
        taxableTotal += lineTaxable;
        cgstTotal += lineCgstAmt;
        sgstTotal += lineSgstAmt;
        igstTotal += lineIgstAmt;
    });

    // Bill-level discount
    const billDiscPct = parseFloat(document.getElementById('billDiscountPercent')?.value || 0);
    let billDiscAmt = 0;
    if (billDiscPct > 0) {
        billDiscAmt = (taxableTotal * billDiscPct) / 100;
        taxableTotal = Math.max(0, taxableTotal - billDiscAmt);
    }
    const billDiscAmtElem = document.getElementById('billDiscountAmount');
    if (billDiscAmtElem) billDiscAmtElem.value = billDiscAmt.toFixed(2);

    const totalTax = cgstTotal + sgstTotal + igstTotal;
    const preRoundTotal = taxableTotal + totalTax;
    const grandTotal = Math.round(preRoundTotal);
    const roundOff = grandTotal - preRoundTotal;

    // Update Form Totals
    setElemVal('displayGrossAmount', grossTotal.toFixed(2));
    setElemVal('inputGrossAmount', grossTotal.toFixed(2));
    setElemVal('displayTaxableAmount', taxableTotal.toFixed(2));
    setElemVal('inputTaxableAmount', taxableTotal.toFixed(2));

    if (isGst) {
        setElemVal('displayCgstAmount', cgstTotal.toFixed(2));
        setElemVal('inputCgstAmount', cgstTotal.toFixed(2));
        setElemVal('displaySgstAmount', sgstTotal.toFixed(2));
        setElemVal('inputSgstAmount', sgstTotal.toFixed(2));
        setElemVal('displayIgstAmount', igstTotal.toFixed(2));
        setElemVal('inputIgstAmount', igstTotal.toFixed(2));
        setElemVal('displayTotalTaxAmount', totalTax.toFixed(2));
        setElemVal('inputTotalTaxAmount', totalTax.toFixed(2));
    }

    setElemVal('displayRoundOff', roundOff.toFixed(2));
    setElemVal('inputRoundOff', roundOff.toFixed(2));
    setElemVal('displayGrandTotal', grandTotal.toFixed(2));
    setElemVal('inputGrandTotal', grandTotal.toFixed(2));

    // Calculate Paid / Balance
    const paidInput = document.getElementById('inputPaidAmount');
    let paidAmt = parseFloat(paidInput?.value || 0);
    
    // Default auto-fill paid amount if cash or UPI selected
    const paymentMode = document.getElementById('paymentModeSelect')?.value;
    if ((paymentMode === 'cash' || paymentMode === 'upi' || paymentMode === 'bank') && paidInput && paidInput.dataset.manual !== 'true') {
        paidAmt = grandTotal;
        paidInput.value = grandTotal.toFixed(2);
    }

    const balanceAmt = Math.max(0, grandTotal - paidAmt);
    setElemVal('displayBalanceAmount', balanceAmt.toFixed(2));
    setElemVal('inputBalanceAmount', balanceAmt.toFixed(2));
}

function setElemVal(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
        el.value = val;
    } else {
        el.innerText = val;
    }
}

// Barcode Scan Handler with automatic quantity merge
function handleBarcodeScan(barcode) {
    barcode = barcode.trim();
    if (!barcode) return;

    // Check if barcode already exists in grid
    const existingRows = document.querySelectorAll('#billingItemsBody tr');
    for (let r of existingRows) {
        const rowBarcode = r.querySelector('.item-barcode-input')?.value;
        if (rowBarcode && rowBarcode.toLowerCase() === barcode.toLowerCase()) {
            const qtyInput = r.querySelector('.item-qty');
            if (qtyInput) {
                qtyInput.value = (parseFloat(qtyInput.value || 0) + 1).toFixed(2);
                calculateBillingTotals();
                showToast(`Increased quantity for item: ${r.querySelector('.item-name-input')?.value}`, 'info');
                return;
            }
        }
    }

    // Match barcode in available items catalog
    if (window.AVAILABLE_ITEMS) {
        const match = window.AVAILABLE_ITEMS.find(i => i.barcode && i.barcode.toLowerCase() === barcode.toLowerCase());
        if (match) {
            addBillingRow();
            const newRow = document.querySelector('#billingItemsBody tr:last-child');
            const select = newRow.querySelector('.item-select');
            select.value = match.id;
            onItemSelectChange(select, newRow.id.replace('row_', ''));
            showToast(`Added: ${match.name}`, 'success');
            return;
        }
    }

    showToast(`No item found matching barcode [${barcode}]`, 'warning');
}
