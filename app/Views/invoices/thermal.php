<?php
$isGst = (bool)$company['gst_enabled'];
$upiString = !empty($company['upi_id']) ? "upi://pay?pa=" . urlencode($company['upi_id']) . "&pn=" . urlencode($company['company_name']) . "&am=" . $sale['grand_total'] . "&cu=INR" : "";
$qrUrl = $upiString ? "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($upiString) : "";
?>

<div class="invoice-thermal-box print-container format-thermal">
    <!-- Header -->
    <div class="text-center mb-1">
        <?php if (!empty($company['logo_url'])): ?>
            <div class="mb-1 text-center">
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 48px; max-width: 140px; object-fit: contain; filter: grayscale(100%);">
            </div>
        <?php endif; ?>
        <h6 class="fw-bold mb-0 text-uppercase"><?= e($company['company_name']) ?></h6>
        <div style="font-size: 10px;"><?= e($company['address_line1']) ?>, <?= e($company['city']) ?></div>
        <div style="font-size: 10px;">Ph: <?= e($company['mobile']) ?></div>
        <?php if ($isGst && $company['gstin']): ?>
            <div style="font-size: 10px;" class="fw-bold">GSTIN: <?= e($company['gstin']) ?></div>
        <?php endif; ?>
        <div class="fw-bold text-uppercase mt-1" style="font-size: 11px;">
            <?= $isGst ? 'RETAIL TAX INVOICE' : 'CASH BILL / RECEIPT' ?>
        </div>
    </div>

    <div class="thermal-divider"></div>

    <!-- Bill Details -->
    <div style="font-size: 10px;">
        <div class="d-flex justify-content-between">
            <span>Bill #: <strong><?= e($sale['invoice_number']) ?></strong></span>
            <span>Date: <?= formatDate($sale['invoice_date']) ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Customer: <strong><?= e(substr($sale['customer_name'], 0, 18)) ?></strong></span>
            <span>Mode: <?= strtoupper($sale['payment_mode']) ?></span>
        </div>
    </div>

    <div class="thermal-divider"></div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr class="fw-bold" style="border-bottom: 1px dashed #000;">
                <th>ITEM</th>
                <th class="text-center">QTY</th>
                <th class="text-end">RATE</th>
                <th class="text-end">AMT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td colspan="4" class="fw-bold pb-0"><?= e($item['item_name']) ?></td>
                </tr>
                <tr style="border-bottom: 1px dotted #ccc;">
                    <td></td>
                    <td class="text-center"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                    <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                    <td class="text-end fw-bold">₹<?= number_format($item['total_amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="thermal-divider"></div>

    <!-- Calculations -->
    <div style="font-size: 10px;">
        <div class="d-flex justify-content-between">
            <span>Taxable Amount:</span>
            <span>₹<?= number_format($sale['taxable_amount'], 2) ?></span>
        </div>
        <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
            <?php if ($sale['is_interstate']): ?>
                <div class="d-flex justify-content-between">
                    <span>IGST:</span>
                    <span>₹<?= number_format($sale['igst_amount'], 2) ?></span>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between">
                    <span>CGST:</span>
                    <span>₹<?= number_format($sale['cgst_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>SGST:</span>
                    <span>₹<?= number_format($sale['sgst_amount'], 2) ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ((float)$sale['round_off'] != 0): ?>
            <div class="d-flex justify-content-between text-muted">
                <span>Round Off:</span>
                <span>₹<?= number_format($sale['round_off'], 2) ?></span>
            </div>
        <?php endif; ?>

        <div class="thermal-divider"></div>

        <div class="d-flex justify-content-between fw-bold" style="font-size: 13px;">
            <span>GRAND TOTAL:</span>
            <span>₹<?= number_format($sale['grand_total'], 2) ?></span>
        </div>

        <div class="d-flex justify-content-between mt-1">
            <span>Paid: ₹<?= number_format($sale['paid_amount'], 2) ?></span>
            <span>Due: ₹<?= number_format($sale['balance_amount'], 2) ?></span>
        </div>
    </div>

    <!-- UPI QR Code for instant desk counter scan -->
    <?php if ($qrUrl && $sale['balance_amount'] > 0): ?>
        <div class="thermal-divider"></div>
        <div class="text-center my-2">
            <img src="<?= $qrUrl ?>" alt="Scan to Pay" style="width: 90px; height: 90px;">
            <div style="font-size: 8px;" class="fw-bold mt-1">SCAN WITH ANY UPI APP TO PAY</div>
        </div>
    <?php endif; ?>

    <div class="thermal-divider"></div>

    <!-- Thermal Footer -->
    <div class="text-center" style="font-size: 9px;">
        <div><?= e($settings['thermal_footer_note'] ?? 'Thank you for your visit!') ?></div>
        <div>Software: myslip.in</div>
    </div>
</div>
