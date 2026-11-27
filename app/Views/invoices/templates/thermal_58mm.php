<?php
/**
 * Thermal POS 58mm (2-Inch) Receipt Template
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
?>
<div class="thermal-receipt-58mm print-container bg-white text-dark" style="font-family: 'Courier New', Courier, monospace; width: 56mm; margin: 0 auto; padding: 2px; font-size: 11px; line-height: 1.2;">
    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px;">
        <?php if (!empty($company['logo_url'])): ?>
            <div style="margin-bottom: 3px;">
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 40px; max-width: 120px; object-fit: contain; filter: grayscale(100%);">
            </div>
        <?php endif; ?>
        <div style="font-size: 13px; font-weight: bold;"><?= e($company['company_name']) ?></div>
        <div style="font-size: 10px;"><?= e($company['city'] ?? '') ?> | Ph: <?= e($company['mobile'] ?? '') ?></div>
        <?php if ($isGst && !empty($company['gstin'])): ?>
            <div style="font-size: 9px;">GST: <?= e($company['gstin']) ?></div>
        <?php endif; ?>
    </div>

    <div style="border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; font-size: 10px;">
        <div># <?= e($sale['invoice_number']) ?> | <?= formatDate($sale['invoice_date']) ?></div>
        <div>C: <?= e($sale['customer_name']) ?></div>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 4px;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">Item</th>
                <th style="text-align: center;">Q</th>
                <th style="text-align: right;">Amt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td style="padding: 2px 0;"><?= e($item['item_name']) ?></td>
                    <td style="text-align: center;"><?= (int)$item['quantity'] ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 3px 0; margin-bottom: 4px;">
        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: bold;">
            <span>TOTAL:</span>
            <span><?= formatCurrency($sale['grand_total']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 9px;">
            <span>Mode: <?= strtoupper($sale['payment_mode']) ?></span>
            <span>Paid: <?= formatCurrency($sale['paid_amount']) ?></span>
        </div>
    </div>

    <div style="text-align: center; font-size: 9px; padding-top: 2px;">
        <div>Thank You! Visit Again</div>
    </div>
</div>
