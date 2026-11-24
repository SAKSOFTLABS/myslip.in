<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-journal-plus me-1"></i> New Double-Entry Journal Voucher</h4>
        <span class="text-muted small">Total Debit must exactly equal Total Credit</span>
    </div>
    <a href="<?= url('/vouchers/journals') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Journals
    </a>
</div>

<form id="journalForm" action="<?= url('/vouchers/journals/store') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="card-custom mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Journal Date <span class="text-danger">*</span></label>
                    <input type="date" name="journal_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required autofocus>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold small">General Narration <span class="text-danger">*</span></label>
                    <input type="text" name="narration" class="form-control form-control-sm" placeholder="e.g. Being depreciation on computers recorded for the year" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Journal Grid -->
    <div class="card-custom mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold">Journal Lines</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addJournalRow()">
                <i class="bi bi-plus-lg me-1"></i> Add Line
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table billing-grid-table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th style="min-width: 260px;">Particulars (Account)</th>
                            <th style="width: 160px;" class="text-end">Debit (Dr) ₹</th>
                            <th style="width: 160px;" class="text-end">Credit (Cr) ₹</th>
                            <th>Line Narration (Optional)</th>
                            <th style="width: 50px;" class="text-center"><i class="bi bi-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody id="journalGridBody"></tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Totals:</td>
                            <td class="text-end text-primary" id="totalDebitCell">₹ 0.00</td>
                            <td class="text-end text-primary" id="totalCreditCell">₹ 0.00</td>
                            <td colspan="2">
                                <span id="balanceIndicator" class="badge bg-danger">Difference: ₹ 0.00 (Unbalanced)</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= url('/vouchers/journals') ?>" class="btn btn-light border">Cancel</a>
        <button type="submit" id="saveJournalBtn" class="btn btn-primary fw-bold px-4" disabled>
            <i class="bi bi-check2-circle me-1"></i> Save & Post Journal
        </button>
    </div>
</form>

<script>
const AVAILABLE_ACCOUNTS = <?= json_encode($accounts) ?>;
let journalRowCount = 0;

function addJournalRow(defaultType = 'debit') {
    journalRowCount++;
    const tbody = document.getElementById('journalGridBody');
    const row = document.createElement('tr');
    row.id = `jrow_${journalRowCount}`;
    row.className = 'align-middle';

    row.innerHTML = `
        <td class="text-center row-num">${tbody.children.length + 1}</td>
        <td>
            <select name="lines[${journalRowCount}][account_id]" class="form-select form-select-sm" required>
                <option value="">-- Select Account --</option>
                ${AVAILABLE_ACCOUNTS.map(a => `<option value="${a.id}">${a.account_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="number" name="lines[${journalRowCount}][debit]" class="form-control form-control-sm text-end j-debit" step="0.01" min="0" value="0.00" oninput="onDebitInput(this, ${journalRowCount})" required>
        </td>
        <td>
            <input type="number" name="lines[${journalRowCount}][credit]" class="form-control form-control-sm text-end j-credit" step="0.01" min="0" value="0.00" oninput="onCreditInput(this, ${journalRowCount})" required>
        </td>
        <td>
            <input type="text" name="lines[${journalRowCount}][narration]" class="form-control form-control-sm" placeholder="Line narration...">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeJournalRow(${journalRowCount})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    calculateJournalTotals();
}

function onDebitInput(input, rowId) {
    const val = parseFloat(input.value || 0);
    if (val > 0) {
        document.getElementById(`jrow_${rowId}`).querySelector('.j-credit').value = '0.00';
    }
    calculateJournalTotals();
}

function onCreditInput(input, rowId) {
    const val = parseFloat(input.value || 0);
    if (val > 0) {
        document.getElementById(`jrow_${rowId}`).querySelector('.j-debit').value = '0.00';
    }
    calculateJournalTotals();
}

function removeJournalRow(rowId) {
    const r = document.getElementById(`jrow_${rowId}`);
    if (r) {
        r.remove();
        updateJournalRowNumbers();
        calculateJournalTotals();
    }
}

function updateJournalRowNumbers() {
    document.querySelectorAll('#journalGridBody tr').forEach((r, idx) => {
        r.querySelector('.row-num').innerText = idx + 1;
    });
}

function calculateJournalTotals() {
    let totDebit = 0.00;
    let totCredit = 0.00;

    document.querySelectorAll('#journalGridBody tr').forEach(r => {
        const d = parseFloat(r.querySelector('.j-debit')?.value || 0);
        const c = parseFloat(r.querySelector('.j-credit')?.value || 0);
        totDebit += d;
        totCredit += c;
    });

    totDebit = Math.round(totDebit * 100) / 100;
    totCredit = Math.round(totCredit * 100) / 100;

    document.getElementById('totalDebitCell').innerText = '₹ ' + totDebit.toFixed(2);
    document.getElementById('totalCreditCell').innerText = '₹ ' + totCredit.toFixed(2);

    const diff = Math.abs(totDebit - totCredit);
    const badge = document.getElementById('balanceIndicator');
    const saveBtn = document.getElementById('saveJournalBtn');

    if (totDebit > 0 && diff < 0.01) {
        badge.className = 'badge bg-success';
        badge.innerText = 'Balanced: Total Debit == Total Credit';
        saveBtn.disabled = false;
    } else {
        badge.className = 'badge bg-danger';
        badge.innerText = `Difference: ₹ ${diff.toFixed(2)} (Unbalanced)`;
        saveBtn.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addJournalRow('debit');
    addJournalRow('credit');
});
</script>
