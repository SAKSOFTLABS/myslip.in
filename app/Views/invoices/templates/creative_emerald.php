<?php
/**
 * Creative Emerald Invoice Template (A4 - Paid Template)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$emerald = '#059669';
$emeraldDark = '#065f46';
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>
<div class="invoice-creative-emerald print-container p-4 bg-white" style="font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b;">
    <!-- Emerald Accent Top Strip -->
    <div style="height: 6px; background: linear-gradient(90deg, #10b981 0%, #059669 100%); margin: -1.5rem -1.5rem 1.5rem -1.5rem;"></div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-start gap-3">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 75px; max-width: 150px; object-fit: contain;" class="mt-1 flex-shrink-0">
            <?php endif; ?>
            <div>
                <span class="badge px-3 py-1 text-white fw-bold text-uppercase mb-2" style="background-color: <?= $emerald ?>; font-size: 11px;">
                    <?= $isGst ? 'GST TAX INVOICE' : 'INVOICE BILL' ?>
                </span>
                <h2 class="fw-bold mb-1" style="color: <?= $emeraldDark ?>;"><?= e($company['company_name']) ?></h2>
                <div class="small text-muted"><?= e($company['address_line1'] ?? '') ?>, <?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?></div>
                <div class="small text-muted">Phone: <strong><?= e($company['mobile'] ?? '') ?></strong> | Email: <?= e($company['email'] ?? '') ?></div>
                <?php if ($isGst && !empty($company['gstin'])): ?>
                    <div class="small fw-bold mt-1 text-dark">GSTIN: <span class="text-uppercase"><?= e($company['gstin']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <div class="fs-4 fw-bold" style="color: <?= $emerald ?>;"><?= e($sale['invoice_number']) ?></div>
            <div class="small text-muted mt-1">Invoice Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
            <?php if (!empty($sale['due_date']) && (float)($sale['balance_amount'] ?? 0) > 0): ?>
                <div class="small text-muted">Due Date: <strong><?= formatDate($sale['due_date']) ?></strong></div>
            <?php endif; ?>
            <div class="small text-muted">Payment: <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
        </div>
    </div>

    <!-- Customer Card -->
    <div class="p-3 rounded-3 mb-4" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
        <div class="row g-2">
            <div class="col-7">
                <span class="small fw-bold text-uppercase" style="color: <?= $emeraldDark ?>; font-size: 10px;">CLIENT / RECIPIENT</span>
                <h5 class="fw-bold mb-1 text-dark"><?= e($sale['customer_name']) ?></h5>
                <div class="small text-muted"><?= e($sale['place_of_supply']) ?> (State Code: <?= e($sale['customer_state_code']) ?>)</div>
                <div class="small text-muted">Phone: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <?php if ($isGst && !empty($sale['customer_gstin'])): ?>
                    <div class="small fw-bold text-dark mt-1">GSTIN: <?= e($sale['customer_gstin']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-5 text-end small">
                <div>Place of Supply: <strong><?= e($sale['place_of_supply']) ?></strong></div>
                <?php if (!empty($sale['reference_no'])): ?>
                    <div>Order Ref: <strong><?= e($sale['reference_no']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table class="table mb-4">
        <thead style="background-color: #059669; color: #ffffff;">
            <tr>
                <th class="py-2 text-center" style="width: 35px;">#</th>
                <th class="py-2">Item Description</th>
                <?php if ($isGst): ?><th class="py-2 text-center" style="width: 75px;">HSN</th><?php endif; ?>
                <th class="py-2 text-center" style="width: 65px;">Qty</th>
                <th class="py-2 text-end" style="width: 85px;">Rate</th>
                <th class="py-2 text-end" style="width: 75px;">Disc</th>
                <th class="py-2 text-end" style="width: 95px;">Taxable</th>
                <?php if ($isGst): ?>
                    <th class="py-2 text-center" style="width: 65px;">GST</th>
                    <th class="py-2 text-end" style="width: 80px;">Tax</th>
                <?php endif; ?>
                <th class="py-2 text-end pe-3" style="width: 105px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 1;
            foreach ($sale['items'] as $item): 
                $taxAmt = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
            ?>
                <tr class="border-bottom border-light">
                    <td class="text-center text-muted"><?= $count++ ?></td>
                    <td><strong><?= e($item['item_name']) ?></strong></td>
                    <?php if ($isGst): ?><td class="text-center text-muted"><?= e($item['hsn_sac'] ?: '-') ?></td><?php endif; ?>
                    <td class="text-center"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                    <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                    <td class="text-end text-muted"><?= (float)$item['discount_amount'] > 0 ? formatCurrency($item['discount_amount'], false) : '-' ?></td>
                    <td class="text-end"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                    <?php if ($isGst): ?>
                        <td class="text-center"><?= (float)$item['gst_rate'] ?>%</td>
                        <td class="text-end"><?= formatCurrency($taxAmt, false) ?></td>
                    <?php endif; ?>
                    <td class="text-end pe-3 fw-bold"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals & Remittance -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="p-3 rounded-2 mb-2" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
                <div class="small text-muted mb-1">Total in Words:</div>
                <div class="fw-bold" style="color: <?= $emeraldDark ?>;"><?= numberToWords($sale['grand_total']) ?></div>
            </div>
            <?php if (!empty($company['bank_account_no'])): ?>
                <div class="p-2 border rounded-2 small d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-uppercase" style="color: <?= $emeraldDark ?>;">Bank Remittance:</div>
                        <div>Bank: <strong><?= e($company['bank_name']) ?></strong> | A/c: <strong><?= e($company['bank_account_no']) ?></strong></div>
                        <div>IFSC: <strong><?= e($company['bank_ifsc']) ?></strong> | UPI: <strong><?= e($company['upi_id'] ?: 'N/A') ?></strong></div>
                    </div>
                    <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
                        <img src="<?= $qrUrl ?>" alt="QR" style="width: 70px; height: 70px;" class="rounded border ms-2">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Taxable Value:</span> <span><?= formatCurrency($sale['taxable_amount']) ?></span></div>
                <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">GST Total:</span> <span><?= formatCurrency($sale['total_tax_amount']) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center p-2 rounded-2 text-white mb-2" style="background-color: <?= $emerald ?>;">
                    <span class="fw-bold">Total Amount</span>
                    <span class="fs-5 fw-bold"><?= formatCurrency($sale['grand_total']) ?></span>
                </div>
                <div class="d-flex justify-content-between small text-success fw-semibold"><span>Paid:</span> <span><?= formatCurrency($sale['paid_amount']) ?></span></div>
                <?php if ((float)($sale['balance_amount'] ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between small text-danger fw-semibold"><span>Balance:</span> <span><?= formatCurrency($sale['balance_amount']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="d-flex justify-content-between align-items-end pt-3 border-top text-muted small">
        <div style="max-width: 60%; font-size: 10px;">
            <?= nl2br(e($settings['terms_conditions'] ?? '1. Goods once sold will not be taken back.')) ?>
        </div>
        <div class="text-end">
            <div class="small mb-4">Authorized Signature for <strong><?= e($company['company_name']) ?></strong></div>
            <div class="border-top pt-1 fw-bold text-dark"><?= e($settings['signature_label'] ?? 'Authorized Signatory') ?></div>
        </div>
    </div>
</div>
