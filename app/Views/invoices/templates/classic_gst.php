<?php
/**
 * Classic GST Invoice Template (A4)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$primaryColor = $settings['primary_color'] ?? '#1e3a8a';
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>
<div class="invoice-a4-box print-container p-4 bg-white" style="font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b;">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom border-2" style="border-color: <?= e($primaryColor) ?> !important;">
        <div style="max-width: 60%;">
            <h3 class="fw-bold mb-1" style="color: <?= e($primaryColor) ?>;"><?= e($company['company_name']) ?></h3>
            <div class="small text-muted mb-1"><?= e($company['address_line1'] ?? '') ?><?= !empty($company['address_line2']) ? ', ' . e($company['address_line2']) : '' ?></div>
            <div class="small text-muted mb-1"><?= e($company['city'] ?? '') ?><?= !empty($company['pin_code']) ? ' - ' . e($company['pin_code']) : '' ?>, <?= e($company['state'] ?? '') ?> (State Code: <?= e($company['state_code'] ?? '') ?>)</div>
            <div class="small text-muted">Phone: <strong><?= e($company['mobile'] ?? '') ?></strong> | Email: <?= e($company['email'] ?: 'sales@business.com') ?></div>
            <?php if ($isGst && !empty($company['gstin'])): ?>
                <div class="small fw-bold text-dark mt-1">GSTIN: <span class="text-uppercase"><?= e($company['gstin']) ?></span> | PAN: <?= e($company['pan'] ?: 'N/A') ?></div>
            <?php endif; ?>
        </div>
        <div class="text-end">
            <h4 class="fw-bold text-uppercase mb-1" style="color: <?= e($primaryColor) ?>;">
                <?= $isGst ? 'TAX INVOICE' : 'BILL OF SUPPLY' ?>
            </h4>
            <div class="small text-muted">Original for Recipient</div>
            <div class="fw-bold fs-6 text-dark mt-2">Invoice #: <?= e($sale['invoice_number']) ?></div>
            <div class="small">Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
            <?php if (!empty($sale['due_date'])): ?>
                <div class="small text-muted">Due Date: <?= formatDate($sale['due_date']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Billed To Details -->
    <div class="row g-2 mb-3 pb-2 border-bottom">
        <div class="col-7">
            <div class="small fw-bold text-uppercase text-muted mb-1">Billed To (Customer Details):</div>
            <h6 class="fw-bold mb-1 text-dark"><?= e($sale['customer_name']) ?></h6>
            <div class="small text-muted"><?= e($sale['place_of_supply']) ?> (State Code: <?= e($sale['customer_state_code']) ?>)</div>
            <div class="small text-muted">Mobile: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
            <?php if ($isGst && !empty($sale['customer_gstin'])): ?>
                <div class="small fw-bold text-dark">GSTIN: <?= e($sale['customer_gstin']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-5 text-end small">
            <div class="mb-1"><span class="text-muted">Place of Supply:</span> <strong><?= e($sale['place_of_supply']) ?></strong></div>
            <div class="mb-1"><span class="text-muted">Payment Mode:</span> <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
            <?php if (!empty($sale['reference_no'])): ?>
                <div><span class="text-muted">Ref / PO #:</span> <?= e($sale['reference_no']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Line Items Table -->
    <table class="table invoice-table mb-3" style="border: 1px solid #cbd5e1;">
        <thead style="background-color: #f8fafc; border-bottom: 2px solid #cbd5e1;">
            <tr>
                <th class="text-center" style="width: 35px;">#</th>
                <th>Item Description</th>
                <?php if ($isGst): ?><th class="text-center" style="width: 80px;">HSN/SAC</th><?php endif; ?>
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
                <tr>
                    <td class="text-center"><?= $count++ ?></td>
                    <td>
                        <strong><?= e($item['item_name']) ?></strong>
                        <?php if (!empty($item['barcode'])): ?><span class="text-muted small ms-1">(<?= e($item['barcode']) ?>)</span><?php endif; ?>
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
        </tbody>
    </table>

    <!-- Totals & Banking Row -->
    <div class="row g-2 mb-3">
        <div class="col-7">
            <div class="p-2 bg-light border rounded small mb-2">
                <span class="text-muted">Total Amount in Words:</span><br>
                <strong class="text-dark"><?= numberToWords($sale['grand_total']) ?></strong>
            </div>

            <?php if (!empty($settings['show_bank_details']) && !empty($company['bank_account_no'])): ?>
                <div class="p-2 border rounded small d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-uppercase mb-1" style="color: <?= e($primaryColor) ?>;">Bank Payment Details:</div>
                        <div>Bank Name: <strong><?= e($company['bank_name']) ?></strong></div>
                        <div>A/c No: <strong><?= e($company['bank_account_no']) ?></strong></div>
                        <div>IFSC Code: <strong><?= e($company['bank_ifsc']) ?></strong> | Branch: <?= e($company['bank_branch'] ?: 'Main') ?></div>
                        <?php if (!empty($company['upi_id'])): ?>
                            <div>UPI ID: <strong><?= e($company['upi_id']) ?></strong></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
                        <div class="text-center ms-2">
                            <img src="<?= $qrUrl ?>" alt="UPI QR" style="width: 75px; height: 75px;">
                            <div style="font-size: 8px;" class="text-muted mt-1">Scan to Pay UPI</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-5">
            <table class="table table-sm table-bordered mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted small">Taxable Value:</td>
                        <td class="text-end fw-semibold"><?= formatCurrency($sale['taxable_amount']) ?></td>
                    </tr>
                    <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                        <?php if (!empty($sale['is_interstate'])): ?>
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
                    <tr class="table-dark fw-bold" style="background-color: <?= e($primaryColor) ?> !important;">
                        <td class="text-white">Grand Total:</td>
                        <td class="text-end text-white fs-6"><?= formatCurrency($sale['grand_total']) ?></td>
                    </tr>
                    <tr>
                        <td class="small text-muted">Paid Amount:</td>
                        <td class="text-end text-success fw-bold"><?= formatCurrency($sale['paid_amount']) ?></td>
                    </tr>
                    <tr>
                        <td class="small text-muted">Balance Due:</td>
                        <td class="text-end text-danger fw-bold"><?= formatCurrency($sale['balance_amount']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer & Terms -->
    <div class="row g-2 pt-2 border-top mt-3">
        <div class="col-7 small">
            <div class="fw-bold text-muted mb-1">Terms & Conditions:</div>
            <div class="text-muted" style="font-size: 10px; line-height: 1.3;">
                <?= nl2br(e($settings['terms_conditions'] ?? "1. Goods once sold will not be taken back.\n2. Subject to local jurisdiction.")) ?>
            </div>
            <div class="mt-2 text-muted" style="font-size: 9px;">
                <?= e($settings['declaration'] ?? "We declare that this invoice shows the actual price of the goods described.") ?>
            </div>
        </div>
        <div class="col-5 text-end small d-flex flex-column justify-content-between">
            <div class="text-muted">For <strong><?= e($company['company_name']) ?></strong></div>
            <div class="mt-4">
                <div class="fw-bold text-dark border-top d-inline-block pt-1" style="min-width: 140px;">
                    <?= e($settings['signature_label'] ?? 'Authorized Signatory') ?>
                </div>
            </div>
        </div>
    </div>
</div>
