<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Item;
use App\Services\StockEngine;

class DashboardController extends Controller
{
    public function index(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // 1. KPI Stats
        // Today's Sales
        $todaySales = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(grand_total), 0) FROM `sales` WHERE `company_id` = :comp_id AND `invoice_date` = :today AND `status` = 'active'",
            ['comp_id' => $companyId, 'today' => $today]
        );

        // Today's Purchase
        $todayPurchase = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(grand_total), 0) FROM `purchases` WHERE `company_id` = :comp_id AND `purchase_date` = :today AND `status` = 'active'",
            ['comp_id' => $companyId, 'today' => $today]
        );

        // Today's Receipts
        $todayReceipts = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM `receipts` WHERE `company_id` = :comp_id AND `receipt_date` = :today AND `status` = 'active'",
            ['comp_id' => $companyId, 'today' => $today]
        );

        // Today's Payments
        $todayPayments = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM `payments` WHERE `company_id` = :comp_id AND `payment_date` = :today AND `status` = 'active'",
            ['comp_id' => $companyId, 'today' => $today]
        );

        // This Month Sales & Purchases
        $monthSales = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(grand_total), 0) FROM `sales` WHERE `company_id` = :comp_id AND `invoice_date` BETWEEN :start AND :end AND `status` = 'active'",
            ['comp_id' => $companyId, 'start' => $monthStart, 'end' => $monthEnd]
        );

        $monthPurchase = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(grand_total), 0) FROM `purchases` WHERE `company_id` = :comp_id AND `purchase_date` BETWEEN :start AND :end AND `status` = 'active'",
            ['comp_id' => $companyId, 'start' => $monthStart, 'end' => $monthEnd]
        );

        // Outstanding Receivables
        $receivables = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(balance_amount), 0) FROM `sales` WHERE `company_id` = :comp_id AND `status` = 'active'",
            ['comp_id' => $companyId]
        );

        // Outstanding Payables
        $payables = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(balance_amount), 0) FROM `purchases` WHERE `company_id` = :comp_id AND `status` = 'active'",
            ['comp_id' => $companyId]
        );

        // Current Stock Value (Dynamically computed from actual purchase bills and inventory movements)
        $stockValue = StockEngine::getTotalStockAssetValue($companyId);

        // Month Gross Profit = Taxable Sales - Cost of Goods Sold
        $cogs = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(si.quantity * si.cost_rate), 0) 
             FROM `sales_items` si
             JOIN `sales` s ON si.sale_id = s.id
             WHERE s.company_id = :comp_id AND s.invoice_date BETWEEN :start AND :end AND s.status = 'active'",
            ['comp_id' => $companyId, 'start' => $monthStart, 'end' => $monthEnd]
        );
        $monthTaxableSales = (float)$db->fetchValue(
            "SELECT COALESCE(SUM(taxable_amount), 0) FROM `sales` WHERE `company_id` = :comp_id AND `invoice_date` BETWEEN :start AND :end AND `status` = 'active'",
            ['comp_id' => $companyId, 'start' => $monthStart, 'end' => $monthEnd]
        );
        $monthProfit = max(0, $monthTaxableSales - $cogs);

        // 2. Recent Sales & Purchases
        $saleModel = new Sale();
        $recentSales = $saleModel->getSalesList(['limit' => 5]);

        $purchaseModel = new Purchase();
        $recentPurchases = $purchaseModel->getPurchaseList(['limit' => 5]);

        // 3. Low Stock Items (where current_stock <= reorder_level)
        $lowStockItems = $db->fetchAll(
            "SELECT i.id, i.name, i.code, i.reorder_level, u.short_name as unit_name,
                    COALESCE((SELECT SUM(qty_in - qty_out) FROM `stock_movements` sm WHERE sm.item_id = i.id AND sm.is_cancelled = 0), 0) as current_stock
             FROM `items` i
             LEFT JOIN `units` u ON i.unit_id = u.id
             WHERE i.company_id = :comp_id AND i.is_deleted = 0
             HAVING current_stock <= i.reorder_level
             ORDER BY current_stock ASC LIMIT 6",
            ['comp_id' => $companyId]
        );

        // 4. Monthly Trend Data (Last 6 Months) for Chart.js
        $monthlyLabels = [];
        $monthlySalesData = [];
        $monthlyPurchasesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = date('Y-m-01', strtotime("-{$i} month"));
            $mEnd = date('Y-m-t', strtotime("-{$i} month"));
            $mLabel = date('M Y', strtotime("-{$i} month"));

            $monthlyLabels[] = $mLabel;

            $sVal = (float)$db->fetchValue(
                "SELECT COALESCE(SUM(grand_total), 0) FROM `sales` WHERE `company_id` = :comp_id AND `invoice_date` BETWEEN :st AND :en AND `status` = 'active'",
                ['comp_id' => $companyId, 'st' => $mStart, 'en' => $mEnd]
            );
            $pVal = (float)$db->fetchValue(
                "SELECT COALESCE(SUM(grand_total), 0) FROM `purchases` WHERE `company_id` = :comp_id AND `purchase_date` BETWEEN :st AND :en AND `status` = 'active'",
                ['comp_id' => $companyId, 'st' => $mStart, 'en' => $mEnd]
            );

            $monthlySalesData[] = $sVal;
            $monthlyPurchasesData[] = $pVal;
        }

        $this->view('dashboard.index', [
            'pageTitle' => 'Dashboard Overview',
            'stats' => [
                'todaySales' => $todaySales,
                'todayPurchase' => $todayPurchase,
                'todayReceipts' => $todayReceipts,
                'todayPayments' => $todayPayments,
                'monthSales' => $monthSales,
                'monthPurchase' => $monthPurchase,
                'receivables' => $receivables,
                'payables' => $payables,
                'stockValue' => $stockValue,
                'monthProfit' => $monthProfit
            ],
            'recentSales' => $recentSales,
            'recentPurchases' => $recentPurchases,
            'lowStockItems' => $lowStockItems,
            'chart' => [
                'labels' => $monthlyLabels,
                'sales' => $monthlySalesData,
                'purchases' => $monthlyPurchasesData
            ]
        ]);
    }
}
