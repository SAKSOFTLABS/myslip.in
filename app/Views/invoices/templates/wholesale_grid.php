<?php
/**
 * Wholesale & B2B Heavy Grid Invoice Template (A4 - Paid Template)
 */
$isGst = !empty($company['gst_enabled']) && $company['gst_enabled'] == 1;
$headerBg = '#334155';
?>
<div class="invoice-wholesale-grid print-container p-4 bg-white" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000;">
    <!-- Main Boxed Container -->
    <div style="border: 2px solid #000;">
        <!-- Header -->
        <div class="p-3 text-center border-bottom border-2 border-dark" style="background-color: #f8fafc;">
            <div class="text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 13px;"><?= $isGst ? 'TAX INVOICE (RULE 46 OF CGST RULES)' : 'COMMERCIAL BILL OF SUPPLY' ?></div>
            <?php if (!empty($company['logo_url'])): ?>
                <div class="my-2 text-center">
                    <img src="<?= asset($company['logo_url']) ?>" alt="Logo" style="max-height: 65px; max-width: 180px; object-fit: contain;">
                </div>
            <?php endif; ?>
            <h2 class="fw-bold my-1 text-uppercase"><?= e($company['company_name']) ?></h2>
            <div><?= e($company['address_line1'] ?? '') ?>, <?= e($company['city'] ?? '') ?>, <?= e($company['state'] ?? '') ?> - <?= e($company['pin_code'] ?? '') ?></div>
            <div>Phone: <strong><?= e($company['mobile'] ?? '') ?></strong> | Email: <?= e($company['email'] ?? '') ?></div>
            <?php if ($isGst && !empty($company['gstin'])): ?>
                <div class="fw-bold mt-1">GSTIN: <?= e($company['gstin']) ?> | State: <?= e($company['state'] ?? '') ?> (Code: <?= e($company['state_code'] ?? '') ?>)</div>
            <?php endif; ?>
        </div>

        <!-- 4-Box Dispatch & Customer Grid -->
        <div class="row g-0 border-bottom border-dark">
            <div class="col-6 p-2 border-end border-dark">
                <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">Details of Receiver (Billed To):</div>
                <div class="fw-bold fs-6"><?= e($sale['customer_name']) ?></div>
                <div>Address: <?= e($sale['place_of_supply']) ?></div>
                <div>Phone: <?= e($sale['customer_mobile'] ?: 'N/A') ?></div>
                <?php if ($isGst && !empty($sale['customer_gstin'])): ?>
                    <div class="fw-bold">GSTIN/UIN: <?= e($sale['customer_gstin']) ?></div>
                <?php endif; ?>
                <div>State: <?= e($sale['customer_state'] ?? '') ?> (Code: <?= e($sale['customer_state_code'] ?? '') ?>)</div>
            </div>
            <div class="col-6 p-2">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="p-0 fw-bold">Invoice Number:</td><td class="p-0 text-end fw-bold"><?= e($sale['invoice_number']) ?></td></tr>
                    <tr><td class="p-0">Invoice Date:</td><td class="p-0 text-end"><?= formatDate($sale['invoice_date']) ?></td></tr>
                    <tr><td class="p-0">Payment Terms:</td><td class="p-0 text-end text-uppercase"><?= e($sale['payment_mode']) ?></td></tr>
                    <tr><td class="p-0">Due Date:</td><td class="p-0 text-end"><?= formatDate($sale['due_date'] ?? $sale['invoice_date']) ?></td></tr>
                    <tr><td class="p-0">Transport / LR No:</td><td class="p-0 text-end"><?= e($sale['reference_no'] ?: 'Direct Handover') ?></td></tr>
                    <tr><td class="p-0">Place of Supply:</td><td class="p-0 text-end"><?= e($sale['place_of_supply']) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Dense Table -->
        <table class="table table-sm table-bordered mb-0 border-dark" style="border-width: 0 0 1px 0;">
            <thead style="background-color: #f1f5f9;">
                <tr class="text-center align-middle">
                    <th style="width: 30px;">#</th>
                    <th>Description of Goods / Services</th>
                    <?php if ($isGst): ?><th style="width: 75px;">HSN/SAC</th><?php endif; ?>
                    <th style="width: 60px;">Quantity</th>
                    <th style="width: 75px;">Rate (₹)</th>
                    <th style="width: 65px;">Disc (₹)</th>
                    <th style="width: 85px;">Taxable (₹)</th>
                    <?php if ($isGst): ?>
                        <th style="width: 55px;">GST%</th>
                        <th style="width: 75px;">Tax (₹)</th>
                    <?php endif; ?>
                    <th style="width: 95px;">Total (₹)</th>
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
                        <td><strong><?= e($item['item_name']) ?></strong></td>
                        <?php if ($isGst): ?><td class="text-center font-monospace"><?= e($item['hsn_sac'] ?: '-') ?></td><?php endif; ?>
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

        <!-- Totals & Banking -->
        <div class="row g-0 border-bottom border-dark">
            <div class="col-7 p-2 border-end border-dark">
                <div class="mb-2"><strong>Total Amount in Words:</strong><br><?= numberToWords($sale['grand_total']) ?></div>
                <?php if (!empty($company['bank_account_no'])): ?>
                    <div class="p-2 border border-dark rounded bg-light" style="font-size: 10px;">
                        <div class="fw-bold text-uppercase">NEFT / RTGS Bank Details:</div>
                        <div>Bank: <strong><?= e($company['bank_name']) ?></strong> | A/c No: <strong><?= e($company['bank_account_no']) ?></strong></div>
                        <div>IFSC: <strong><?= e($company['bank_ifsc']) ?></strong> | Branch: <?= e($company['bank_branch'] ?: 'Main') ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-5 p-2">
                <div class="d-flex justify-content-between mb-1"><span>Taxable Amount:</span> <span class="fw-bold"><?= formatCurrency($sale['taxable_amount']) ?></span></div>
                <?php if ($isGst && (float)$sale['total_tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-1"><span>Total Tax:</span> <span><?= formatCurrency($sale['total_tax_amount']) ?></span></div>
                <?php endif; ?>
                <?php if ((float)$sale['round_off'] != 0): ?>
                    <div class="d-flex justify-content-between mb-1"><span>Round Off:</span> <span><?= formatCurrency($sale['round_off']) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between fs-6 fw-bold border-top border-dark pt-1 mt-1">
                    <span>Invoice Grand Total:</span>
                    <span><?= formatCurrency($sale['grand_total']) ?></span>
                </div>
                <div class="d-flex justify-content-between text-muted">
                    <span>Paid: <?= formatCurrency($sale['paid_amount']) ?></span>
                    <span>Bal: <?= formatCurrency($sale['balance_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Terms & Multi-Signatory Footer -->
        <div class="row g-0 p-2">
            <div class="col-8" style="font-size: 9px; line-height: 1.3;">
                <div class="fw-bold text-uppercase mb-1">Declaration & Terms:</div>
                <div><?= nl2br(e($settings['terms_conditions'] ?? '1. Goods sold are intended for commercial wholesale resale.')) ?></div>
            </div>
            <div class="col-4 text-end" style="font-size: 10px;">
                <div>For <strong><?= e($company['company_name']) ?></strong></div>
                <div style="height: 35px;"></div>
                <div class="fw-bold border-top border-dark d-inline-block pt-1"><?= e($settings['signature_label'] ?? 'Authorized Signatory') ?></div>
            </div>
        </div>
    </div>
</div>
