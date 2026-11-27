<?php
/**
 * Standard Full Page A4 Invoice Template (Full Height Black Edition)
 */
$isGst = (bool)$company['gst_enabled'];
$primaryColor = '#000000';
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($upiString) : "";
?>

<style>
.invoice-a4-fullheight {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    min-height: 268mm !important;
    box-sizing: border-box !important;
    font-family: 'Segoe UI', Arial, sans-serif !important;
    color: #000000 !important;
    background: #ffffff !important;
}

.invoice-a4-fullheight .a4-top-section {
    flex-shrink: 0;
}

.invoice-a4-fullheight .a4-table-wrapper {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    margin: 8px 0 12px 0;
}

.invoice-a4-fullheight .a4-items-table {
    width: 100%;
    height: 100%;
    flex-grow: 1;
    border-collapse: collapse !important;
    border: 1.5px solid #000000 !important;
    margin-bottom: 0 !important;
}

.invoice-a4-fullheight .a4-items-table thead th {
    background-color: #f1f5f9 !important;
    color: #000000 !important;
    border: 1px solid #000000 !important;
    border-bottom: 1.5px solid #000000 !important;
    padding: 6px 8px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
}

.invoice-a4-fullheight .a4-items-table tbody tr.item-row {
    height: 1px !important;
}

.invoice-a4-fullheight .a4-items-table tbody tr.item-row td {
    border-left: 1px solid #000000 !important;
    border-right: 1px solid #000000 !important;
    border-top: none !important;
    border-bottom: 1px dashed #cbd5e1 !important;
    padding: 6px 8px !important;
    vertical-align: top !important;
    font-size: 11px !important;
    color: #000000 !important;
}

.invoice-a4-fullheight .a4-items-table tbody tr.spacer-row td {
    border-left: 1px solid #000000 !important;
    border-right: 1px solid #000000 !important;
    border-top: none !important;
    border-bottom: 1.5px solid #000000 !important;
    height: 100% !important;
    padding: 0 !important;
    background: transparent !important;
}

.invoice-a4-fullheight .a4-bottom-section {
    flex-shrink: 0;
}

