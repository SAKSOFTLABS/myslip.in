<?php
/**
 * Modern Blue Invoice Template (A4)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$primaryColor = '#0284c7'; // Sky / Royal Blue
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>
<div class="invoice-modern-blue print-container p-4 bg-white" style="font-family: 'Segoe UI', system-ui, sans-serif; color: #0f172a;">
    <!-- Modern Header Banner -->
    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-4 text-white" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($company['logo_url'])): ?>
                <div class="bg-white p-1 rounded-2 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="min-width: 60px;">
                    <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 55px; max-width: 140px; object-fit: contain;">
                </div>
            <?php endif; ?>
            <div>
                <h2 class="fw-bold mb-1 text-white"><?= e($company['company_name']) ?></h2>
                <div class="small opacity-75"><?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?> | Phone: <?= e($company['mobile'] ?? '') ?></div>
                <?php if ($isGst && !empty($company['gstin'])): ?>
                    <div class="small fw-semibold mt-1"><span class="badge bg-white text-dark">GSTIN: <?= e($company['gstin']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-primary fs-6 px-3 py-1 fw-bold text-uppercase"><?= $isGst ? 'TAX INVOICE' : 'BILL OF SUPPLY' ?></span>
            <div class="fs-5 fw-bold mt-2 text-white"><?= e($sale['invoice_number']) ?></div>
            <div class="small opacity-75">Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
        </div>
    </div>

    <!-- Customer & Meta Section -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="p-3 rounded-2 border border-light-subtle bg-light">
                <span class="badge bg-primary-subtle text-primary mb-2 text-uppercase fw-semibold" style="font-size: 11px;">Billed To</span>
                <h5 class="fw-bold mb-1 text-dark"><?= e($sale['customer_name']) ?></h5>
                <div class="small text-muted"><?= e($sale['place_of_supply']) ?> (State Code: <?= e($sale['customer_state_code']) ?>)</div>
                <div class="small text-muted">Phone: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <?php if ($isGst && !empty($sale['customer_gstin'])): ?>
                    <div class="small fw-bold text-dark mt-1">GSTIN: <?= e($sale['customer_gstin']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-5">
            <div class="p-3 rounded-2 border border-light-subtle bg-light h-100">
                <span class="badge bg-secondary-subtle text-secondary mb-2 text-uppercase fw-semibold" style="font-size: 11px;">Invoice Details</span>
                <div class="small d-flex justify-content-between mb-1"><span class="text-muted">Payment Mode:</span> <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
                <div class="small d-flex justify-content-between mb-1"><span class="text-muted">Place of Supply:</span> <strong><?= e($sale['place_of_supply']) ?></strong></div>
                <?php if (!empty($sale['due_date']) && (float)($sale['balance_amount'] ?? 0) > 0): ?>
                    <div class="small d-flex justify-content-between mb-1"><span class="text-muted">Due Date:</span> <strong><?= formatDate($sale['due_date']) ?></strong></div>
                <?php endif; ?>
                <?php if (!empty($sale['reference_no'])): ?>
                    <div class="small d-flex justify-content-between"><span class="text-muted">Ref/PO:</span> <strong><?= e($sale['reference_no']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table class="table mb-4" style="border-collapse: separate; border-spacing: 0;">
        <thead>
            <tr style="background-color: #f0f9ff; color: #0369a1;">
                <th class="py-2 px-3 rounded-start" style="width: 40px;">#</th>
                <th class="py-2">Item Description</th>
                <?php if ($isGst): ?><th class="py-2 text-center" style="width: 80px;">HSN</th><?php endif; ?>
                <th class="py-2 text-center" style="width: 70px;">Qty</th>
                <th class="py-2 text-end" style="width: 90px;">Rate</th>
                <th class="py-2 text-end" style="width: 80px;">Disc</th>
                <th class="py-2 text-end" style="width: 100px;">Taxable</th>
                <?php if ($isGst): ?>
                    <th class="py-2 text-center" style="width: 70px;">GST %</th>
                    <th class="py-2 text-end" style="width: 85px;">Tax</th>
                <?php endif; ?>
                <th class="py-2 text-end px-3 rounded-end" style="width: 110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 1;
            foreach ($sale['items'] as $item): 
                $taxAmt = (float)$item['cgst_amount'] + (float)$item['sgst_amount'] + (float)$item['igst_amount'];
            ?>
                <tr class="border-bottom border-light">
                    <td class="px-3 text-muted"><?= $count++ ?></td>
                    <td>
                        <strong class="text-dark"><?= e($item['item_name']) ?></strong>
                        <?php if (!empty($item['barcode'])): ?><div class="small text-muted font-monospace"><?= e($item['barcode']) ?></div><?php endif; ?>
                    </td>
                    <?php if ($isGst): ?><td class="text-center text-muted"><?= e($item['hsn_sac'] ?: '-') ?></td><?php endif; ?>
                    <td class="text-center"><?= formatQty($item['quantity']) ?> <span class="small text-muted"><?= e($item['unit_name']) ?></span></td>
                    <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                    <td class="text-end text-muted"><?= (float)$item['discount_amount'] > 0 ? formatCurrency($item['discount_amount'], false) : '-' ?></td>
                    <td class="text-end fw-semibold"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                    <?php if ($isGst): ?>
                        <td class="text-center"><?= (float)$item['gst_rate'] ?>%</td>
                        <td class="text-end"><?= formatCurrency($taxAmt, false) ?></td>
                    <?php endif; ?>
                    <td class="text-end px-3 fw-bold text-dark"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Summary Box -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="small text-muted mb-1">Amount in Words:</div>
                <div class="fw-bold text-dark"><?= numberToWords($sale['grand_total']) ?></div>
            </div>
            <?php if (!empty($settings['show_bank_details']) && !empty($company['bank_account_no'])): ?>
                <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center">
                    <div class="small">
                        <div class="fw-bold text-primary mb-1">Bank Payment Details:</div>
                        <div>Bank: <strong><?= e($company['bank_name']) ?></strong> | A/c: <strong><?= e($company['bank_account_no']) ?></strong></div>
                        <div>IFSC: <strong><?= e($company['bank_ifsc']) ?></strong> | UPI: <strong><?= e($company['upi_id'] ?: 'N/A') ?></strong></div>
                    </div>
                    <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
                        <img src="<?= $qrUrl ?>" alt="QR" style="width: 70px; height: 70px;" class="ms-2 rounded border">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-5">
            <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Taxable Amount:</span> <span class="fw-bold"><?= formatCurrency($sale['taxable_amount']) ?></span></div>
                <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">GST Total:</span> <span class="fw-bold"><?= formatCurrency($sale['total_tax_amount']) ?></span></div>
                <?php endif; ?>
                <?php if ((float)$sale['round_off'] != 0): ?>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Round Off:</span> <span><?= formatCurrency($sale['round_off']) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center p-2 rounded-2 text-white mb-2" style="background-color: #0284c7;">
                    <span class="fw-bold">Grand Total</span>
                    <span class="fs-5 fw-bold"><?= formatCurrency($sale['grand_total']) ?></span>
                </div>
                <div class="d-flex justify-content-between small text-success fw-semibold mb-1"><span>Paid:</span> <span><?= formatCurrency($sale['paid_amount']) ?></span></div>
                <?php if ((float)($sale['balance_amount'] ?? 0) > 0): ?>
                    <div class="d-flex justify-content-between small text-danger fw-semibold"><span>Balance Due:</span> <span><?= formatCurrency($sale['balance_amount']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modern Signature Block -->
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
