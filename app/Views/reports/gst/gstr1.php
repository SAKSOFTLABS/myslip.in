<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-warning"></i> GSTR-1 Outward Supplies Report</h4>
        <p class="text-muted small mb-0">Detailed B2B registered sales, B2C unregistered sales, and HSN summary for GST portal filing</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print GSTR-1
        </button>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="gstr1Tabs">
    <li class="nav-item">
        <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#b2bTab">
            <i class="bi bi-building me-1"></i> 4A, 4B - B2B Invoices (Registered) (<?= count($b2bInvoices) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold" data-bs-toggle="tab" href="#b2cTab">
            <i class="bi bi-person me-1"></i> 7 - B2C Small Invoices (Unregistered) (<?= count($b2cInvoices) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold" data-bs-toggle="tab" href="#hsnTab">
            <i class="bi bi-list-columns-reverse me-1"></i> 12 - HSN Summary
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- B2B Invoices -->
    <div class="tab-pane fade show active" id="b2bTab">
        <div class="card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>GSTIN of Recipient</th>
                                <th>Customer Name</th>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Place of Supply</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">Total Invoice Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($b2bInvoices)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted small">No B2B registered sales in this period.</td></tr>
                            <?php else: ?>
                                <?php foreach ($b2bInvoices as $inv): ?>
                                    <tr>
                                        <td><span class="badge bg-success-subtle text-success"><?= e($inv['customer_gstin']) ?></span></td>
                                        <td><strong><?= e($inv['customer_name']) ?></strong></td>
                                        <td><?= e($inv['invoice_number']) ?></td>
                                        <td><?= formatDate($inv['invoice_date']) ?></td>
                                        <td><?= e($inv['place_of_supply']) ?></td>
                                        <td class="text-end"><?= formatCurrency($inv['taxable_amount'], false) ?></td>
                                        <td class="text-end"><?= (float)$inv['cgst_amount'] > 0 ? formatCurrency($inv['cgst_amount'], false) : '-' ?></td>
                                        <td class="text-end"><?= (float)$inv['sgst_amount'] > 0 ? formatCurrency($inv['sgst_amount'], false) : '-' ?></td>
                                        <td class="text-end"><?= (float)$inv['igst_amount'] > 0 ? formatCurrency($inv['igst_amount'], false) : '-' ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($inv['grand_total'], false) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- B2C Invoices -->
    <div class="tab-pane fade" id="b2cTab">
        <div class="card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Place of Supply</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">Invoice Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($b2cInvoices)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted small">No B2C unregistered sales in this period.</td></tr>
                            <?php else: ?>
                                <?php foreach ($b2cInvoices as $inv): ?>
                                    <tr>
                                        <td><?= e($inv['invoice_number']) ?></td>
                                        <td><?= formatDate($inv['invoice_date']) ?></td>
                                        <td><?= e($inv['customer_name']) ?></td>
                                        <td><?= e($inv['place_of_supply']) ?></td>
                                        <td class="text-end"><?= formatCurrency($inv['taxable_amount'], false) ?></td>
                                        <td class="text-end"><?= (float)$inv['cgst_amount'] > 0 ? formatCurrency($inv['cgst_amount'], false) : '-' ?></td>
                                        <td class="text-end"><?= (float)$inv['sgst_amount'] > 0 ? formatCurrency($inv['sgst_amount'], false) : '-' ?></td>
                                        <td class="text-end"><?= (float)$inv['igst_amount'] > 0 ? formatCurrency($inv['igst_amount'], false) : '-' ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($inv['grand_total'], false) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- HSN Summary -->
    <div class="tab-pane fade" id="hsnTab">
        <div class="card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>HSN / SAC</th>
                                <th class="text-center">Total Quantity</th>
                                <th class="text-end">Total Value</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                                <th class="text-end">IGST</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hsnSummary)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted small">No HSN data found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($hsnSummary as $h): ?>
                                    <tr>
                                        <td><strong><?= e($h['hsn_sac'] ?: 'N/A') ?></strong></td>
                                        <td class="text-center"><?= formatQty($h['total_qty']) ?> <?= e($h['unit_name'] ?: 'PCS') ?></td>
                                        <td class="text-end"><?= formatCurrency($h['total_val'], false) ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($h['total_taxable'], false) ?></td>
                                        <td class="text-end"><?= formatCurrency($h['total_cgst'], false) ?></td>
                                        <td class="text-end"><?= formatCurrency($h['total_sgst'], false) ?></td>
                                        <td class="text-end"><?= formatCurrency($h['total_igst'], false) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
