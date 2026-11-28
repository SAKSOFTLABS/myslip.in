<?php
namespace App\Services;

use App\Core\Database;

/**
 * Inventory Movement & Costing Engine
 */
class StockEngine
{
    /**
     * Record a stock movement ledger entry
     */
    public static function recordMovement(
        int $companyId,
        int $financialYearId,
        int $itemId,
        string $transactionType,
        int $transactionId,
        ?int $transactionItemId,
        string $transactionDate,
        float $qtyIn,
        float $qtyOut,
        float $rate,
        float $value,
        string $remarks = ''
    ): int {
        $db = Database::getInstance();
        $sql = "INSERT INTO `stock_movements` 
                (`company_id`, `financial_year_id`, `item_id`, `transaction_type`, `transaction_id`, `transaction_item_id`, `transaction_date`, `qty_in`, `qty_out`, `rate`, `total_value`, `remarks`, `is_cancelled`) 
                VALUES 
                (:company_id, :fy_id, :item_id, :tx_type, :tx_id, :tx_item_id, :tx_date, :qty_in, :qty_out, :rate, :value, :remarks, 0)";
        
        $db->execute($sql, [
            'company_id' => $companyId,
            'fy_id' => $financialYearId,
            'item_id' => $itemId,
            'tx_type' => $transactionType,
            'tx_id' => $transactionId,
            'tx_item_id' => $transactionItemId,
            'tx_date' => $transactionDate,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'rate' => $rate,
            'value' => $value,
            'remarks' => $remarks
        ]);

        return $db->lastInsertId();
    }

    /**
     * Get real-time stock quantity for an item
     */
    public static function getItemStock(int $companyId, int $itemId): float
    {
        $db = Database::getInstance();
        $sql = "SELECT COALESCE(SUM(qty_in - qty_out), 0) as current_stock 
                FROM `stock_movements` 
                WHERE `company_id` = :company_id 
                  AND `item_id` = :item_id 
                  AND `is_cancelled` = 0";
        
        return (float)$db->fetchValue($sql, [
            'company_id' => $companyId,
            'item_id' => $itemId
        ]);
    }

    /**
     * Cancel all movements associated with a transaction
     */
    public static function cancelTransactionMovements(int $companyId, string $transactionType, int $transactionId): void
    {
        $db = Database::getInstance();
        $sql = "UPDATE `stock_movements` 
                SET `is_cancelled` = 1 
                WHERE `company_id` = :company_id 
                  AND `transaction_type` = :tx_type 
                  AND `transaction_id` = :tx_id";
        
        $db->execute($sql, [
            'company_id' => $companyId,
            'tx_type' => $transactionType,
            'tx_id' => $transactionId
        ]);
    }

    /**
     * Calculate Weighted Average Cost (WAC) for an item
     */
    public static function getItemCostRate(int $companyId, int $itemId): float
    {
        $db = Database::getInstance();
        // Query inward stock movements (Opening + Purchases + Purchase Returns reversal)
        $sql = "SELECT SUM(qty_in) as total_qty, SUM(total_value) as total_val 
                FROM `stock_movements` 
                WHERE `company_id` = :company_id 
                  AND `item_id` = :item_id 
                  AND `qty_in` > 0 
                  AND `is_cancelled` = 0";
        
        $row = $db->fetchOne($sql, [
            'company_id' => $companyId,
            'item_id' => $itemId
        ]);

        if ($row && (float)$row['total_qty'] > 0) {
            return round((float)$row['total_val'] / (float)$row['total_qty'], 2);
        }

        // Fallback to purchase_rate on item record
        $item = $db->fetchOne("SELECT purchase_rate FROM `items` WHERE id = :id AND company_id = :comp_id", [
            'id' => $itemId,
            'comp_id' => $companyId
        ]);

        return $item ? (float)$item['purchase_rate'] : 0.00;
    }

