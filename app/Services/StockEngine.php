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
}
