<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-warning"></i> GSTR-3B Tax Summary</h4>
        <p class="text-muted small mb-0">Summary of outward taxable supplies vs inward eligible Input Tax Credit (ITC)</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print GSTR-3B
        </button>
    </div>
</div>

<!-- 3.1 Outward Taxable Supplies -->
<div class="card-custom mb-4">
    <div class="card-header bg-primary text-white">
        <span class="fw-bold"><i class="bi bi-arrow-up-circle me-1"></i> 3.1 Details of Outward Supplies & Advances (Sales Tax Liability)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-custom table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nature of Supply</th>
                    <th class="text-end">Total Taxable Value (₹)</th>
                    <th class="text-end">Integrated Tax (IGST) ₹</th>
                    <th class="text-end">Central Tax (CGST) ₹</th>
                    <th class="text-end">State/UT Tax (SGST) ₹</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>(a) Outward taxable supplies (other than zero rated, nil rated and exempted)</strong></td>
                    <td class="text-end fw-bold"><?= formatCurrency($outward['taxable'], false) ?></td>
                    <td class="text-end text-primary fw-bold"><?= formatCurrency($outward['igst'], false) ?></td>
                    <td class="text-end text-primary fw-bold"><?= formatCurrency($outward['cgst'], false) ?></td>
                    <td class="text-end text-primary fw-bold"><?= formatCurrency($outward['sgst'], false) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 4. Eligible Input Tax Credit -->
<div class="card-custom mb-4">
    <div class="card-header bg-success text-white">
        <span class="fw-bold"><i class="bi bi-arrow-down-circle me-1"></i> 4. Eligible Input Tax Credit (ITC on Purchases & Expenses)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-custom table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Details</th>
                    <th class="text-end">Total Taxable Value (₹)</th>
                    <th class="text-end">Integrated Tax (IGST) ₹</th>
                    <th class="text-end">Central Tax (CGST) ₹</th>
                    <th class="text-end">State/UT Tax (SGST) ₹</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>(A) ITC Available (whether in full or part) - All other ITC (Purchases)</strong></td>
                    <td class="text-end fw-bold"><?= formatCurrency($inwardItc['taxable'], false) ?></td>
                    <td class="text-end text-success fw-bold"><?= formatCurrency($inwardItc['igst'], false) ?></td>
                    <td class="text-end text-success fw-bold"><?= formatCurrency($inwardItc['cgst'], false) ?></td>
                    <td class="text-end text-success fw-bold"><?= formatCurrency($inwardItc['sgst'], false) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 5. Net Tax Payable / Credit Carry Forward -->
<?php
$netCgst = max(0, (float)$outward['cgst'] - (float)$inwardItc['cgst']);
$netSgst = max(0, (float)$outward['sgst'] - (float)$inwardItc['sgst']);
$netIgst = max(0, (float)$outward['igst'] - (float)$inwardItc['igst']);
$netPayable = $netCgst + $netSgst + $netIgst;
?>
<div class="card-custom">
    <div class="card-header bg-dark text-white">
        <span class="fw-bold"><i class="bi bi-wallet2 me-1"></i> Net Tax Computation Summary</span>
    </div>
    <div class="card-body p-3">
        <div class="row text-center g-3">
            <div class="col-md-3">
                <div class="text-muted small">Total Output Tax Liability</div>
                <div class="fs-5 fw-bold text-primary"><?= formatCurrency($outward['total_tax']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Total Input Tax Credit (ITC)</div>
                <div class="fs-5 fw-bold text-success"><?= formatCurrency($inwardItc['total_tax']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Net Estimated Tax Payable (Cash Ledger)</div>
                <div class="fs-4 fw-bold text-danger"><?= formatCurrency($netPayable) ?></div>
            </div>
        </div>
    </div>
</div>
