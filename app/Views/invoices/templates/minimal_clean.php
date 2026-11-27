<?php
/**
 * Minimalist Mono Invoice Template (A4)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
?>
<div class="invoice-minimal print-container p-5 bg-white" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #18181b;">
    <div class="d-flex justify-content-between align-items-baseline mb-5 pb-3 border-bottom border-dark border-2">
        <div class="d-flex align-items-start gap-3">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 65px; max-width: 140px; object-fit: contain;" class="mt-1 flex-shrink-0">
            <?php endif; ?>
            <div>
                <h2 class="fw-bolder tracking-tight mb-1" style="letter-spacing: -0.5px;"><?= e($company['company_name']) ?></h2>
                <div class="small text-muted"><?= e($company['address_line1'] ?? '') ?>, <?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?></div>
                <div class="small text-muted">Phone: <?= e($company['mobile'] ?? '') ?> | <?= e($company['email'] ?? '') ?></div>
            </div>
        </div>
        <div class="text-end">
            <div class="display-6 fw-bold" style="font-size: 1.8rem;"><?= $isGst ? 'INVOICE' : 'RECEIPT' ?></div>
            <div class="small font-monospace text-muted"># <?= e($sale['invoice_number']) ?></div>
            <div class="small text-muted">Date: <?= formatDate($sale['invoice_date']) ?></div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <div class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">BILLED TO</div>
            <div class="fs-6 fw-bold mt-1"><?= e($sale['customer_name']) ?></div>
            <div class="small text-muted"><?= e($sale['customer_mobile'] ?: 'No phone') ?></div>
            <?php if (!empty($sale['customer_gstin'])): ?>
                <div class="small font-monospace mt-1">GSTIN: <?= e($sale['customer_gstin']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <div class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">PAYMENT DETAILS</div>
            <div class="small mt-1">Status: <strong class="text-uppercase"><?= e($sale['payment_status']) ?></strong></div>
            <div class="small">Mode: <?= e($sale['payment_mode']) ?></div>
            <?php if (!empty($sale['due_date']) && (float)($sale['balance_amount'] ?? 0) > 0): ?>
                <div class="small text-muted">Due: <?= formatDate($sale['due_date']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <table class="table mb-4">
        <thead>
            <tr class="border-bottom border-dark">
                <th class="ps-0 py-2">Item</th>
                <th class="text-center py-2">Qty</th>
                <th class="text-end py-2">Rate</th>
                <?php if ($isGst): ?><th class="text-center py-2">GST</th><?php endif; ?>
                <th class="text-end pe-0 py-2">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale['items'] as $item): ?>
                <tr class="border-bottom border-light-subtle">
                    <td class="ps-0 py-3">
                        <div class="fw-semibold"><?= e($item['item_name']) ?></div>
                        <?php if (!empty($item['hsn_sac'])): ?><div class="small text-muted font-monospace" style="font-size: 11px;">HSN: <?= e($item['hsn_sac']) ?></div><?php endif; ?>
                    </td>
                    <td class="text-center py-3"><?= formatQty($item['quantity']) ?> <?= e($item['unit_name']) ?></td>
                    <td class="text-end py-3"><?= formatCurrency($item['rate'], false) ?></td>
                    <?php if ($isGst): ?><td class="text-center py-3"><?= (float)$item['gst_rate'] ?>%</td><?php endif; ?>
                    <td class="text-end pe-0 py-3 fw-bold"><?= formatCurrency($item['total_amount'], false) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row justify-content-end mb-5">
        <div class="col-5">
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Subtotal</span>
                <span><?= formatCurrency($sale['taxable_amount']) ?></span>
            </div>
            <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Tax</span>
                    <span><?= formatCurrency($sale['total_tax_amount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between py-2 border-top border-bottom border-dark mt-2 fw-bold">
                <span class="fs-6">Total</span>
                <span class="fs-5"><?= formatCurrency($sale['grand_total']) ?></span>
            </div>
        </div>
    </div>

    <div class="pt-4 border-top border-light-subtle text-muted small d-flex justify-content-between">
        <div>Thank you for choosing <?= e($company['company_name']) ?>.</div>
        <div><?= e($settings['signature_label'] ?? 'Authorized Signatory') ?></div>
    </div>
</div>
