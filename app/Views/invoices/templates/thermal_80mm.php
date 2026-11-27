<?php
/**
 * Thermal POS 80mm (3-Inch) Receipt Template
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>
<div class="thermal-receipt-80mm print-container bg-white text-dark" style="font-family: 'Courier New', Courier, monospace; width: 78mm; margin: 0 auto; padding: 5px; font-size: 12px; line-height: 1.25;">
    <!-- Store Header -->
    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px;">
        <?php if (!empty($company['logo_url'])): ?>
            <div style="margin-bottom: 4px;">
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 50px; max-width: 150px; object-fit: contain; filter: grayscale(100%);">
            </div>
        <?php endif; ?>
        <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;"><?= e($company['company_name']) ?></div>
        <div><?= e($company['address_line1'] ?? '') ?>, <?= e($company['city'] ?? '') ?></div>
        <div>Ph: <?= e($company['mobile'] ?? '') ?></div>
        <?php if ($isGst && !empty($company['gstin'])): ?>
            <div>GSTIN: <?= e($company['gstin']) ?></div>
        <?php endif; ?>
        <div style="font-weight: bold; margin-top: 4px;"><?= $isGst ? '*** TAX INVOICE ***' : '*** CASH RECEIPT ***' ?></div>
    </div>

    <!-- Meta Details -->
    <div style="border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; font-size: 11px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Bill: <strong><?= e($sale['invoice_number']) ?></strong></span>
            <span>Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></span>
        </div>
        <div>Customer: <?= e($sale['customer_name']) ?></div>
        <?php if (!empty($sale['customer_mobile'])): ?>
            <div>Mobile: <?= e($sale['customer_mobile']) ?></div>
        <?php endif; ?>
    </div>

    <!-- Items -->
    <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 6px;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left; padding-bottom: 3px;">Item</th>
                <th style="text-align: center; padding-bottom: 3px;">Qty</th>
                <th style="text-align: right; padding-bottom: 3px;">Rate</th>
                <th style="text-align: right; padding-bottom: 3px;">Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td style="padding: 2px 0;">
                        <?= e($item['item_name']) ?>
                    </td>
                    <td style="text-align: center; padding: 2px 0;"><?= formatQty($item['quantity']) ?></td>
                    <td style="text-align: right; padding: 2px 0;"><?= formatCurrency($item['rate'], false) ?></td>
                    <td style="text-align: right; padding: 2px 0; font-weight: bold;"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; margin-bottom: 6px; font-size: 12px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span><?= formatCurrency($sale['taxable_amount']) ?></span>
        </div>
        <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
            <div style="display: flex; justify-content: space-between; font-size: 11px;">
                <span>Total Tax (GST):</span>
                <span><?= formatCurrency($sale['total_tax_amount']) ?></span>
            </div>
        <?php endif; ?>
        <?php if ((float)$sale['round_off'] != 0): ?>
            <div style="display: flex; justify-content: space-between; font-size: 11px;">
                <span>Round Off:</span>
                <span><?= formatCurrency($sale['round_off']) ?></span>
            </div>
        <?php endif; ?>
        <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: bold; margin-top: 4px; border-top: 1px solid #000; padding-top: 2px;">
            <span>NET TOTAL:</span>
            <span><?= formatCurrency($sale['grand_total']) ?></span>
        </div>
    </div>

    <!-- Payment info & QR -->
    <div style="font-size: 11px; margin-bottom: 6px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Payment Mode:</span>
            <span style="font-weight: bold; text-transform: uppercase;"><?= e($sale['payment_mode']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Amount Paid:</span>
            <span><?= formatCurrency($sale['paid_amount']) ?></span>
        </div>
        <?php if ((float)$sale['balance_amount'] > 0): ?>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>Balance Due:</span>
                <span><?= formatCurrency($sale['balance_amount']) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($settings['show_upi_qr']) && $qrUrl): ?>
        <div style="text-align: center; margin: 8px 0;">
            <img src="<?= $qrUrl ?>" alt="QR" style="width: 90px; height: 90px;">
            <div style="font-size: 10px; margin-top: 2px;">Scan & Pay via UPI</div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div style="text-align: center; border-top: 1px dashed #000; padding-top: 6px; font-size: 11px;">
        <div><?= e($settings['thermal_footer_note'] ?? 'Thank you! Visit again.') ?></div>
        <div style="font-size: 9px; margin-top: 3px;">Powered by MYSLIP.IN</div>
    </div>
</div>
