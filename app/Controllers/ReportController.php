<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Item;
use App\Services\ExportEngine;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->view('reports.index', [
            'pageTitle' => 'Financial & Operational Reports'
        ]);
    }

    // 1. Sales Report (Bill-wise & Item-wise)
    public function salesReport(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = parseDateToDb($this->request->input('start_date')) ?: date('Y-m-01');
        $endDate = parseDateToDb($this->request->input('end_date')) ?: date('Y-m-t');
        $customerId = $this->request->input('customer_id');

        $sql = "SELECT s.*, c.mobile as customer_phone 
                FROM `sales` s 
                JOIN `customers` c ON s.customer_id = c.id 
                WHERE s.company_id = :comp_id AND s.invoice_date BETWEEN :start AND :end";
        
        $params = ['comp_id' => $companyId, 'start' => $startDate, 'end' => $endDate];

        if ($customerId) {
            $sql .= " AND s.customer_id = :cid";
            $params['cid'] = $customerId;
        }

        $sql .= " ORDER BY s.invoice_date ASC, s.id ASC";
        $rows = $db->fetchAll($sql, $params);

        if ($this->request->input('export') === 'csv') {
            $csvHeaders = ['Invoice #', 'Date', 'Customer Name', 'Taxable Amount', 'Tax Amount', 'Round Off', 'Grand Total', 'Paid', 'Balance', 'Status'];
            $csvRows = [];
            foreach ($rows as $r) {
                $csvRows[] = [
                    $r['invoice_number'],
                    formatDate($r['invoice_date']),
                    $r['customer_name'],
                    $r['taxable_amount'],
                    $r['total_tax_amount'],
                    $r['round_off'],
                    $r['grand_total'],
                    $r['paid_amount'],
                    $r['balance_amount'],
                    $r['status']
                ];
            }
            ExportEngine::exportCsv('sales_report_' . date('Ymd'), $csvHeaders, $csvRows);
        }

        $customers = (new Customer())->all(['status' => 'active'], 'name ASC');

        $this->view('reports.sales', [
            'pageTitle' => 'Sales Register Report',
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customers' => $customers,
            'selectedCustomer' => $customerId
        ]);
    }

    // 2. Cash Book
    public function cashBook(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = parseDateToDb($this->request->input('start_date')) ?: date('Y-m-01');
        $endDate = parseDateToDb($this->request->input('end_date')) ?: date('Y-m-t');

        // Find cash account
        $cashAcc = $db->fetchOne("SELECT id, opening_balance, balance_type FROM `accounts` WHERE `company_id` = :comp_id AND `is_cash` = 1 LIMIT 1", ['comp_id' => $companyId]);
        
        $entries = [];
        $openingBalance = 0.00;

        if ($cashAcc) {
            $accId = $cashAcc['id'];
            // Pre-period movements
            $preDebits = (float)$db->fetchValue("SELECT COALESCE(SUM(debit), 0) FROM `ledger_entries` WHERE `company_id` = :comp_id AND `account_id` = :acc_id AND `voucher_date` < :st AND `is_cancelled` = 0", ['comp_id' => $companyId, 'acc_id' => $accId, 'st' => $startDate]);
            $preCredits = (float)$db->fetchValue("SELECT COALESCE(SUM(credit), 0) FROM `ledger_entries` WHERE `company_id` = :comp_id AND `account_id` = :acc_id AND `voucher_date` < :st AND `is_cancelled` = 0", ['comp_id' => $companyId, 'acc_id' => $accId, 'st' => $startDate]);
            
            $baseOpening = ($cashAcc['balance_type'] === 'DR') ? (float)$cashAcc['opening_balance'] : -(float)$cashAcc['opening_balance'];
            $openingBalance = $baseOpening + ($preDebits - $preCredits);

            // Period entries
            $entries = $db->fetchAll(
                "SELECT * FROM `ledger_entries` 
                 WHERE `company_id` = :comp_id AND `account_id` = :acc_id AND `voucher_date` BETWEEN :st AND :en AND `is_cancelled` = 0 
                 ORDER BY `voucher_date` ASC, `id` ASC",
                ['comp_id' => $companyId, 'acc_id' => $accId, 'st' => $startDate, 'en' => $endDate]
            );
        }

        $this->view('reports.cash_book', [
            'pageTitle' => 'Cash Book Report',
            'entries' => $entries,
            'openingBalance' => $openingBalance,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    // 3. Trial Balance
    public function trialBalance(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $sql = "SELECT a.id, a.account_name, ag.category, ag.name as group_name, a.opening_balance, a.balance_type,
                       COALESCE(SUM(le.debit), 0) as period_debit,
                       COALESCE(SUM(le.credit), 0) as period_credit
                FROM `accounts` a
                JOIN `account_groups` ag ON a.group_id = ag.id
                LEFT JOIN `ledger_entries` le ON a.id = le.account_id AND le.is_cancelled = 0
                WHERE a.company_id = :comp_id AND a.is_deleted = 0
                GROUP BY a.id, a.account_name, ag.category, ag.name, a.opening_balance, a.balance_type
                ORDER BY ag.category ASC, a.account_name ASC";
        
        $accounts = $db->fetchAll($sql, ['comp_id' => $companyId]);

        $this->view('reports.trial_balance', [
            'pageTitle' => 'Trial Balance',
            'accounts' => $accounts
        ]);
    }

    // 4. Profit & Loss Statement
    public function profitAndLoss(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = $this->request->input('start_date', date('Y-04-01'));
        $endDate = $this->request->input('end_date', date('Y-03-31', strtotime('+1 year')));

        // Incomes (Category = income)
        $incomes = $db->fetchAll(
            "SELECT a.account_name, COALESCE(SUM(le.credit - le.debit), 0) as balance 
             FROM `accounts` a 
             JOIN `account_groups` ag ON a.group_id = ag.id 
             LEFT JOIN `ledger_entries` le ON a.id = le.account_id AND le.voucher_date BETWEEN :st AND :en AND le.is_cancelled = 0 
             WHERE a.company_id = :comp_id AND ag.category = 'income' AND a.is_deleted = 0 
             GROUP BY a.id, a.account_name",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        // Expenses (Category = expenses)
        $expenses = $db->fetchAll(
            "SELECT a.account_name, COALESCE(SUM(le.debit - le.credit), 0) as balance 
             FROM `accounts` a 
             JOIN `account_groups` ag ON a.group_id = ag.id 
             LEFT JOIN `ledger_entries` le ON a.id = le.account_id AND le.voucher_date BETWEEN :st AND :en AND le.is_cancelled = 0 
             WHERE a.company_id = :comp_id AND ag.category = 'expenses' AND a.is_deleted = 0 
             GROUP BY a.id, a.account_name",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        $this->view('reports.profit_loss', [
            'pageTitle' => 'Profit & Loss Statement',
            'incomes' => $incomes,
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    // 5. Item Gross Profit Report
    public function itemProfit(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = parseDateToDb($this->request->input('start_date')) ?: date('Y-m-01');
        $endDate = parseDateToDb($this->request->input('end_date')) ?: date('Y-m-t');

        $sql = "SELECT i.name as item_name, i.code as item_code, u.short_name as unit_name,
                       SUM(si.quantity) as total_qty_sold,
                       SUM(si.taxable_amount) as total_sales_val,
                       SUM(si.quantity * si.cost_rate) as total_cost_val,
                       SUM(si.taxable_amount - (si.quantity * si.cost_rate)) as gross_profit
                FROM `sales_items` si
                JOIN `sales` s ON si.sale_id = s.id
                JOIN `items` i ON si.item_id = i.id
                LEFT JOIN `units` u ON i.unit_id = u.id
                WHERE s.company_id = :comp_id AND s.invoice_date BETWEEN :st AND :en AND s.status = 'active'
                GROUP BY i.id, i.name, i.code, u.short_name
                ORDER BY gross_profit DESC";

        $rows = $db->fetchAll($sql, ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]);

        $this->view('reports.item_profit', [
            'pageTitle' => 'Item-Wise Gross Profit Report',
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
