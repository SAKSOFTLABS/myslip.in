<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Unit;
use App\Models\AuditLog;
use App\Services\GstEngine;
use App\Services\StockEngine;
use App\Services\AccountingEngine;
use App\Services\NumberingEngine;
use Exception;

class SalesController extends Controller
{
    private Sale $saleModel;
    private Customer $customerModel;
    private Item $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new Sale();
        $this->customerModel = new Customer();
        $this->itemModel = new Item();
    }

    public function index(): void
    {
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');
        $search = $this->request->input('search');

        $sales = $this->saleModel->getSalesList([
            'start_date' => parseDateToDb($startDate),
            'end_date' => parseDateToDb($endDate),
            'search' => $search
        ]);

        $this->view('transactions.sales.index', [
            'pageTitle' => 'Sales Invoices',
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search
        ]);
    }

    public function create(): void
    {
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();

        $customers = $this->customerModel->getCustomersWithOutstanding();
        $items = $this->itemModel->getItemsWithDetails();
        $nextInvoiceNumber = NumberingEngine::previewNextNumber($companyId, $fyId, 'sale');
        $unitModel = new Unit();
        $units = $unitModel->getAvailableUnits($companyId);
        $groupModel = new ItemGroup();
        $groups = $groupModel->all([], 'name ASC');

        $this->view('transactions.sales.form', [
            'pageTitle' => 'New Sales Invoice',
            'sale' => null,
            'customers' => $customers,
            'items' => $items,
            'units' => $units,
            'groups' => $groups,
            'nextInvoiceNumber' => $nextInvoiceNumber,
            'states' => INDIAN_STATES
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();
        $gstEnabled = Session::get('gst_enabled', true);
        $companyStateCode = Session::get('company_state_code', '27');

        $customerId = (int)$this->request->input('customer_id');
        $invoiceDate = parseDateToDb($this->request->input('invoice_date')) ?: date('Y-m-d');
        $dueDate = parseDateToDb($this->request->input('due_date'));
        $paymentMode = $this->request->input('payment_mode', 'credit');
        $salesType = $this->request->input('sales_type', 'tax_invoice');
        $referenceNo = trim($this->request->input('reference_no', ''));
        $notes = trim($this->request->input('notes', ''));

        $rawItems = $this->request->input('items', []);
        if (empty($customerId) || empty($rawItems)) {
            $this->flash('danger', 'Please select a customer and at least one line item.');
            $this->redirect('/sales/create');
        }

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            $this->flash('danger', 'Selected customer does not exist.');
            $this->redirect('/sales/create');
        }

        $customerStateCode = $customer['state_code'] ?: '27';
        $placeOfSupply = $customer['state'];
        $isInterstate = GstEngine::isInterstate($companyStateCode, $customerStateCode);

        $db = Database::getInstance();

        try {
            $saleId = $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $gstEnabled, $customerId, $customer, $customerStateCode, $invoiceDate,
                $dueDate, $paymentMode, $salesType, $referenceNo, $notes, $placeOfSupply, $isInterstate, $rawItems
            ) {
                // 1. Generate guaranteed invoice number
                $invoiceNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'sale');

                // 2. Compute item lines
                $grossTotal = 0.00;
                $itemDiscountTotal = 0.00;
                $taxableTotal = 0.00;
                $cgstTotal = 0.00;
                $sgstTotal = 0.00;
                $igstTotal = 0.00;
                $processedItems = [];

                foreach ($rawItems as $raw) {
                    $itemId = (int)($raw['item_id'] ?? 0);
                    $qty = (float)($raw['quantity'] ?? 0);
                    $rate = (float)($raw['rate'] ?? 0);
                    $discPct = (float)($raw['discount_percent'] ?? 0);
                    $gstRate = (float)($raw['gst_rate'] ?? 0);

                    if ($itemId <= 0 || $qty <= 0) {
                        continue;
                    }

                    $itemRecord = $this->itemModel->find($itemId);
                    $itemName = $itemRecord ? $itemRecord['name'] : ($raw['item_name'] ?? 'Item');
                    $barcode = $itemRecord ? $itemRecord['barcode'] : null;
                    $hsnSac = $itemRecord ? $itemRecord['hsn_sac'] : null;
                    $unitName = $raw['unit_name'] ?? 'PCS';
                    $costRate = StockEngine::getItemCostRate($companyId, $itemId);

                    $computed = GstEngine::computeLineItem($qty, $rate, $discPct, 0.00, $gstRate, $isInterstate, $gstEnabled);

                    $grossTotal += $computed['gross_amount'];
                    $itemDiscountTotal += $computed['discount_amount'];
                    $taxableTotal += $computed['taxable_amount'];
                    $cgstTotal += $computed['cgst_amount'];
                    $sgstTotal += $computed['sgst_amount'];
                    $igstTotal += $computed['igst_amount'];

                    $processedItems[] = [
                        'item_id' => $itemId,
                        'item_name' => $itemName,
                        'barcode' => $barcode,
                        'hsn_sac' => $hsnSac,
                        'quantity' => $qty,
                        'unit_name' => $unitName,
                        'rate' => $rate,
                        'discount_percent' => $discPct,
                        'discount_amount' => $computed['discount_amount'],
                        'taxable_amount' => $computed['taxable_amount'],
                        'gst_rate' => $computed['gst_rate'],
                        'cgst_rate' => $computed['cgst_rate'],
                        'cgst_amount' => $computed['cgst_amount'],
                        'sgst_rate' => $computed['sgst_rate'],
                        'sgst_amount' => $computed['sgst_amount'],
                        'igst_rate' => $computed['igst_rate'],
                        'igst_amount' => $computed['igst_amount'],
                        'total_amount' => $computed['total_amount'],
                        'cost_rate' => $costRate
                    ];
                }

                if (empty($processedItems)) {
                    throw new Exception("Invoice must contain at least one valid line item.");
                }

                $totalTax = round($cgstTotal + $sgstTotal + $igstTotal, 2);
                $preRound = round($taxableTotal + $totalTax, 2);
                $grandTotal = round($preRound);
                $roundOff = round($grandTotal - $preRound, 2);

                $paidAmount = (float)$this->request->input('paid_amount', 0.00);
                if ($paymentMode === 'cash' || $paymentMode === 'upi' || $paymentMode === 'bank') {
                    $paidAmount = $grandTotal;
                }
                $balanceAmount = max(0, round($grandTotal - $paidAmount, 2));
                $paymentStatus = ($balanceAmount == 0) ? 'paid' : (($paidAmount > 0) ? 'partial' : 'unpaid');

                // 3. Insert Sales Header
                $sqlSale = "INSERT INTO `sales` 
                    (`company_id`, `financial_year_id`, `invoice_number`, `invoice_date`, `due_date`, `customer_id`, `customer_name`, `customer_mobile`, `customer_gstin`, `customer_state`, `customer_state_code`, `place_of_supply`, `is_interstate`, `payment_mode`, `sales_type`, `reference_no`, `gross_amount`, `item_discount_amount`, `taxable_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_tax_amount`, `round_off`, `grand_total`, `paid_amount`, `balance_amount`, `payment_status`, `notes`, `status`, `created_by`) 
                    VALUES 
                    (:comp_id, :fy_id, :inv_num, :inv_date, :due_date, :cust_id, :cust_name, :cust_mobile, :cust_gstin, :cust_state, :cust_scode, :pos, :is_inter, :pmode, :stype, :ref_no, :gross, :item_disc, :taxable, :cgst, :sgst, :igst, :tax_amt, :round_off, :grand, :paid, :balance, :pstatus, :notes, 'active', :created_by)";

                $db->execute($sqlSale, [
                    'comp_id' => $companyId,
                    'fy_id' => $fyId,
                    'inv_num' => $invoiceNumber,
                    'inv_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'cust_id' => $customerId,
                    'cust_name' => $customer['name'],
                    'cust_mobile' => $customer['mobile'],
                    'cust_gstin' => $customer['gstin'],
                    'cust_state' => $customer['state'],
                    'cust_scode' => $customerStateCode,
                    'pos' => $placeOfSupply,
                    'is_inter' => $isInterstate ? 1 : 0,
                    'pmode' => $paymentMode,
                    'stype' => $salesType,
                    'ref_no' => $referenceNo,
                    'gross' => $grossTotal,
                    'item_disc' => $itemDiscountTotal,
                    'taxable' => $taxableTotal,
                    'cgst' => $cgstTotal,
                    'sgst' => $sgstTotal,
                    'igst' => $igstTotal,
                    'tax_amt' => $totalTax,
                    'round_off' => $roundOff,
                    'grand' => $grandTotal,
                    'paid' => $paidAmount,
                    'balance' => $balanceAmount,
                    'pstatus' => $paymentStatus,
                    'notes' => $notes,
                    'created_by' => $userId
                ]);

                $saleId = $db->lastInsertId();

                // 4. Insert Sales Items & Stock Movement Ledger
                $stmtItem = $db->getConnection()->prepare(
                    "INSERT INTO `sales_items` 
                     (`company_id`, `sale_id`, `item_id`, `item_name`, `barcode`, `hsn_sac`, `quantity`, `unit_name`, `rate`, `discount_percent`, `discount_amount`, `taxable_amount`, `gst_rate`, `cgst_rate`, `cgst_amount`, `sgst_rate`, `sgst_amount`, `igst_rate`, `igst_amount`, `total_amount`, `cost_rate`) 
                     VALUES 
                     (:company_id, :sale_id, :item_id, :item_name, :barcode, :hsn_sac, :quantity, :unit_name, :rate, :discount_percent, :discount_amount, :taxable_amount, :gst_rate, :cgst_rate, :cgst_amount, :sgst_rate, :sgst_amount, :igst_rate, :igst_amount, :total_amount, :cost_rate)"
                );

                foreach ($processedItems as $pItem) {
                    $stmtItem->execute(array_merge(['company_id' => $companyId, 'sale_id' => $saleId], $pItem));
                    $saleItemId = $db->lastInsertId();

                    // Record Outward Stock Movement
                    StockEngine::recordMovement(
                        $companyId,
                        $fyId,
                        $pItem['item_id'],
                        'sale',
                        $saleId,
                        $saleItemId,
                        $invoiceDate,
                        0.000,
                        $pItem['quantity'],
                        $pItem['rate'],
                        $pItem['taxable_amount'],
                        "Sale Invoice {$invoiceNumber}"
                    );
                }

                // 5. Automated Double-Entry Ledger Posting
                $customerAccountId = AccountingEngine::getCustomerAccountId($companyId, $customerId, $customer['name']);
                $salesAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Sales Account', 'income', 13);
                $roundOffAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Round Off Account', 'expenses', 18);

                $ledgerLines = [];

                if ($paymentMode === 'cash') {
                    $cashAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Cash Account', 'assets', 1, true, false);
                    $ledgerLines[] = ['account_id' => $cashAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Cash Sale {$invoiceNumber}"];
                } elseif ($paymentMode === 'bank' || $paymentMode === 'upi') {
                    $bankAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Primary Bank Account', 'assets', 2, false, true);
                    $ledgerLines[] = ['account_id' => $bankAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Bank/UPI Sale {$invoiceNumber}"];
                } else {
                    $ledgerLines[] = ['account_id' => $customerAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Credit Sale {$invoiceNumber}"];
                }

                // Credit Sales Account
                $ledgerLines[] = ['account_id' => $salesAccountId, 'debit' => 0.00, 'credit' => $taxableTotal, 'narration' => "Sales Value for {$invoiceNumber}"];

                // Credit GST Accounts if applicable
                if ($gstEnabled && $totalTax > 0) {
                    if ($isInterstate) {
                        $igstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output IGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $igstAccountId, 'debit' => 0.00, 'credit' => $igstTotal, 'narration' => "Output IGST on {$invoiceNumber}"];
                    } else {
                        $cgstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output CGST', 'liabilities', 8);
                        $sgstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output SGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $cgstAccountId, 'debit' => 0.00, 'credit' => $cgstTotal, 'narration' => "Output CGST on {$invoiceNumber}"];
                        $ledgerLines[] = ['account_id' => $sgstAccountId, 'debit' => 0.00, 'credit' => $sgstTotal, 'narration' => "Output SGST on {$invoiceNumber}"];
                    }
                }

                // Round Off posting
                if ($roundOff > 0) {
                    // Credit round off income / liability
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => 0.00, 'credit' => $roundOff, 'narration' => "Round Off on {$invoiceNumber}"];
                } elseif ($roundOff < 0) {
                    // Debit round off expense
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => abs($roundOff), 'credit' => 0.00, 'narration' => "Round Off on {$invoiceNumber}"];
                }

                AccountingEngine::postVoucher($companyId, $fyId, 'sale', $saleId, $invoiceNumber, $invoiceDate, $ledgerLines);

                AuditLog::log('Invoice Created', 'Sales', $saleId, ['invoice_number' => $invoiceNumber, 'grand_total' => $grandTotal]);
                return $saleId;
            });

            $this->flash('success', "Invoice created successfully.");
            
            // Check if Save & Print was clicked
            if ($this->request->input('action') === 'save_and_print') {
                $this->redirect('/invoices/print/' . $saleId);
            }

            $this->redirect('/sales/view/' . $saleId);

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/sales/create');
        }
    }

    public function viewInvoice(int $id): void
    {
        $sale = $this->saleModel->getSaleWithItems($id);
        if (!$sale) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
        }

        $this->view('transactions.sales.view', [
            'pageTitle' => 'Invoice ' . $sale['invoice_number'],
            'sale' => $sale
        ]);
    }

    public function cancel(int $id): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $userId = Session::getUserId();
        $reason = trim($this->request->input('cancelled_reason', 'Cancelled by User'));

        $sale = $this->saleModel->find($id);
        if (!$sale) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
        }

        if ($sale['status'] === 'cancelled') {
            $this->flash('warning', 'Invoice is already cancelled.');
            $this->redirect('/sales/view/' . $id);
        }

        $db = Database::getInstance();
        $db->transaction(function(Database $db) use ($companyId, $userId, $id, $sale, $reason) {
            // 1. Mark Sale as cancelled
            $db->execute(
                "UPDATE `sales` 
                 SET `status` = 'cancelled', `cancelled_reason` = :reason, `cancelled_by` = :uid, `cancelled_at` = NOW() 
                 WHERE `id` = :id AND `company_id` = :comp_id",
                [
                    'reason' => $reason,
                    'uid' => $userId,
                    'id' => $id,
                    'comp_id' => $companyId
                ]
            );

            // 2. Reverse Stock Movement
            StockEngine::cancelTransactionMovements($companyId, 'sale', $id);

            // 3. Reverse Accounting Ledger
            AccountingEngine::cancelVoucher($companyId, 'sale', $id);

            AuditLog::log('Invoice Cancelled', 'Sales', $id, ['invoice_number' => $sale['invoice_number'], 'reason' => $reason]);
        });

        $this->flash('success', "Invoice [{$sale['invoice_number']}] cancelled successfully. Stock and accounting ledgers have been restored.");
        $this->redirect('/sales/view/' . $id);
    }

    public function edit(int $id): void
    {
        $companyId = Session::getCompanyId();
        $sale = $this->saleModel->getSaleWithItems($id);
        if (!$sale || (int)$sale['company_id'] !== $companyId) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
            return;
        }

        $customers = $this->customerModel->getCustomersWithOutstanding();
        $items = $this->itemModel->getItemsWithDetails();
        $unitModel = new Unit();
        $units = $unitModel->getAvailableUnits($companyId);
        $groupModel = new ItemGroup();
        $groups = $groupModel->all([], 'name ASC');

        $this->view('transactions.sales.form', [
            'pageTitle' => 'Edit Sales Invoice: ' . $sale['invoice_number'],
            'sale' => $sale,
            'customers' => $customers,
            'items' => $items,
            'units' => $units,
            'groups' => $groups,
            'nextInvoiceNumber' => $sale['invoice_number'],
            'states' => INDIAN_STATES
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();
        $gstEnabled = Session::get('gst_enabled', true);
        $companyStateCode = Session::get('company_state_code', '27');

        $sale = $this->saleModel->find($id);
        if (!$sale || (int)$sale['company_id'] !== $companyId) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
            return;
        }

        $customerId = (int)$this->request->input('customer_id');
        $invoiceDate = parseDateToDb($this->request->input('invoice_date')) ?: date('Y-m-d');
        $dueDate = parseDateToDb($this->request->input('due_date'));
        $paymentMode = $this->request->input('payment_mode', 'credit');
        $salesType = $this->request->input('sales_type', 'tax_invoice');
        $referenceNo = trim($this->request->input('reference_no', ''));
        $notes = trim($this->request->input('notes', ''));

        $rawItems = $this->request->input('items', []);
        if (empty($customerId) || empty($rawItems)) {
            $this->flash('danger', 'Please select a customer and at least one line item.');
            $this->redirect('/sales/edit/' . $id);
            return;
        }

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            $this->flash('danger', 'Selected customer does not exist.');
            $this->redirect('/sales/edit/' . $id);
            return;
        }

        $customerStateCode = $customer['state_code'] ?: '27';
        $placeOfSupply = $customer['state'];
        $isInterstate = GstEngine::isInterstate($companyStateCode, $customerStateCode);

        $db = Database::getInstance();

        try {
            $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $gstEnabled, $id, $sale, $customerId, $customer, $customerStateCode,
                $invoiceDate, $dueDate, $paymentMode, $salesType, $referenceNo, $notes, $placeOfSupply, $isInterstate, $rawItems
            ) {
                $invoiceNumber = $sale['invoice_number'];

                // 1. Compute item lines
                $grossTotal = 0.00;
                $itemDiscountTotal = 0.00;
                $taxableTotal = 0.00;
                $cgstTotal = 0.00;
                $sgstTotal = 0.00;
                $igstTotal = 0.00;
                $processedItems = [];

                foreach ($rawItems as $raw) {
                    $itemId = (int)($raw['item_id'] ?? 0);
                    $qty = (float)($raw['quantity'] ?? 0);
                    $rate = (float)($raw['rate'] ?? 0);
                    $discPct = (float)($raw['discount_percent'] ?? 0);
                    $gstRate = (float)($raw['gst_rate'] ?? 0);

                    if ($itemId <= 0 || $qty <= 0) {
                        continue;
                    }

                    $itemRecord = $this->itemModel->find($itemId);
                    $itemName = $itemRecord ? $itemRecord['name'] : ($raw['item_name'] ?? 'Item');
                    $barcode = $itemRecord ? $itemRecord['barcode'] : null;
                    $hsnSac = $itemRecord ? $itemRecord['hsn_sac'] : null;
                    $unitName = $raw['unit_name'] ?? 'PCS';
                    $costRate = StockEngine::getItemCostRate($companyId, $itemId);

                    $computed = GstEngine::computeLineItem($qty, $rate, $discPct, 0.00, $gstRate, $isInterstate, $gstEnabled);

                    $grossTotal += $computed['gross_amount'];
                    $itemDiscountTotal += $computed['discount_amount'];
                    $taxableTotal += $computed['taxable_amount'];
                    $cgstTotal += $computed['cgst_amount'];
                    $sgstTotal += $computed['sgst_amount'];
                    $igstTotal += $computed['igst_amount'];

                    $processedItems[] = [
                        'item_id' => $itemId,
                        'item_name' => $itemName,
                        'barcode' => $barcode,
                        'hsn_sac' => $hsnSac,
                        'quantity' => $qty,
                        'unit_name' => $unitName,
                        'rate' => $rate,
                        'discount_percent' => $discPct,
                        'discount_amount' => $computed['discount_amount'],
                        'taxable_amount' => $computed['taxable_amount'],
                        'gst_rate' => $computed['gst_rate'],
                        'cgst_rate' => $computed['cgst_rate'],
                        'cgst_amount' => $computed['cgst_amount'],
                        'sgst_rate' => $computed['sgst_rate'],
                        'sgst_amount' => $computed['sgst_amount'],
                        'igst_rate' => $computed['igst_rate'],
                        'igst_amount' => $computed['igst_amount'],
                        'total_amount' => $computed['total_amount'],
                        'cost_rate' => $costRate
                    ];
                }

                if (empty($processedItems)) {
                    throw new Exception("Invoice must contain at least one valid line item.");
                }

                $totalTax = round($cgstTotal + $sgstTotal + $igstTotal, 2);
                $preRound = round($taxableTotal + $totalTax, 2);
                $grandTotal = round($preRound);
                $roundOff = round($grandTotal - $preRound, 2);

                $paidAmount = (float)$this->request->input('paid_amount', $sale['paid_amount'] ?? 0.00);
                if ($paymentMode === 'cash' || $paymentMode === 'upi' || $paymentMode === 'bank') {
                    $paidAmount = $grandTotal;
                }
                $balanceAmount = max(0, round($grandTotal - $paidAmount, 2));
                $paymentStatus = ($balanceAmount == 0) ? 'paid' : (($paidAmount > 0) ? 'partial' : 'unpaid');

                // 2. Clear old stock movements, ledger entries, and sales items
                $db->execute(
                    "DELETE FROM `stock_movements` 
                     WHERE `company_id` = :comp_id AND `transaction_type` = 'sale' AND `transaction_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );
                $db->execute(
                    "DELETE FROM `ledger_entries` 
                     WHERE `company_id` = :comp_id AND `voucher_type` = 'sale' AND `voucher_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );
                $db->execute(
                    "DELETE FROM `sales_items` 
                     WHERE `company_id` = :comp_id AND `sale_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                // 3. Update Sales Header
                $sqlUpdate = "UPDATE `sales` SET 
                    `invoice_date` = :inv_date,
                    `due_date` = :due_date,
                    `customer_id` = :cust_id,
                    `customer_name` = :cust_name,
                    `customer_mobile` = :cust_mobile,
                    `customer_gstin` = :cust_gstin,
                    `customer_state` = :cust_state,
                    `customer_state_code` = :cust_scode,
                    `place_of_supply` = :pos,
                    `is_interstate` = :is_inter,
                    `payment_mode` = :pmode,
                    `sales_type` = :stype,
                    `reference_no` = :ref_no,
                    `gross_amount` = :gross,
                    `item_discount_amount` = :item_disc,
                    `taxable_amount` = :taxable,
                    `cgst_amount` = :cgst,
                    `sgst_amount` = :sgst,
                    `igst_amount` = :igst,
                    `total_tax_amount` = :tax_amt,
                    `round_off` = :round_off,
                    `grand_total` = :grand,
                    `paid_amount` = :paid,
                    `balance_amount` = :balance,
                    `payment_status` = :pstatus,
                    `notes` = :notes,
                    `status` = 'active',
                    `cancelled_reason` = NULL,
                    `cancelled_by` = NULL,
                    `cancelled_at` = NULL
                    WHERE `id` = :id AND `company_id` = :comp_id";

                $db->execute($sqlUpdate, [
                    'id' => $id,
                    'comp_id' => $companyId,
                    'inv_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'cust_id' => $customerId,
                    'cust_name' => $customer['name'],
                    'cust_mobile' => $customer['mobile'],
                    'cust_gstin' => $customer['gstin'],
                    'cust_state' => $customer['state'],
                    'cust_scode' => $customerStateCode,
                    'pos' => $placeOfSupply,
                    'is_inter' => $isInterstate ? 1 : 0,
                    'pmode' => $paymentMode,
                    'stype' => $salesType,
                    'ref_no' => $referenceNo,
                    'gross' => $grossTotal,
                    'item_disc' => $itemDiscountTotal,
                    'taxable' => $taxableTotal,
                    'cgst' => $cgstTotal,
                    'sgst' => $sgstTotal,
                    'igst' => $igstTotal,
                    'tax_amt' => $totalTax,
                    'round_off' => $roundOff,
                    'grand' => $grandTotal,
                    'paid' => $paidAmount,
                    'balance' => $balanceAmount,
                    'pstatus' => $paymentStatus,
                    'notes' => $notes
                ]);

                // 4. Insert new items and stock movements
                $stmtItem = $db->getConnection()->prepare(
                    "INSERT INTO `sales_items` 
                     (`company_id`, `sale_id`, `item_id`, `item_name`, `barcode`, `hsn_sac`, `quantity`, `unit_name`, `rate`, `discount_percent`, `discount_amount`, `taxable_amount`, `gst_rate`, `cgst_rate`, `cgst_amount`, `sgst_rate`, `sgst_amount`, `igst_rate`, `igst_amount`, `total_amount`, `cost_rate`) 
                     VALUES 
                     (:company_id, :sale_id, :item_id, :item_name, :barcode, :hsn_sac, :quantity, :unit_name, :rate, :discount_percent, :discount_amount, :taxable_amount, :gst_rate, :cgst_rate, :cgst_amount, :sgst_rate, :sgst_amount, :igst_rate, :igst_amount, :total_amount, :cost_rate)"
                );

                foreach ($processedItems as $pItem) {
                    $stmtItem->execute(array_merge(['company_id' => $companyId, 'sale_id' => $id], $pItem));
                    $saleItemId = $db->lastInsertId();

                    StockEngine::recordMovement(
                        $companyId,
                        $fyId,
                        $pItem['item_id'],
                        'sale',
                        $id,
                        $saleItemId,
                        $invoiceDate,
                        0.000,
                        $pItem['quantity'],
                        $pItem['rate'],
                        $pItem['taxable_amount'],
                        "Sale Invoice {$invoiceNumber} (Updated)"
                    );
                }

                // 5. Post double-entry accounting ledger
                $customerAccountId = AccountingEngine::getCustomerAccountId($companyId, $customerId, $customer['name']);
                $salesAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Sales Account', 'income', 13);
                $roundOffAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Round Off Account', 'expenses', 18);

                $ledgerLines = [];
                if ($paymentMode === 'cash') {
                    $cashAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Cash Account', 'assets', 1, true, false);
                    $ledgerLines[] = ['account_id' => $cashAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Cash Sale {$invoiceNumber}"];
                } elseif ($paymentMode === 'bank' || $paymentMode === 'upi') {
                    $bankAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Primary Bank Account', 'assets', 2, false, true);
                    $ledgerLines[] = ['account_id' => $bankAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Bank/UPI Sale {$invoiceNumber}"];
                } else {
                    $ledgerLines[] = ['account_id' => $customerAccountId, 'debit' => $grandTotal, 'credit' => 0.00, 'narration' => "Credit Sale {$invoiceNumber}"];
                }

                $ledgerLines[] = ['account_id' => $salesAccountId, 'debit' => 0.00, 'credit' => $taxableTotal, 'narration' => "Sales Value for {$invoiceNumber}"];

                if ($gstEnabled && $totalTax > 0) {
                    if ($isInterstate) {
                        $igstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output IGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $igstAccountId, 'debit' => 0.00, 'credit' => $igstTotal, 'narration' => "Output IGST on {$invoiceNumber}"];
                    } else {
                        $cgstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output CGST', 'liabilities', 8);
                        $sgstAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Output SGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $cgstAccountId, 'debit' => 0.00, 'credit' => $cgstTotal, 'narration' => "Output CGST on {$invoiceNumber}"];
                        $ledgerLines[] = ['account_id' => $sgstAccountId, 'debit' => 0.00, 'credit' => $sgstTotal, 'narration' => "Output SGST on {$invoiceNumber}"];
                    }
                }

                if ($roundOff > 0) {
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => 0.00, 'credit' => $roundOff, 'narration' => "Round Off on {$invoiceNumber}"];
                } elseif ($roundOff < 0) {
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => abs($roundOff), 'credit' => 0.00, 'narration' => "Round Off on {$invoiceNumber}"];
                }

                AccountingEngine::postVoucher($companyId, $fyId, 'sale', $id, $invoiceNumber, $invoiceDate, $ledgerLines);

                AuditLog::log('Invoice Updated', 'Sales', $id, ['invoice_number' => $invoiceNumber, 'grand_total' => $grandTotal]);
            });

            $this->flash('success', "Invoice [{$sale['invoice_number']}] updated successfully.");
            $this->redirect('/sales/view/' . $id);
        } catch (\Throwable $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/sales/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        $companyId = Session::getCompanyId();
        $sale = $this->saleModel->find($id);
        if (!$sale || (int)$sale['company_id'] !== $companyId) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
            return;
        }

        $db = Database::getInstance();
        try {
            $db->transaction(function(Database $db) use ($companyId, $id, $sale) {
                // 1. Delete associated stock movements
                $db->execute(
                    "DELETE FROM `stock_movements` 
                     WHERE `company_id` = :comp_id AND `transaction_type` = 'sale' AND `transaction_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                // 2. Delete associated double-entry ledger entries
                $db->execute(
                    "DELETE FROM `ledger_entries` 
                     WHERE `company_id` = :comp_id AND `voucher_type` = 'sale' AND `voucher_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                // 3. Delete receipt allocations if any
                $db->execute(
                    "DELETE FROM `receipt_allocations` 
                     WHERE `company_id` = :comp_id AND `sale_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                // 4. Delete sales line items
                $db->execute(
                    "DELETE FROM `sales_items` 
                     WHERE `company_id` = :comp_id AND `sale_id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                // 5. Delete sales record
                $db->execute(
                    "DELETE FROM `sales` 
                     WHERE `company_id` = :comp_id AND `id` = :id",
                    ['comp_id' => $companyId, 'id' => $id]
                );

                AuditLog::log('Invoice Deleted', 'Sales', $id, ['invoice_number' => $sale['invoice_number'], 'grand_total' => $sale['grand_total']]);
            });

            $this->flash('success', "Invoice [{$sale['invoice_number']}] has been permanently deleted.");
        } catch (\Throwable $e) {
            error_log("Failed to delete invoice {$id}: " . $e->getMessage());
            $this->flash('danger', 'Failed to delete invoice: ' . $e->getMessage());
        }

        $this->redirect('/sales');
    }
}
