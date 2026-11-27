/**
 * MYSLIP.IN — Dynamic Billing Grid, Type & Select Autocomplete, and Real-time Tax Calculator
 */

let itemRowCount = 0;
let currentHighlightedItemIndex = {};

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Type & Select Autocomplete for Customer and Vendor selection
 */
function initEntitySearch(config) {
    const searchInput = document.getElementById(config.inputId);
    const hiddenInput = document.getElementById(config.hiddenId);
    const dropdown = document.getElementById(config.dropdownId);
    const clearBtn = config.clearBtnId ? document.getElementById(config.clearBtnId) : null;
    const items = config.dataList || [];
    let highlightedIndex = -1;

    if (!searchInput || !hiddenInput || !dropdown) return;

    function renderOptions(filteredList) {
        if (!filteredList.length) {
            dropdown.innerHTML = `<div class="p-3 text-center text-muted small"><i class="bi bi-search me-1"></i> No matching ${config.entityType || 'entry'} found</div>`;
            dropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        filteredList.slice(0, 20).forEach((item, idx) => {
            const due = parseFloat(item.invoice_outstanding || item.bill_outstanding || 0);
            const dueLabel = config.entityType === 'vendor' ? 'Payable' : 'Due';
            html += `
                <div class="type-select-option ${idx === highlightedIndex ? 'active' : ''}" data-idx="${idx}" data-id="${item.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">${escapeHtml(item.name)}</div>
                            <div class="text-muted small">
                                ${item.mobile ? '<i class="bi bi-phone me-1"></i>' + escapeHtml(item.mobile) : 'No Phone'}
                                ${item.state ? ' • ' + escapeHtml(item.state) : ''}
                                ${item.gstin ? ' • GST: ' + escapeHtml(item.gstin) : ''}
                            </div>
                        </div>
                        <div class="text-end ps-2">
                            <span class="badge ${due > 0 ? (config.entityType === 'vendor' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') : 'bg-success-subtle text-success'}">
                                ${dueLabel}: ₹${due.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');

        dropdown.querySelectorAll('.type-select-option').forEach(el => {
            el.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const id = parseInt(this.getAttribute('data-id'));
                const selItem = items.find(it => parseInt(it.id) === id);
                if (selItem) {
                    selectEntity(selItem);
                }
            });
        });
    }

    function selectEntity(item) {
        hiddenInput.value = item.id;
        searchInput.value = item.name + (item.mobile ? ` (${item.mobile})` : '');
        searchInput.classList.remove('is-invalid');
        if (clearBtn) clearBtn.classList.remove('d-none');
        dropdown.classList.add('d-none');
        highlightedIndex = -1;

        if (typeof config.onSelect === 'function') {
            config.onSelect(item);
        }
    }

    function clearEntity() {
        hiddenInput.value = '';
        searchInput.value = '';
        if (clearBtn) clearBtn.classList.add('d-none');
        dropdown.classList.add('d-none');
        highlightedIndex = -1;

        if (typeof config.onClear === 'function') {
            config.onClear();
        }
        searchInput.focus();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearEntity();
        });
    }

    searchInput.addEventListener('focus', function() {
        const query = this.value.trim().toLowerCase();
        const filtered = query ? items.filter(it => 
            (it.name && it.name.toLowerCase().includes(query)) ||
            (it.mobile && it.mobile.includes(query)) ||
            (it.gstin && it.gstin.toLowerCase().includes(query))
        ) : items;
        highlightedIndex = -1;
        renderOptions(filtered);
    });

    searchInput.addEventListener('input', function() {
        if (hiddenInput.value) {
            hiddenInput.value = '';
            if (typeof config.onClear === 'function') {
                config.onClear();
            }
        }
        if (clearBtn) {
            clearBtn.classList.toggle('d-none', !this.value.trim());
        }

        const query = this.value.trim().toLowerCase();
        const filtered = query ? items.filter(it => 
            (it.name && it.name.toLowerCase().includes(query)) ||
            (it.mobile && it.mobile.includes(query)) ||
            (it.gstin && it.gstin.toLowerCase().includes(query))
        ) : items;
        highlightedIndex = -1;
        renderOptions(filtered);
    });

    searchInput.addEventListener('keydown', function(e) {
        const options = dropdown.querySelectorAll('.type-select-option');
        if (dropdown.classList.contains('d-none') || !options.length) {
            if (e.key === 'ArrowDown') {
                searchInput.dispatchEvent(new Event('focus'));
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = Math.min(highlightedIndex + 1, options.length - 1);
            updateHighlight(options);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = Math.max(highlightedIndex - 1, 0);
            updateHighlight(options);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && options[highlightedIndex]) {
                const id = parseInt(options[highlightedIndex].getAttribute('data-id'));
                const selItem = items.find(it => parseInt(it.id) === id);
                if (selItem) selectEntity(selItem);
            } else if (options.length === 1) {
                const id = parseInt(options[0].getAttribute('data-id'));
                const selItem = items.find(it => parseInt(it.id) === id);
                if (selItem) selectEntity(selItem);
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.add('d-none');
        }
    });

    function updateHighlight(options) {
        options.forEach((el, idx) => {
            el.classList.toggle('active', idx === highlightedIndex);
            if (idx === highlightedIndex) {
                el.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('d-none');
        }
    });
}

/**
 * Add a dynamic item billing row with Type & Select
 */
function addBillingRow(itemData = null) {
    itemRowCount++;
    const tbody = document.getElementById('billingItemsBody');
    if (!tbody) return;

    const row = document.createElement('tr');
    row.id = `row_${itemRowCount}`;
    row.className = 'billing-row align-middle';

    const isGst = window.GST_ENABLED === true;

    row.innerHTML = `
        <td class="text-center row-index">${tbody.children.length + 1}</td>
        <td class="position-relative item-cell">
            <input type="hidden" name="items[${itemRowCount}][item_id]" class="item-select item-id-input" value="">
            <input type="hidden" name="items[${itemRowCount}][item_name]" class="item-name-input" value="">
            <input type="hidden" name="items[${itemRowCount}][barcode]" class="item-barcode-input" value="">

            <div class="input-group input-group-sm">
                <input type="text" 
                       class="form-control form-control-sm item-search-input" 
                       placeholder="Type item name, code, barcode..." 
                       autocomplete="off" 
                       onfocus="openItemDropdown(${itemRowCount})" 
                       oninput="filterItemDropdown(this, ${itemRowCount})" 
                       onkeydown="navigateItemDropdown(event, ${itemRowCount})">
                <button type="button" class="btn btn-outline-secondary btn-clear-row-item p-0 px-2 d-none" onclick="clearItemRowSelection(${itemRowCount})" title="Clear item">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="item-dropdown-menu shadow-lg rounded-2 border d-none" id="itemDropdown_${itemRowCount}"></div>
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
        populateRowWithItem(itemRowCount, itemData);
    }

    calculateBillingTotals();
}

function closeAllItemDropdowns() {
    document.querySelectorAll('.item-dropdown-menu').forEach(el => el.classList.add('d-none'));
}

function openItemDropdown(rowId) {
    closeAllItemDropdowns();
    const row = document.getElementById(`row_${rowId}`);
    if (!row) return;

    const input = row.querySelector('.item-search-input');
    filterItemDropdown(input, rowId);
}

function filterItemDropdown(input, rowId) {
    const dropdown = document.getElementById(`itemDropdown_${rowId}`);
    const row = document.getElementById(`row_${rowId}`);
    if (!dropdown || !row) return;

    const idInput = row.querySelector('.item-id-input');
    const nameInput = row.querySelector('.item-name-input');
    const clearBtn = row.querySelector('.btn-clear-row-item');

    if (idInput && idInput.value && nameInput && input.value !== nameInput.value) {
        idInput.value = '';
    }
    if (clearBtn) {
        clearBtn.classList.toggle('d-none', !input.value.trim());
    }

    const query = input.value.trim().toLowerCase();
    const items = window.AVAILABLE_ITEMS || [];
    const isPurchase = window.IS_PURCHASE === true;

    const filtered = query ? items.filter(it => 
        (it.name && it.name.toLowerCase().includes(query)) ||
        (it.code && it.code.toLowerCase().includes(query)) ||
        (it.barcode && it.barcode.toLowerCase().includes(query)) ||
        (it.hsn_sac && it.hsn_sac.toLowerCase().includes(query))
    ) : items;

    currentHighlightedItemIndex[rowId] = -1;

    if (!filtered.length) {
        dropdown.innerHTML = `<div class="p-3 text-center text-muted small"><i class="bi bi-search me-1"></i> No items found matching "${escapeHtml(query)}"</div>`;
        dropdown.classList.remove('d-none');
        return;
    }

    let html = '';
    filtered.slice(0, 25).forEach((it, idx) => {
        const rate = parseFloat(isPurchase ? (it.purchase_rate || 0) : (it.selling_rate || 0));
        const stock = parseFloat(it.current_stock || 0);
        html += `
            <div class="item-dropdown-option p-2 border-bottom d-flex justify-content-between align-items-center" 
                 data-item-id="${it.id}" 
                 data-idx="${idx}"
                 onmousedown="selectRowItem(${rowId}, ${it.id})">
                <div class="flex-grow-1 me-2">
                    <div class="fw-bold text-dark item-name">${escapeHtml(it.name)}</div>
                    <div class="text-muted small">
                        ${it.code ? '<span class="me-2">Code: ' + escapeHtml(it.code) + '</span>' : ''}
                        ${it.barcode ? '<span class="me-2"><i class="bi bi-upc-scan"></i> ' + escapeHtml(it.barcode) + '</span>' : ''}
                        ${it.hsn_sac ? '<span class="me-2">HSN: ' + escapeHtml(it.hsn_sac) + '</span>' : ''}
                        <span>GST: ${it.gst_rate || 0}%</span>
                    </div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold text-primary">₹${rate.toFixed(2)}</div>
                    <span class="badge ${stock > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted'}">
                        Stock: ${stock} ${escapeHtml(it.unit_name || 'PCS')}
                    </span>
                </div>
            </div>
        `;
    });

    dropdown.innerHTML = html;
    dropdown.classList.remove('d-none');
}

function navigateItemDropdown(event, rowId) {
    const dropdown = document.getElementById(`itemDropdown_${rowId}`);
    if (!dropdown || dropdown.classList.contains('d-none')) {
        if (event.key === 'ArrowDown') {
            openItemDropdown(rowId);
        }
        return;
    }

    const options = dropdown.querySelectorAll('.item-dropdown-option');
    if (!options.length) return;

    let idx = currentHighlightedItemIndex[rowId] ?? -1;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        idx = Math.min(idx + 1, options.length - 1);
        currentHighlightedItemIndex[rowId] = idx;
        updateItemOptionHighlight(dropdown, idx);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        idx = Math.max(idx - 1, 0);
        currentHighlightedItemIndex[rowId] = idx;
        updateItemOptionHighlight(dropdown, idx);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (idx >= 0 && options[idx]) {
            const itemId = parseInt(options[idx].getAttribute('data-item-id'));
            selectRowItem(rowId, itemId);
        } else if (options.length === 1) {
            const itemId = parseInt(options[0].getAttribute('data-item-id'));
            selectRowItem(rowId, itemId);
        }
    } else if (event.key === 'Escape') {
        dropdown.classList.add('d-none');
    }
}

function updateItemOptionHighlight(dropdown, activeIdx) {
    const options = dropdown.querySelectorAll('.item-dropdown-option');
    options.forEach((el, i) => {
        el.classList.toggle('active', i === activeIdx);
        if (i === activeIdx) {
            el.scrollIntoView({ block: 'nearest' });
        }
    });
}

function selectRowItem(rowId, itemId) {
    const row = document.getElementById(`row_${rowId}`);
    if (!row) return;

    const items = window.AVAILABLE_ITEMS || [];
    const item = items.find(it => parseInt(it.id) === parseInt(itemId));
    if (!item) return;

    const isPurchase = window.IS_PURCHASE === true;
    const rate = parseFloat(isPurchase ? (item.purchase_rate || 0) : (item.selling_rate || 0));

    row.querySelector('.item-id-input').value = item.id;
    row.querySelector('.item-name-input').value = item.name;
    row.querySelector('.item-barcode-input').value = item.barcode || '';

    const searchInput = row.querySelector('.item-search-input');
    searchInput.value = item.name;
    searchInput.classList.remove('is-invalid');

    const clearBtn = row.querySelector('.btn-clear-row-item');
    if (clearBtn) clearBtn.classList.remove('d-none');

    const hsnInput = row.querySelector('.item-hsn');
    if (hsnInput) hsnInput.value = item.hsn_sac || '';

    const unit = item.unit_name || 'PCS';
    const unitLabel = row.querySelector('.item-unit-label');
    const unitInput = row.querySelector('.item-unit-input');
    if (unitLabel) unitLabel.innerText = unit;
    if (unitInput) unitInput.value = unit;

    const rateInput = row.querySelector('.item-rate');
    if (rateInput) rateInput.value = rate.toFixed(2);

    const gstSelect = row.querySelector('.item-gst-rate');
    if (gstSelect) {
        gstSelect.value = parseFloat(item.gst_rate || 0);
    }

    const dropdown = document.getElementById(`itemDropdown_${rowId}`);
    if (dropdown) dropdown.classList.add('d-none');

    calculateBillingTotals();

    // Auto-advance focus to Quantity field for rapid billing
    const qtyInput = row.querySelector('.item-qty');
    if (qtyInput) {
        setTimeout(() => {
            qtyInput.focus();
            qtyInput.select();
        }, 50);
    }
}

function clearItemRowSelection(rowId) {
    const row = document.getElementById(`row_${rowId}`);
    if (!row) return;

    row.querySelector('.item-id-input').value = '';
    row.querySelector('.item-name-input').value = '';
    row.querySelector('.item-barcode-input').value = '';

    const searchInput = row.querySelector('.item-search-input');
    searchInput.value = '';

    const clearBtn = row.querySelector('.btn-clear-row-item');
    if (clearBtn) clearBtn.classList.add('d-none');

    const hsnInput = row.querySelector('.item-hsn');
    if (hsnInput) hsnInput.value = '';

    const rateInput = row.querySelector('.item-rate');
    if (rateInput) rateInput.value = '0.00';

    const discInput = row.querySelector('.item-disc-pct');
    if (discInput) discInput.value = '0';

    const dropdown = document.getElementById(`itemDropdown_${rowId}`);
    if (dropdown) dropdown.classList.add('d-none');

    calculateBillingTotals();
    searchInput.focus();
}

function populateRowWithItem(rowId, itemData) {
    if (!itemData) return;
    if (itemData.id) {
        selectRowItem(rowId, itemData.id);
    }
    const row = document.getElementById(`row_${rowId}`);
    if (!row) return;

    if (itemData.quantity !== undefined) {
        row.querySelector('.item-qty').value = itemData.quantity;
    }
    if (itemData.rate !== undefined) {
        row.querySelector('.item-rate').value = itemData.rate;
    }
    if (itemData.discount_percent !== undefined) {
        row.querySelector('.item-disc-pct').value = itemData.discount_percent;
    }
    calculateBillingTotals();
}

function onItemSelectChange(selectElem, rowId) {
    // Kept for backward compatibility if any static view uses native select
    const option = selectElem.options[selectElem.selectedIndex];
    if (option && option.value) {
        selectRowItem(rowId, option.value);
    }
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

    if (window.AVAILABLE_ITEMS) {
        const match = window.AVAILABLE_ITEMS.find(i => i.barcode && i.barcode.toLowerCase() === barcode.toLowerCase());
        if (match) {
            addBillingRow();
            const newRow = document.querySelector('#billingItemsBody tr:last-child');
            const rowId = parseInt(newRow.id.replace('row_', ''));
            selectRowItem(rowId, match.id);
            showToast(`Added: ${match.name}`, 'success');
            return;
        }
    }

    showToast(`No item found matching barcode [${barcode}]`, 'warning');
}

// Global click to close item dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('.item-cell')) {
        closeAllItemDropdowns();
    }
});
