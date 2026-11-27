<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\AuditLog;
use App\Services\GstEngine;
use App\Services\StockEngine;
use App\Services\AccountingEngine;
use App\Services\NumberingEngine;
use Exception;

class PurchaseController extends Controller
{
    private Purchase $purchaseModel;
    private Vendor $vendorModel;
    private Item $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->purchaseModel = new Purchase();
        $this->vendorModel = new Vendor();
        $this->itemModel = new Item();
    }

    public function index(): void
    {
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');
        $search = $this->request->input('search');

        $purchases = $this->purchaseModel->getPurchaseList([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'search' => $search
        ]);

        $this->view('transactions.purchases.index', [
            'pageTitle' => 'Purchase Bills',
            'purchases' => $purchases,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search
        ]);
    }

    public function create(): void
    {
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();

        $vendors = $this->vendorModel->getVendorsWithOutstanding();
        $items = $this->itemModel->getItemsWithDetails();
        $nextPurchaseNumber = NumberingEngine::previewNextNumber($companyId, $fyId, 'purchase');

        $this->view('transactions.purchases.form', [
            'pageTitle' => 'New Purchase Bill',
            'vendors' => $vendors,
            'items' => $items,
            'nextPurchaseNumber' => $nextPurchaseNumber,
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

        $vendorId = (int)$this->request->input('vendor_id');
        $supplierInvoiceNo = trim($this->request->input('supplier_invoice_number', ''));
        $purchaseDate = $this->request->input('purchase_date', date('Y-m-d'));
        $dueDate = $this->request->input('due_date') ?: null;
        $paymentMode = $this->request->input('payment_mode', 'credit');
        $notes = trim($this->request->input('notes', ''));

        $rawItems = $this->request->input('items', []);
        if (empty($vendorId) || empty($rawItems)) {
            $this->flash('danger', 'Please select a vendor and at least one line item.');
            $this->redirect('/purchases/create');
        }

        $vendor = $this->vendorModel->find($vendorId);
        if (!$vendor) {
            $this->flash('danger', 'Selected vendor does not exist.');
            $this->redirect('/purchases/create');
        }

        $vendorStateCode = $vendor['state_code'] ?: '27';
        $isInterstate = GstEngine::isInterstate($companyStateCode, $vendorStateCode);

        $db = Database::getInstance();

        try {
            $purchaseId = $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $gstEnabled, $vendorId, $vendor, $supplierInvoiceNo,
                $purchaseDate, $dueDate, $paymentMode, $notes, $isInterstate, $rawItems, $vendorStateCode
            ) {
                $purchaseNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'purchase');

                $grossTotal = 0.00;
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

                    if ($itemId <= 0 || $qty <= 0) continue;

                    $itemRecord = $this->itemModel->find($itemId);
                    $itemName = $itemRecord ? $itemRecord['name'] : ($raw['item_name'] ?? 'Item');
                    $hsnSac = $itemRecord ? $itemRecord['hsn_sac'] : null;
                    $unitName = $raw['unit_name'] ?? 'PCS';

                    $computed = GstEngine::computeLineItem($qty, $rate, $discPct, 0.00, $gstRate, $isInterstate, $gstEnabled);

                    $grossTotal += $computed['gross_amount'];
                    $taxableTotal += $computed['taxable_amount'];
                    $cgstTotal += $computed['cgst_amount'];
                    $sgstTotal += $computed['sgst_amount'];
                    $igstTotal += $computed['igst_amount'];

                    $processedItems[] = [
                        'item_id' => $itemId,
                        'item_name' => $itemName,
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
                        'total_amount' => $computed['total_amount']
                    ];
                }

                if (empty($processedItems)) {
                    throw new Exception("Bill must contain at least one valid line item.");
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

                // 1. Insert Purchase
                $sqlPur = "INSERT INTO `purchases` 
                    (`company_id`, `financial_year_id`, `purchase_number`, `supplier_invoice_number`, `purchase_date`, `due_date`, `vendor_id`, `vendor_name`, `vendor_gstin`, `vendor_state`, `vendor_state_code`, `is_interstate`, `payment_mode`, `gross_amount`, `taxable_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_tax_amount`, `round_off`, `grand_total`, `paid_amount`, `balance_amount`, `payment_status`, `notes`, `status`, `created_by`) 
                    VALUES 
                    (:comp_id, :fy_id, :pnum, :sinv, :pdate, :due, :vid, :vname, :vgst, :vstate, :vcode, :is_inter, :pmode, :gross, :taxable, :cgst, :sgst, :igst, :tax_amt, :round_off, :grand, :paid, :balance, :pstatus, :notes, 'active', :created_by)";

                $db->execute($sqlPur, [
                    'comp_id' => $companyId,
                    'fy_id' => $fyId,
                    'pnum' => $purchaseNumber,
                    'sinv' => $supplierInvoiceNo,
                    'pdate' => $purchaseDate,
                    'due' => $dueDate,
                    'vid' => $vendorId,
                    'vname' => $vendor['name'],
                    'vgst' => $vendor['gstin'],
                    'vstate' => $vendor['state'],
                    'vcode' => $vendorStateCode,
                    'is_inter' => $isInterstate ? 1 : 0,
                    'pmode' => $paymentMode,
                    'gross' => $grossTotal,
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

                $purchaseId = $db->lastInsertId();

                // 2. Insert Purchase Items & Record Inward Stock Movement
                $stmtItem = $db->getConnection()->prepare(
                    "INSERT INTO `purchase_items` 
                     (`company_id`, `purchase_id`, `item_id`, `item_name`, `hsn_sac`, `quantity`, `unit_name`, `rate`, `discount_percent`, `discount_amount`, `taxable_amount`, `gst_rate`, `cgst_rate`, `cgst_amount`, `sgst_rate`, `sgst_amount`, `igst_rate`, `igst_amount`, `total_amount`) 
                     VALUES 
                     (:company_id, :purchase_id, :item_id, :item_name, :hsn_sac, :quantity, :unit_name, :rate, :discount_percent, :discount_amount, :taxable_amount, :gst_rate, :cgst_rate, :cgst_amount, :sgst_rate, :sgst_amount, :igst_rate, :igst_amount, :total_amount)"
                );

                foreach ($processedItems as $pItem) {
                    $stmtItem->execute(array_merge(['company_id' => $companyId, 'purchase_id' => $purchaseId], $pItem));
                    $purchaseItemId = $db->lastInsertId();

                    // Record Inward Stock Movement
                    StockEngine::recordMovement(
                        $companyId,
                        $fyId,
                        $pItem['item_id'],
                        'purchase',
                        $purchaseId,
                        $purchaseItemId,
                        $purchaseDate,
                        $pItem['quantity'],
                        0.000,
                        $pItem['rate'],
                        $pItem['taxable_amount'],
                        "Purchase Bill {$purchaseNumber}"
                    );
                }

                // 3. Automated Double-Entry Ledger Posting
                $vendorAccountId = AccountingEngine::getVendorAccountId($companyId, $vendorId, $vendor['name']);
                $purchaseAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Purchase Account', 'expenses', 16);
                $roundOffAccountId = AccountingEngine::getOrCreateAccount($companyId, 'Round Off Account', 'expenses', 18);

                $ledgerLines = [];

                // Debit Purchase Account (Taxable Total)
                $ledgerLines[] = ['account_id' => $purchaseAccountId, 'debit' => $taxableTotal, 'credit' => 0.00, 'narration' => "Purchase Value {$purchaseNumber}"];

                // Debit Input GST Accounts if applicable
                if ($gstEnabled && $totalTax > 0) {
                    if ($isInterstate) {
                        $inputIgstId = AccountingEngine::getOrCreateAccount($companyId, 'Input IGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $inputIgstId, 'debit' => $igstTotal, 'credit' => 0.00, 'narration' => "Input IGST on {$purchaseNumber}"];
                    } else {
                        $inputCgstId = AccountingEngine::getOrCreateAccount($companyId, 'Input CGST', 'liabilities', 8);
                        $inputSgstId = AccountingEngine::getOrCreateAccount($companyId, 'Input SGST', 'liabilities', 8);
                        $ledgerLines[] = ['account_id' => $inputCgstId, 'debit' => $cgstTotal, 'credit' => 0.00, 'narration' => "Input CGST on {$purchaseNumber}"];
                        $ledgerLines[] = ['account_id' => $inputSgstId, 'debit' => $sgstTotal, 'credit' => 0.00, 'narration' => "Input SGST on {$purchaseNumber}"];
                    }
                }

                // Round Off adjustment
                if ($roundOff > 0) {
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => $roundOff, 'credit' => 0.00, 'narration' => "Round off {$purchaseNumber}"];
                } elseif ($roundOff < 0) {
                    $ledgerLines[] = ['account_id' => $roundOffAccountId, 'debit' => 0.00, 'credit' => abs($roundOff), 'narration' => "Round off {$purchaseNumber}"];
                }

                // Credit Vendor / Cash / Bank Account (Grand Total)
                if ($paymentMode === 'cash') {
                    $cashId = AccountingEngine::getOrCreateAccount($companyId, 'Cash Account', 'assets', 1, true, false);
                    $ledgerLines[] = ['account_id' => $cashId, 'debit' => 0.00, 'credit' => $grandTotal, 'narration' => "Cash Paid for {$purchaseNumber}"];
                } elseif ($paymentMode === 'bank' || $paymentMode === 'upi') {
                    $bankId = AccountingEngine::getOrCreateAccount($companyId, 'Primary Bank Account', 'assets', 2, false, true);
                    $ledgerLines[] = ['account_id' => $bankId, 'debit' => 0.00, 'credit' => $grandTotal, 'narration' => "Bank Paid for {$purchaseNumber}"];
                } else {
                    $ledgerLines[] = ['account_id' => $vendorAccountId, 'debit' => 0.00, 'credit' => $grandTotal, 'narration' => "Credit Purchase {$purchaseNumber}"];
                }

                AccountingEngine::postVoucher($companyId, $fyId, 'purchase', $purchaseId, $purchaseNumber, $purchaseDate, $ledgerLines);

                AuditLog::log('Purchase Created', 'Purchases', $purchaseId, ['purchase_number' => $purchaseNumber, 'grand_total' => $grandTotal]);
                return $purchaseId;
            });

            $this->flash('success', "Purchase Bill recorded successfully. Inventory stock has been updated.");
            $this->redirect('/purchases/view/' . $purchaseId);

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/purchases/create');
        }
    }

    public function viewBill(int $id): void
    {
        $purchase = $this->purchaseModel->getPurchaseWithItems($id);
        if (!$purchase) {
            $this->flash('danger', 'Purchase bill not found.');
            $this->redirect('/purchases');
        }

        $this->view('transactions.purchases.view', [
            'pageTitle' => 'Purchase ' . $purchase['purchase_number'],
            'purchase' => $purchase
        ]);
    }

    public function cancel(int $id): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $userId = Session::getUserId();
        $reason = trim($this->request->input('cancelled_reason', 'Cancelled by User'));

        $purchase = $this->purchaseModel->find($id);
        if (!$purchase) {
            $this->flash('danger', 'Purchase bill not found.');
            $this->redirect('/purchases');
        }

        if ($purchase['status'] === 'cancelled') {
            $this->flash('warning', 'Purchase is already cancelled.');
            $this->redirect('/purchases/view/' . $id);
        }

        $db = Database::getInstance();
        $db->transaction(function(Database $db) use ($companyId, $userId, $id, $purchase, $reason) {
            $db->execute(
                "UPDATE `purchases` 
                 SET `status` = 'cancelled', `cancelled_reason` = :reason, `cancelled_by` = :uid, `cancelled_at` = NOW() 
                 WHERE `id` = :id AND `company_id` = :comp_id",
                ['reason' => $reason, 'uid' => $userId, 'id' => $id, 'comp_id' => $companyId]
            );

            StockEngine::cancelTransactionMovements($companyId, 'purchase', $id);
            AccountingEngine::cancelVoucher($companyId, 'purchase', $id);

            AuditLog::log('Purchase Cancelled', 'Purchases', $id, ['purchase_number' => $purchase['purchase_number']]);
        });

        $this->flash('success', "Purchase [{$purchase['purchase_number']}] cancelled. Inward stock and ledger entries have been reversed.");
        $this->redirect('/purchases/view/' . $id);
    }
}
