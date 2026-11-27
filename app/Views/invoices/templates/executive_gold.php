<?php
/**
 * Executive Gold Luxury Invoice Template (A4 - Paid Template)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$goldPrimary = '#b45309'; // Warm amber/gold
$goldDark = '#78350f';
$slateBg = '#0f172a';
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>
<div class="invoice-executive-gold print-container p-5 bg-white" style="font-family: 'Georgia', serif; color: #1e293b; border: 8px double #d97706;">
    <!-- Gold Header Bar -->
    <div class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom" style="border-color: #d97706 !important;">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 75px; max-width: 150px; object-fit: contain; border: 1px solid #d97706; padding: 3px; border-radius: 4px;" class="flex-shrink-0">
            <?php endif; ?>
            <div>
                <div class="text-uppercase fw-bold text-muted small" style="letter-spacing: 2px;">OFFICIAL INVOICE</div>
                <h2 class="fw-bold mb-1" style="color: <?= $goldDark ?>; letter-spacing: 0.5px;"><?= e($company['company_name']) ?></h2>
                <div class="small text-muted" style="font-family: sans-serif;"><?= e($company['address_line1'] ?? '') ?>, <?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?></div>
                <div class="small text-muted" style="font-family: sans-serif;">Contact: <?= e($company['mobile'] ?? '') ?> | Email: <?= e($company['email'] ?? '') ?></div>
                <?php if ($isGst && !empty($company['gstin'])): ?>
                    <div class="small fw-bold mt-1" style="color: <?= $goldPrimary ?>; font-family: sans-serif;">GSTIN: <span class="text-uppercase"><?= e($company['gstin']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <div class="badge px-3 py-2 text-white fw-bold text-uppercase fs-6" style="background-color: <?= $goldDark ?>; letter-spacing: 1px;">
                <?= $isGst ? 'TAX INVOICE' : 'COMMERCIAL BILL' ?>
            </div>
            <div class="fs-5 fw-bold mt-2" style="color: <?= $goldPrimary ?>; font-family: sans-serif;"># <?= e($sale['invoice_number']) ?></div>
            <div class="small text-muted" style="font-family: sans-serif;">Dated: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
            <?php if (!empty($sale['due_date']) && (float)($sale['balance_amount'] ?? 0) > 0): ?>
                <div class="small text-muted" style="font-family: sans-serif;">Payment Due: <?= formatDate($sale['due_date']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Client Box -->
    <div class="row g-3 mb-4" style="font-family: sans-serif;">
        <div class="col-7">
            <div class="p-3 border rounded-2" style="background-color: #fffbeb; border-color: #fde68a !important;">
                <div class="text-uppercase fw-bold small" style="color: <?= $goldDark ?>; font-size: 10px; letter-spacing: 1px;">BILLED CLIENT</div>
                <h5 class="fw-bold mb-1 text-dark"><?= e($sale['customer_name']) ?></h5>
                <div class="small text-muted"><?= e($sale['place_of_supply']) ?> (Code: <?= e($sale['customer_state_code']) ?>)</div>
                <div class="small text-muted">Phone: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <?php if ($isGst && !empty($sale['customer_gstin'])): ?>
                    <div class="small fw-bold text-dark mt-1">GSTIN: <?= e($sale['customer_gstin']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-5">
            <div class="p-3 border rounded-2 h-100" style="background-color: #f8fafc;">
                <div class="text-uppercase fw-bold small text-muted" style="font-size: 10px; letter-spacing: 1px;">TRANSACTION RECORD</div>
                <div class="small d-flex justify-content-between mb-1 mt-1"><span class="text-muted">Payment Mode:</span> <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
                <div class="small d-flex justify-content-between mb-1"><span class="text-muted">Supply State:</span> <strong><?= e($sale['place_of_supply']) ?></strong></div>
                <?php if (!empty($sale['reference_no'])): ?>
                    <div class="small d-flex justify-content-between"><span class="text-muted">Ref / PO #:</span> <strong><?= e($sale['reference_no']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table mb-4" style="font-family: sans-serif; border: 1px solid #e2e8f0;">
        <thead>
            <tr style="background-color: <?= $goldDark ?>; color: #ffffff;">
                <th class="py-2 text-center" style="width: 35px;">#</th>
                <th class="py-2">Description</th>
                <?php if ($isGst): ?><th class="py-2 text-center" style="width: 80px;">HSN</th><?php endif; ?>
                <th class="py-2 text-center" style="width: 70px;">Qty</th>
                <th class="py-2 text-end" style="width: 90px;">Rate</th>
                <th class="py-2 text-end" style="width: 80px;">Disc</th>
                <th class="py-2 text-end" style="width: 100px;">Taxable</th>
                <?php if ($isGst): ?>
                    <th class="py-2 text-center" style="width: 65px;">GST</th>
                    <th class="py-2 text-end" style="width: 85px;">Tax</th>
                <?php endif; ?>
                <th class="py-2 text-end pe-3" style="width: 110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 1;
            foreach ($sale['items'] as $item): 
                $taxAmt = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
            ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
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

    <!-- Totals -->
    <div class="row g-3 mb-4" style="font-family: sans-serif;">
        <div class="col-7">
            <div class="p-3 border rounded-2 mb-2" style="background-color: #fffbeb;">
                <div class="small text-muted mb-1">Invoice Value in Words:</div>
                <div class="fw-bold" style="color: <?= $goldDark ?>;"><?= numberToWords($sale['grand_total']) ?></div>
            </div>
            <?php if (!empty($settings['show_bank_details']) && !empty($company['bank_account_no'])): ?>
                <div class="p-2 border rounded-2 small d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-uppercase mb-1" style="color: <?= $goldDark ?>;">Bank Remittance Details:</div>
                        <div>Bank: <strong><?= e($company['bank_name']) ?></strong> | A/c: <strong><?= e($company['bank_account_no']) ?></strong></div>
                        <div>IFSC: <strong><?= e($company['bank_ifsc']) ?></strong> | UPI: <strong><?= e($company['upi_id'] ?: 'N/A') ?></strong></div>
                    </div>
                    <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
                        <img src="<?= $qrUrl ?>" alt="QR" style="width: 75px; height: 75px;" class="rounded border ms-2">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <div class="p-3 rounded-2" style="border: 2px solid <?= $goldDark ?>; background-color: #fffdf5;">
                <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Taxable Base:</span> <span><?= formatCurrency($sale['taxable_amount']) ?></span></div>
                <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">GST Levy:</span> <span><?= formatCurrency($sale['total_tax_amount']) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center p-2 rounded text-white my-2" style="background-color: <?= $goldDark ?>;">
                    <span class="fw-bold">Total Payable</span>
                    <span class="fs-5 fw-bold"><?= formatCurrency($sale['grand_total']) ?></span>
                </div>
                <div class="d-flex justify-content-between small text-success fw-semibold"><span>Remitted / Paid:</span> <span><?= formatCurrency($sale['paid_amount']) ?></span></div>
                <?php if ((float)($sale['balance_amount'] ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between small text-danger fw-semibold"><span>Outstanding:</span> <span><?= formatCurrency($sale['balance_amount']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Signature -->
    <div class="d-flex justify-content-between align-items-end pt-3 border-top" style="font-family: sans-serif; font-size: 11px;">
        <div style="max-width: 60%; color: #64748b;">
            <?= nl2br(e($settings['terms_conditions'] ?? '1. Commercial invoice governed by standard business terms.')) ?>
        </div>
        <div class="text-end">
            <div class="mb-4 text-muted">Authorized By <?= e($company['company_name']) ?></div>
            <div class="border-top border-dark pt-1 fw-bold"><?= e($settings['signature_label'] ?? 'Authorized Signatory') ?></div>
        </div>
    </div>
</div>
