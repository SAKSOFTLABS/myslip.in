<?php
$isGst = (bool)$company['gst_enabled'];
?>

<div class="invoice-a5-box print-container format-a5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start pb-2 mb-2 border-bottom">
        <div class="d-flex align-items-start gap-2">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 48px; max-width: 100px; object-fit: contain;" class="mt-1 flex-shrink-0">
            <?php endif; ?>
            <div>
                <h5 class="fw-bold mb-0 text-primary"><?= e($company['company_name']) ?></h5>
                <div class="text-muted" style="font-size: 10px;"><?= e($company['city']) ?>, <?= e($company['state']) ?> | Ph: <?= e($company['mobile']) ?></div>
                <?php if ($isGst && $company['gstin']): ?>
                    <div class="fw-bold text-dark" style="font-size: 10px;">GSTIN: <?= e($company['gstin']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <h6 class="fw-bold text-uppercase mb-0"><?= $isGst ? 'TAX INVOICE' : 'BILL OF SUPPLY' ?></h6>
            <div class="fw-bold" style="font-size: 11px;"># <?= e($sale['invoice_number']) ?></div>
            <div class="text-muted" style="font-size: 10px;">Date: <?= formatDate($sale['invoice_date']) ?></div>
        </div>
    </div>

    <!-- Customer -->
    <div class="d-flex justify-content-between mb-2 pb-1 border-bottom" style="font-size: 10px;">
        <div>To: <strong><?= e($sale['customer_name']) ?></strong> (<?= e($sale['place_of_supply']) ?>)</div>
        <div>Mode: <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
    </div>

    <!-- Items -->
    <table class="table invoice-table mb-2" style="font-size: 10px;">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Taxable</th>
                <?php if ($isGst): ?><th class="text-center">GST%</th><?php endif; ?>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td><?= e($item['item_name']) ?></td>
                    <td class="text-center"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                    <td class="text-end"><?= formatCurrency($item['rate'], false) ?></td>
                    <td class="text-end"><?= formatCurrency($item['taxable_amount'], false) ?></td>
                    <?php if ($isGst): ?><td class="text-center"><?= (float)$item['gst_rate'] ?>%</td><?php endif; ?>
                    <td class="text-end fw-bold"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <div style="font-size: 9px;" class="text-muted">
            Amount: <strong><?= numberToWords($sale['grand_total']) ?></strong>
        </div>
        <div class="text-end">
            <span class="text-muted small">Grand Total:</span>
            <span class="fs-6 fw-bold text-success ms-2"><?= formatCurrency($sale['grand_total']) ?></span>
        </div>
    </div>

    <!-- Sign -->
    <div class="d-flex justify-content-between align-items-end mt-4 pt-2" style="font-size: 10px;">
        <div class="text-muted">Thank you for your visit!</div>
        <div>For <?= e($company['company_name']) ?> (Sign)</div>
    </div>
</div>
