<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-graph-up me-2 text-primary"></i> Item Gross Profit Report</h4>
        <p class="text-muted small mb-0">Analysis of sales revenue vs purchase cost per product catalog item</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th>Code</th>
                        <th class="text-center">Total Qty Sold</th>
                        <th class="text-end">Sales Value (Taxable)</th>
                        <th class="text-end">Cost of Goods (COGS)</th>
                        <th class="text-end">Gross Profit (₹)</th>
                        <th class="text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totSales = 0; $totCost = 0; $totProfit = 0;
                    if (empty($rows)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted small">No items sold in selected period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): 
                            $salesVal = (float)$r['total_sales_val'];
                            $costVal = (float)$r['total_cost_val'];
                            $profit = (float)$r['gross_profit'];
                            $margin = ($salesVal > 0) ? round(($profit / $salesVal) * 100, 1) : 0;

                            $totSales += $salesVal;
                            $totCost += $costVal;
                            $totProfit += $profit;
                        ?>
                            <tr>
                                <td><strong><?= e($r['item_name']) ?></strong></td>
                                <td><?= e($r['item_code'] ?: '-') ?></td>
                                <td class="text-center fw-bold"><?= formatQty($r['total_qty_sold']) ?> <?= e($r['unit_name']) ?></td>
                                <td class="text-end"><?= formatCurrency($salesVal, false) ?></td>
                                <td class="text-end"><?= formatCurrency($costVal, false) ?></td>
                                <td class="text-end fw-bold text-<?= $profit >= 0 ? 'success' : 'danger' ?>">
                                    <?= formatCurrency($profit, false) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $margin >= 20 ? 'success' : ($margin >= 5 ? 'primary' : 'warning') ?>-subtle text-<?= $margin >= 20 ? 'success' : ($margin >= 5 ? 'primary' : 'warning') ?>">
                                        <?= $margin ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td colspan="3" class="text-end text-white">Summary Totals:</td>
                        <td class="text-end text-white"><?= formatCurrency($totSales) ?></td>
                        <td class="text-end text-white"><?= formatCurrency($totCost) ?></td>
                        <td class="text-end text-success fs-6"><?= formatCurrency($totProfit) ?></td>
                        <td class="text-center text-white"><?= $totSales > 0 ? round(($totProfit / $totSales) * 100, 1) : 0 ?>%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
