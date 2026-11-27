<?php
/**
 * Retail Quick Slip Template (A5)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
?>
<div class="invoice-retail-a5 print-container p-3 bg-white" style="font-family: 'Segoe UI', sans-serif; font-size: 12px; max-width: 148mm; margin: 0 auto; color: #1e293b;">
    <div class="text-center pb-2 mb-2 border-bottom">
        <?php if (!empty($company['logo_url'])): ?>
            <div class="mb-2 text-center">
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 50px; max-width: 140px; object-fit: contain;">
            </div>
        <?php endif; ?>
        <h4 class="fw-bold mb-0 text-dark"><?= e($company['company_name']) ?></h4>
        <div class="small text-muted"><?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?> | Ph: <?= e($company['mobile'] ?? '') ?></div>
        <?php if ($isGst && !empty($company['gstin'])): ?>
            <div class="small font-monospace fw-bold">GSTIN: <?= e($company['gstin']) ?></div>
        <?php endif; ?>
        <div class="badge bg-warning text-dark mt-1 px-3 py-1 fw-bold text-uppercase"><?= $isGst ? 'RETAIL TAX INVOICE' : 'CASH BILL' ?></div>
    </div>

    <div class="d-flex justify-content-between small mb-2 pb-1 border-bottom">
        <div>
            <div>Bill #: <strong><?= e($sale['invoice_number']) ?></strong></div>
            <div>Cust: <strong><?= e($sale['customer_name']) ?></strong> (<?= e($sale['customer_mobile'] ?: '-') ?>)</div>
        </div>
        <div class="text-end">
            <div>Date: <strong><?= formatDate($sale['invoice_date']) ?></strong></div>
            <div>Mode: <strong class="text-uppercase"><?= e($sale['payment_mode']) ?></strong></div>
        </div>
    </div>

    <table class="table table-sm table-borderless mb-2">
        <thead class="border-bottom border-dark">
            <tr style="font-size: 11px;">
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody class="border-bottom">
            <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td class="py-1">
                        <strong><?= e($item['item_name']) ?></strong>
                        <?php if ((float)$item['discount_amount'] > 0): ?>
                            <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 9px;">Saved ₹<?= formatCurrency($item['discount_amount'], false) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center py-1"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                    <td class="text-end py-1"><?= formatCurrency($item['rate'], false) ?></td>
                    <td class="text-end py-1 fw-semibold"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="border-bottom pb-2 mb-2">
        <div class="d-flex justify-content-between small"><span>Taxable Value:</span> <span><?= formatCurrency($sale['taxable_amount']) ?></span></div>
        <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
            <div class="d-flex justify-content-between small"><span>Total Tax:</span> <span><?= formatCurrency($sale['total_tax_amount']) ?></span></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between fs-6 fw-bold border-top border-dark pt-1 mt-1">
            <span>Net Payable:</span>
            <span><?= formatCurrency($sale['grand_total']) ?></span>
        </div>
        <div class="d-flex justify-content-between small text-muted">
            <span>Paid: <?= formatCurrency($sale['paid_amount']) ?></span>
            <span>Bal: <?= formatCurrency($sale['balance_amount']) ?></span>
        </div>
    </div>

    <div class="text-center text-muted" style="font-size: 10px;">
        <div>Thank you! Please visit again.</div>
        <div><?= e($settings['thermal_footer_note'] ?? 'No exchange without original cash memo.') ?></div>
    </div>
</div>