.invoice-a4-fullheight .border-black {
    border-color: #000000 !important;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
    .invoice-a4-fullheight {
        min-height: 272mm !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
    }
    .a4-bottom-section {
        page-break-inside: avoid;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="invoice-a4-box print-container invoice-a4-fullheight">
    <!-- Format Switcher for Screen Preview -->
    <div class="no-print d-flex justify-content-end gap-2 mb-3 pb-2 border-bottom">
        <span class="small text-muted me-2 align-self-center">Print Template:</span>
        <a href="<?= url('/invoices/print/' . $sale['id'] . '?format=a4') ?>" class="btn btn-sm <?= $format === 'a4' ? 'btn-dark' : 'btn-outline-dark' ?>">A4 Full Page</a>
        <a href="<?= url('/invoices/print/' . $sale['id'] . '?format=a5') ?>" class="btn btn-sm <?= $format === 'a5' ? 'btn-dark' : 'btn-outline-dark' ?>">A5 Half Page</a>
        <a href="<?= url('/invoices/print/' . $sale['id'] . '?format=thermal') ?>" class="btn btn-sm <?= $format === 'thermal' ? 'btn-dark' : 'btn-outline-dark' ?>">3-Inch Thermal POS</a>
    </div>

    <!-- TOP SECTION: HEADER & CUSTOMER DETAILS -->
    <div class="a4-top-section">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom border-2 border-black">
            <div class="d-flex align-items-start gap-3" style="max-width: 65%;">
                <?php if (!empty($company['logo_url'])): ?>
                    <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 75px; max-width: 150px; object-fit: contain;" class="mt-1 flex-shrink-0">
                <?php endif; ?>
                <div>
                    <h3 class="fw-bold mb-1 text-dark"><?= e($company['company_name']) ?></h3>
                    <div class="small text-muted mb-1"><?= e($company['address_line1']) ?><?= $company['address_line2'] ? ', ' . e($company['address_line2']) : '' ?></div>
                    <div class="small text-muted mb-1"><?= e($company['city']) ?><?= $company['pin_code'] ? ' - ' . e($company['pin_code']) : '' ?>, <?= e($company['state']) ?> (State Code: <?= e($company['state_code']) ?>)</div>
                    <div class="small text-muted">Phone: <strong class="text-dark"><?= e($company['mobile']) ?></strong> | Email: <?= e($company['email'] ?: 'sales@business.com') ?></div>
                    <?php if ($isGst && $company['gstin']): ?>
                        <div class="small fw-bold text-dark mt-1">GSTIN: <span class="text-uppercase"><?= e($company['gstin']) ?></span> | PAN: <?= e($company['pan'] ?: 'N/A') ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-uppercase mb-1 text-dark">
                    <?= $isGst ? 'TAX INVOICE' : 'BILL OF SUPPLY' ?>
                </h4>
                <div class="small text-muted">Original for Recipient</div>
                <div class="fw-bold fs-6 text-dark mt-2">Invoice #: <?= e($sale['invoice_number']) ?></div>
                <div class="small text-dark">Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
                <?php if (!empty($sale['due_date']) && (float)($sale['balance_amount'] ?? 0) > 0): ?>
                    <div class="small text-muted">Due Date: <?= formatDate($sale['due_date']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Billed To Details -->
        <div class="row g-2 mb-3 pb-2 border-bottom border-black">
            <div class="col-7">
                <div class="small fw-bold text-uppercase text-dark mb-1">Billed To (Customer Details):</div>
                <h6 class="fw-bold mb-1 text-dark"><?= e($sale['customer_name']) ?></h6>
                <div class="small text-muted"><?= e($sale['place_of_supply']) ?> (State Code: <?= e($sale['customer_state_code']) ?>)</div>
                <div class="small text-muted">Mobile: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <?php if ($isGst && $sale['customer_gstin']): ?>
                    <div class="small fw-bold text-dark">GSTIN: <?= e($sale['customer_gstin']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-5 text-end small">
                <div class="mb-1"><span class="text-muted">Place of Supply:</span> <strong class="text-dark"><?= e($sale['place_of_supply']) ?></strong></div>
                <div class="mb-1"><span class="text-muted">Payment Mode:</span> <strong class="text-uppercase text-dark"><?= e($sale['payment_mode']) ?></strong></div>
                <?php if ($sale['reference_no']): ?>
                    <div><span class="text-muted">Ref / PO #:</span> <?= e($sale['reference_no']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: FULL-HEIGHT AUTO-ADJUSTING ITEMS TABLE -->
    <div class="a4-table-wrapper">
        <table class="table invoice-table a4-items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 35px;">#</th>
                    <th>Item Description</th>
                    <?php if ($isGst): ?><th class="text-center" style="width: 85px;">HSN/SAC</th><?php endif; ?>
                    <th class="text-center" style="width: 65px;">Qty</th>
                    <th class="text-end" style="width: 85px;">Rate (₹)</th>
                    <th class="text-end" style="width: 70px;">Disc (₹)</th>
                    <th class="text-end" style="width: 95px;">Taxable (₹)</th>
                    <?php if ($isGst): ?>
                        <th class="text-center" style="width: 65px;">GST %</th>
                        <th class="text-end" style="width: 80px;">Tax (₹)</th>
                    <?php endif; ?>
                    <th class="text-end" style="width: 105px;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                foreach ($sale['items'] as $item): 
                    $taxAmt = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
                ?>
                    <tr class="item-row">
                        <td class="text-center"><?= $count++ ?></td>
                        <td>
                            <strong><?= e($item['item_name']) ?></strong>
                            <?php if ($item['barcode']): ?><span class="text-muted small ms-1">(<?= e($item['barcode']) ?>)</span><?php endif; ?>
                        </td>
                        <?php if ($isGst): ?><td class="text-center"><?= e($item['hsn_sac'] ?: '-') ?></td><?php endif; ?>
                        <td class="text-center"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                        <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                        <td class="text-end"><?= (float)$item['discount_amount'] > 0 ? formatCurrency($item['discount_amount'], false) : '-' ?></td>
                        <td class="text-end"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                        <?php if ($isGst): ?>
                            <td class="text-center"><?= (float)$item['gst_rate'] ?>%</td>
                            <td class="text-end"><?= formatCurrency($taxAmt, false) ?></td>
                        <?php endif; ?>
                        <td class="text-end fw-bold"><?= formatCurrency($item['total_amount'], false) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Spacer row that expands and fills remaining vertical space down to footer -->
                <tr class="spacer-row">
                    <td></td>
                    <td></td>
                    <?php if ($isGst): ?><td></td><?php endif; ?>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <?php if ($isGst): ?>
                        <td></td>
                        <td></td>
                    <?php endif; ?>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- BOTTOM SECTION: TOTALS, BANKING, TERMS & SIGNATURE -->
    <div class="a4-bottom-section">
        <!-- Totals & Words Row -->
        <div class="row g-2 mb-3">
            <div class="col-7">
                <div class="p-2 bg-light border border-black rounded small mb-2">
                    <span class="text-muted">Total Amount in Words:</span><br>
                    <strong class="text-dark"><?= numberToWords($sale['grand_total']) ?></strong>
                </div>

                <!-- Bank Details Box & QR -->
                <?php if (!empty($settings['show_bank_details']) && !empty($company['bank_account_no'])): ?>
                    <div class="p-2 border border-black rounded small d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-uppercase mb-1 text-dark">Bank Payment Details:</div>
                            <div>Bank Name: <strong><?= e($company['bank_name']) ?></strong></div>
                            <div>A/c No: <strong><?= e($company['bank_account_no']) ?></strong></div>
                            <div>IFSC Code: <strong><?= e($company['bank_ifsc']) ?></strong> | Branch: <?= e($company['bank_branch'] ?: 'Main') ?></div>
                            <?php if ($company['upi_id']): ?>
                                <div>UPI ID: <strong><?= e($company['upi_id']) ?></strong></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
                            <div class="text-center ms-2">
                                <img src="<?= $qrUrl ?>" alt="UPI QR" style="width: 75px; height: 75px;">
                                <div style="font-size: 8px;" class="text-muted mt-1">Scan to Pay via UPI</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-5">
                <table class="table table-sm table-bordered mb-0 border-black" style="border: 1px solid #000000;">
                    <tbody>
                        <tr>
                            <td class="text-muted small">Taxable Value:</td>
                            <td class="text-end fw-semibold"><?= formatCurrency($sale['taxable_amount']) ?></td>
                        </tr>
                        <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                            <?php if ($sale['is_interstate']): ?>
                                <tr>
                                    <td class="text-muted small">Integrated GST (IGST):</td>
                                    <td class="text-end"><?= formatCurrency($sale['igst_amount']) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td class="text-muted small">Central GST (CGST):</td>
                                    <td class="text-end"><?= formatCurrency($sale['cgst_amount']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted small">State GST (SGST):</td>
                                    <td class="text-end"><?= formatCurrency($sale['sgst_amount']) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted small">Round Off (+/-):</td>
                            <td class="text-end"><?= formatCurrency($sale['round_off']) ?></td>
                        </tr>
                        <tr class="fw-bold text-white" style="background-color: #000000 !important; color: #ffffff !important;">
                            <td class="text-white" style="border: 1px solid #000000 !important;">Grand Total:</td>
                            <td class="text-end text-white fs-6" style="border: 1px solid #000000 !important;"><?= formatCurrency($sale['grand_total']) ?></td>
                        </tr>
                        <tr>
                            <td class="small text-muted">Paid Amount:</td>
                            <td class="text-end text-success fw-bold"><?= formatCurrency($sale['paid_amount']) ?></td>
                        </tr>
                        <?php if ((float)($sale['balance_amount'] ?? 0) > 0): ?>
                        <tr>
                            <td class="small text-muted">Balance Due:</td>
                            <td class="text-end text-danger fw-bold"><?= formatCurrency($sale['balance_amount']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Terms & Signature Footer -->
        <div class="row g-2 pt-2 border-top border-black mt-2">
            <div class="col-7 small">
                <div class="fw-bold text-dark mb-1">Terms & Conditions:</div>
                <div class="text-dark" style="font-size: 10px; line-height: 1.35;">
                    <?= nl2br(e($settings['terms_conditions'] ?? "1. Goods once sold will not be taken back.\n2. Subject to local jurisdiction.")) ?>
                </div>
                <div class="mt-2 text-muted" style="font-size: 9px;">
                    <?= e($settings['declaration'] ?? "We declare that this invoice shows the actual price of the goods described.") ?>
                </div>
            </div>
            <div class="col-5 text-end small d-flex flex-column justify-content-between">
                <div class="text-dark">For <strong><?= e($company['company_name']) ?></strong></div>
                <div class="mt-4">
                    <div class="fw-bold text-dark border-top border-black d-inline-block pt-1" style="min-width: 150px;">
                        <?= e($settings['signature_label'] ?? 'Authorized Signatory') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
