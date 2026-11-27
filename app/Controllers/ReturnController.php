<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\AuditLog;
use App\Services\GstEngine;
use App\Services\StockEngine;
use App\Services\AccountingEngine;
use App\Services\NumberingEngine;
use Exception;

class ReturnController extends Controller
{
    private SalesReturn $salesReturnModel;
    private PurchaseReturn $purchaseReturnModel;
    private Customer $customerModel;
    private Vendor $vendorModel;
    private Item $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->salesReturnModel = new SalesReturn();
        $this->purchaseReturnModel = new PurchaseReturn();
        $this->customerModel = new Customer();
        $this->vendorModel = new Vendor();
        $this->itemModel = new Item();
    }

    public function index(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $salesReturns = $db->fetchAll(
            "SELECT sr.*, c.mobile as customer_phone 
             FROM `sales_returns` sr 
             JOIN `customers` c ON sr.customer_id = c.id 
             WHERE sr.company_id = :comp_id ORDER BY sr.return_date DESC",
            ['comp_id' => $companyId]
        );

        $purchaseReturns = $db->fetchAll(
            "SELECT pr.*, v.mobile as vendor_phone 
             FROM `purchase_returns` pr 
             JOIN `vendors` v ON pr.vendor_id = v.id 
             WHERE pr.company_id = :comp_id ORDER BY pr.return_date DESC",
            ['comp_id' => $companyId]
        );

        $this->view('transactions.returns.index', [
            'pageTitle' => 'Returns & Credit/Debit Notes',
            'salesReturns' => $salesReturns,
            'purchaseReturns' => $purchaseReturns
        ]);
    }

    public function createSalesReturn(): void
    {
        $customers = $this->customerModel->getCustomersWithOutstanding();
        $items = $this->itemModel->getItemsWithDetails();

        $this->view('transactions.returns.sales_form', [
            'pageTitle' => 'New Sales Return (Credit Note)',
            'customers' => $customers,
            'items' => $items
        ]);
    }

    public function storeSalesReturn(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();
        $gstEnabled = Session::get('gst_enabled', true);
        $companyStateCode = Session::get('company_state_code', '27');

        $customerId = (int)$this->request->input('customer_id');
        $returnDate = parseDateToDb($this->request->input('return_date')) ?: date('Y-m-d');
        $reason = trim($this->request->input('reason', 'Goods Damaged / Customer Returned'));
        $rawItems = $this->request->input('items', []);

        if (empty($customerId) || empty($rawItems)) {
            $this->flash('danger', 'Please select customer and return item.');
            $this->redirect('/returns/sales/create');
        }

        $customer = $this->customerModel->find($customerId);
        $customerStateCode = $customer['state_code'] ?: '27';
        $isInterstate = GstEngine::isInterstate($companyStateCode, $customerStateCode);

        $db = Database::getInstance();

        try {
            $returnId = $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $gstEnabled, $customerId, $customer, $returnDate,
                $reason, $isInterstate, $rawItems
            ) {
                $returnNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'sales_return');

                $taxableTotal = 0.00;
                $cgstTotal = 0.00;
                $sgstTotal = 0.00;
                $igstTotal = 0.00;
                $processedItems = [];

                foreach ($rawItems as $raw) {
                    $itemId = (int)($raw['item_id'] ?? 0);
                    $qty = (float)($raw['quantity'] ?? 0);
                    $rate = (float)($raw['rate'] ?? 0);
                    $gstRate = (float)($raw['gst_rate'] ?? 0);

                    if ($itemId <= 0 || $qty <= 0) continue;

                    $itemRecord = $this->itemModel->find($itemId);
                    $itemName = $itemRecord ? $itemRecord['name'] : 'Item';
                    $hsnSac = $itemRecord ? $itemRecord['hsn_sac'] : null;

                    $computed = GstEngine::computeLineItem($qty, $rate, 0.00, 0.00, $gstRate, $isInterstate, $gstEnabled);

                    $taxableTotal += $computed['taxable_amount'];
                    $cgstTotal += $computed['cgst_amount'];
                    $sgstTotal += $computed['sgst_amount'];
                    $igstTotal += $computed['igst_amount'];

                    $processedItems[] = [
                        'item_id' => $itemId,
                        'item_name' => $itemName,
                        'hsn_sac' => $hsnSac,
                        'quantity' => $qty,
                        'unit_name' => $raw['unit_name'] ?? 'PCS',
                        'rate' => $rate,
                        'discount_amount' => 0.00,
                        'taxable_amount' => $computed['taxable_amount'],
                        'gst_rate' => $computed['gst_rate'],
                        'cgst_amount' => $computed['cgst_amount'],
                        'sgst_amount' => $computed['sgst_amount'],
                        'igst_amount' => $computed['igst_amount'],
                        'total_amount' => $computed['total_amount']
                    ];
                }

                $totalTax = round($cgstTotal + $sgstTotal + $igstTotal, 2);
                $grandTotal = round($taxableTotal + $totalTax, 2);

                // Insert Return Header
                $sqlSr = "INSERT INTO `sales_returns` 
                    (`company_id`, `financial_year_id`, `return_number`, `return_date`, `customer_id`, `customer_name`, `reason`, `taxable_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_tax_amount`, `grand_total`, `status`, `created_by`) 
                    VALUES 
                    (:comp_id, :fy_id, :rnum, :rdate, :cid, :cname, :reason, :taxable, :cgst, :sgst, :igst, :tax_amt, :grand, 'active', :uid)";

                $db->execute($sqlSr, [
                    'comp_id' => $companyId,
                    'fy_id' => $fyId,
                    'rnum' => $returnNumber,
                    'rdate' => $returnDate,
                    'cid' => $customerId,
                    'cname' => $customer['name'],
                    'reason' => $reason,
                    'taxable' => $taxableTotal,
                    'cgst' => $cgstTotal,
                    'sgst' => $sgstTotal,
                    'igst' => $igstTotal,
                    'tax_amt' => $totalTax,
                    'grand' => $grandTotal,
                    'uid' => $userId
                ]);

                $srId = $db->lastInsertId();

                // Insert Items & Increase Stock Movement
                $stmtItem = $db->getConnection()->prepare(
                    "INSERT INTO `sales_return_items` 
                     (`company_id`, `sales_return_id`, `item_id`, `item_name`, `hsn_sac`, `quantity`, `unit_name`, `rate`, `taxable_amount`, `gst_rate`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_amount`) 
                     VALUES 
                     (:company_id, :sr_id, :item_id, :item_name, :hsn_sac, :quantity, :unit_name, :rate, :taxable_amount, :gst_rate, :cgst_amount, :sgst_amount, :igst_amount, :total_amount)"
                );

                foreach ($processedItems as $pItem) {
                    $stmtItem->execute(array_merge(['company_id' => $companyId, 'sr_id' => $srId], $pItem));

                    // Return increases stock (qty_in)
                    StockEngine::recordMovement(
                        $companyId,
                        $fyId,
                        $pItem['item_id'],
                        'sales_return',
                        $srId,
                        null,
                        $returnDate,
                        $pItem['quantity'],
                        0.000,
                        $pItem['rate'],
                        $pItem['taxable_amount'],
                        "Sales Return {$returnNumber}"
                    );
                }

                // Reverse Double-Entry Accounting
                $customerAccountId = AccountingEngine::getCustomerAccountId($companyId, $customerId, $customer['name']);
                $salesReturnAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Sales Return Account', 'income', 13);

                $ledgerLines = [];
                // Debit Sales Return Account
                $ledgerLines[] = ['account_id' => $salesReturnAccountId, 'debit' => $taxableTotal, 'credit' => 0.00, 'narration' => "Sales Return {$returnNumber}"];

                // Debit Output GST (Reversing Output Tax)
                if ($gstEnabled && $totalTax > 0) {
                    if ($isInterstate) {
                        $igstId = AccountingEngine::getOrCreateAccount($companyId, 'Output IGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $igstId, 'debit' => $igstTotal, 'credit' => 0.00, 'narration' => "Output IGST Reversal {$returnNumber}"];
                    } else {
                        $cgstId = AccountingEngine::getOrCreateAccount($companyId, 'Output CGST', 'liabilities', 8);
                        $sgstId = AccountingEngine::getOrCreateAccount($companyId, 'Output SGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $cgstId, 'debit' => $cgstTotal, 'credit' => 0.00, 'narration' => "Output CGST Reversal {$returnNumber}"];
                        $ledgerLines[] = ['account_id' => $sgstId, 'debit' => $sgstTotal, 'credit' => 0.00, 'narration' => "Output SGST Reversal {$returnNumber}"];
                    }
                }

                // Credit Customer Account (Reducing customer receivable)
                $ledgerLines[] = ['account_id' => $customerAccountId, 'debit' => 0.00, 'credit' => $grandTotal, 'narration' => "Credit Note for Sales Return {$returnNumber}"];

                AccountingEngine::postVoucher($companyId, $fyId, 'sales_return', $srId, $returnNumber, $returnDate, $ledgerLines);

                AuditLog::log('Sales Return Created', 'Returns', $srId, ['return_number' => $returnNumber]);
                return $srId;
            });

            $this->flash('success', "Sales Return recorded successfully. Stock restored and customer ledger updated.");
            $this->redirect('/returns');

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/returns/sales/create');
        }
    }
}
