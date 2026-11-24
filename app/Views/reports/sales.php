<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i> Sales Register Report</h4>
        <p class="text-muted small mb-0">Invoice-wise sales summary, tax breakdowns, and payment collection tracking</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="<?= url('/reports/sales?' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<div class="card-custom mb-4 no-print">
    <div class="card-body p-3">
        <form action="<?= url('/reports/sales') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Customer Filter</label>
                <select name="customer_id" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($selectedCustomer) && $selectedCustomer == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th class="text-end">Taxable Amount</th>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                        <?php endif; ?>
                        <th class="text-end">Round Off</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totTaxable = 0; $totCgst = 0; $totSgst = 0; $totIgst = 0; $totGrand = 0; $totPaid = 0; $totBal = 0;
                    if (empty($rows)): ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted small">No sales records in selected date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): 
                            $totTaxable += (float)$r['taxable_amount'];
                            $totCgst += (float)$r['cgst_amount'];
                            $totSgst += (float)$r['sgst_amount'];
                            $totIgst += (float)$r['igst_amount'];
                            $totGrand += (float)$r['grand_total'];
                            $totPaid += (float)$r['paid_amount'];
                            $totBal += (float)$r['balance_amount'];
                        ?>
                            <tr>
                                <td><?= formatDate($r['invoice_date']) ?></td>
                                <td><strong><?= e($r['invoice_number']) ?></strong></td>
                                <td><?= e($r['customer_name']) ?></td>
                                <td class="text-end"><?= formatCurrency($r['taxable_amount'], false) ?></td>
                                <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                    <td class="text-end"><?= (float)$r['cgst_amount'] > 0 ? formatCurrency($r['cgst_amount'], false) : '-' ?></td>
                                    <td class="text-end"><?= (float)$r['sgst_amount'] > 0 ? formatCurrency($r['sgst_amount'], false) : '-' ?></td>
                                    <td class="text-end"><?= (float)$r['igst_amount'] > 0 ? formatCurrency($r['igst_amount'], false) : '-' ?></td>
                                <?php endif; ?>
                                <td class="text-end"><?= formatCurrency($r['round_off'], false) ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($r['grand_total'], false) ?></td>
                                <td class="text-end text-success"><?= formatCurrency($r['paid_amount'], false) ?></td>
                                <td class="text-end text-danger"><?= formatCurrency($r['balance_amount'], false) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="3" class="text-end text-white">Summary Totals:</td>
                        <td class="text-end"><?= formatCurrency($totTaxable, false) ?></td>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <td class="text-end"><?= formatCurrency($totCgst, false) ?></td>
                            <td class="text-end"><?= formatCurrency($totSgst, false) ?></td>
                            <td class="text-end"><?= formatCurrency($totIgst, false) ?></td>
                        <?php endif; ?>
                        <td class="text-end">-</td>
                        <td class="text-end text-white fs-6"><?= formatCurrency($totGrand) ?></td>
                        <td class="text-end text-success fs-6"><?= formatCurrency($totPaid) ?></td>
                        <td class="text-end text-danger fs-6"><?= formatCurrency($totBal) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