    /**
     * Synchronize missing item purchase rates from actual purchase bills
     */
    public static function syncItemPurchaseRatesFromBills(int $companyId): void
    {
        $db = Database::getInstance();
        $sql = "UPDATE `items` i
                JOIN (
                    SELECT pi.item_id, pi.rate
                    FROM `purchase_items` pi
                    JOIN (
                        SELECT item_id, MAX(id) as max_id
                        FROM `purchase_items`
                        WHERE company_id = :comp_id1
                        GROUP BY item_id
                    ) latest ON pi.id = latest.max_id
                    WHERE pi.company_id = :comp_id2
                ) latest_p ON i.id = latest_p.item_id
                SET i.purchase_rate = latest_p.rate
                WHERE i.company_id = :comp_id3 
                  AND (i.purchase_rate IS NULL OR i.purchase_rate <= 0)";
        
        $db->execute($sql, [
            'comp_id1' => $companyId,
            'comp_id2' => $companyId,
            'comp_id3' => $companyId
        ]);

        // Also backfill opening stock movements that had 0 rate if item now has a purchase rate
        $sqlOpening = "UPDATE `stock_movements` sm
                       JOIN `items` i ON sm.item_id = i.id
                       SET sm.rate = i.purchase_rate, sm.total_value = ROUND(sm.qty_in * i.purchase_rate, 2)
                       WHERE sm.company_id = :comp_id
                         AND sm.transaction_type = 'opening'
                         AND (sm.rate IS NULL OR sm.rate <= 0)
                         AND i.purchase_rate > 0";
        $db->execute($sqlOpening, ['comp_id' => $companyId]);
    }

    /**
     * Calculate Total Stock Asset Value across all inventory items
     * Uses Weighted Average Cost from actual purchase bills and inward movements,
     * falling back to latest purchase bill rate and item master purchase rate.
     */
    public static function getTotalStockAssetValue(int $companyId): float
    {
        $db = Database::getInstance();

        // 1. Ensure any missing item master rates are synced from existing purchase bills
        self::syncItemPurchaseRatesFromBills($companyId);

        // 2. Compute accurate stock valuation using actual purchase bill costs
        $sql = "SELECT COALESCE(SUM(item_stock.current_stock * COALESCE(
                    NULLIF(item_wac.cost_rate, 0),
                    NULLIF(latest_pr.latest_rate, 0),
                    NULLIF(i.purchase_rate, 0),
                    0
                )), 0)
                FROM (
                    SELECT sm.item_id, SUM(sm.qty_in - sm.qty_out) as current_stock
                    FROM `stock_movements` sm
                    WHERE sm.company_id = :comp_id AND sm.is_cancelled = 0
                    GROUP BY sm.item_id
                    HAVING current_stock > 0
                ) item_stock
                JOIN `items` i ON item_stock.item_id = i.id AND i.is_deleted = 0
                LEFT JOIN (
                    SELECT sm_in.item_id, 
                           CASE WHEN SUM(sm_in.qty_in) > 0 THEN ROUND(SUM(sm_in.total_value) / SUM(sm_in.qty_in), 2) ELSE 0 END as cost_rate
                    FROM `stock_movements` sm_in
                    WHERE sm_in.company_id = :comp_id2 AND sm_in.qty_in > 0 AND sm_in.is_cancelled = 0
                    GROUP BY sm_in.item_id
                ) item_wac ON item_stock.item_id = item_wac.item_id
                LEFT JOIN (
                    SELECT pi.item_id, pi.rate as latest_rate
                    FROM `purchase_items` pi
                    JOIN (
                        SELECT item_id, MAX(id) as max_pi_id
                        FROM `purchase_items`
                        WHERE company_id = :comp_id3
                        GROUP BY item_id
                    ) latest_pi ON pi.id = latest_pi.max_pi_id
                ) latest_pr ON item_stock.item_id = latest_pr.item_id";

        return (float)$db->fetchValue($sql, [
            'comp_id' => $companyId,
            'comp_id2' => $companyId,
            'comp_id3' => $companyId
        ]);
    }
}
