<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\Journal;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\AuditLog;
use App\Services\AccountingEngine;
use App\Services\NumberingEngine;
use Exception;

class VoucherController extends Controller
{
    private Receipt $receiptModel;
    private Payment $paymentModel;
    private Journal $journalModel;
    private Customer $customerModel;
    private Vendor $vendorModel;
    private Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->receiptModel = new Receipt();
        $this->paymentModel = new Payment();
        $this->journalModel = new Journal();
        $this->customerModel = new Customer();
        $this->vendorModel = new Vendor();
        $this->accountModel = new Account();
    }

    // ==========================================
    // 1. RECEIPTS
    // ==========================================
    public function receiptsIndex(): void
    {
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');
        $receipts = $this->receiptModel->getReceiptList([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $this->view('transactions.vouchers.receipts_index', [
            'pageTitle' => 'Receipt Vouchers',
            'receipts' => $receipts,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function createReceipt(): void
    {
        $companyId = Session::getCompanyId();
        $customers = $this->customerModel->getCustomersWithOutstanding();
        $cashBankAccounts = $this->accountModel->getCashAndBankAccounts();

        $this->view('transactions.vouchers.receipt_form', [
            'pageTitle' => 'New Customer Receipt',
            'customers' => $customers,
            'cashBankAccounts' => $cashBankAccounts
        ]);
    }

    public function storeReceipt(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();

        $customerId = (int)$this->request->input('customer_id');
        $depositAccountId = (int)$this->request->input('deposit_account_id');
        $receiptDate = $this->request->input('receipt_date', date('Y-m-d'));
        $amount = (float)$this->request->input('amount', 0.00);
        $paymentMode = $this->request->input('payment_mode', 'cash');
        $chequeNo = trim($this->request->input('cheque_no', ''));
        $utrNo = trim($this->request->input('utr_no', ''));
        $notes = trim($this->request->input('notes', ''));

        if ($customerId <= 0 || $depositAccountId <= 0 || $amount <= 0) {
            $this->flash('danger', 'Please provide valid customer, deposit account, and amount.');
            $this->redirect('/vouchers/receipts/create');
        }

        $customer = $this->customerModel->find($customerId);
        $db = Database::getInstance();

        try {
            $receiptId = $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $customerId, $customer, $depositAccountId,
                $receiptDate, $amount, $paymentMode, $chequeNo, $utrNo, $notes
            ) {
                $receiptNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'receipt');

                $sql = "INSERT INTO `receipts` 
                    (`company_id`, `financial_year_id`, `receipt_number`, `receipt_date`, `customer_id`, `deposit_account_id`, `amount`, `payment_mode`, `cheque_no`, `utr_no`, `notes`, `status`, `created_by`) 
                    VALUES 
                    (:comp_id, :fy_id, :rnum, :rdate, :cid, :dep_acc, :amount, :pmode, :cheque, :utr, :notes, 'active', :uid)";

                $db->execute($sql, [
                    'comp_id' => $companyId,
                    'fy_id' => $fyId,
                    'rnum' => $receiptNumber,
                    'rdate' => $receiptDate,
                    'cid' => $customerId,
                    'dep_acc' => $depositAccountId,
                    'amount' => $amount,
                    'pmode' => $paymentMode,
                    'cheque' => $chequeNo ?: null,
                    'utr' => $utrNo ?: null,
                    'notes' => $notes ?: null,
                    'uid' => $userId
                ]);

                $receiptId = $db->lastInsertId();

                // Double-Entry Ledger: Debit Cash/Bank, Credit Customer Account
                $customerAccountId = AccountingEngine::getCustomerAccountId($companyId, $customerId, $customer['name']);
                $ledgerLines = [
                    ['account_id' => $depositAccountId, 'debit' => $amount, 'credit' => 0.00, 'narration' => "Receipt {$receiptNumber} from {$customer['name']}"],
                    ['account_id' => $customerAccountId, 'debit' => 0.00, 'credit' => $amount, 'narration' => "Payment received {$receiptNumber}"]
                ];

                AccountingEngine::postVoucher($companyId, $fyId, 'receipt', $receiptId, $receiptNumber, $receiptDate, $ledgerLines);

                AuditLog::log('Receipt Created', 'Receipts', $receiptId, ['amount' => $amount, 'customer' => $customer['name']]);
                return $receiptId;
            });

            $this->flash('success', "Receipt voucher recorded successfully.");
            $this->redirect('/vouchers/receipts');

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/vouchers/receipts/create');
        }
    }

    // ==========================================
    // 2. PAYMENTS
    // ==========================================
    public function paymentsIndex(): void
    {
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');
        $payments = $this->paymentModel->getPaymentList([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $this->view('transactions.vouchers.payments_index', [
            'pageTitle' => 'Payment Vouchers',
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function createPayment(): void
    {
        $companyId = Session::getCompanyId();
        $vendors = $this->vendorModel->getVendorsWithOutstanding();
        $cashBankAccounts = $this->accountModel->getCashAndBankAccounts();
        $expenseAccounts = $this->accountModel->all([], 'account_name ASC');

        $this->view('transactions.vouchers.payment_form', [
            'pageTitle' => 'New Payment Voucher',
            'vendors' => $vendors,
            'cashBankAccounts' => $cashBankAccounts,
            'expenseAccounts' => $expenseAccounts
        ]);
    }

    public function storePayment(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();

        $vendorId = $this->request->input('vendor_id') ? (int)$this->request->input('vendor_id') : null;
        $expenseAccountId = $this->request->input('expense_account_id') ? (int)$this->request->input('expense_account_id') : null;
        $paidFromAccountId = (int)$this->request->input('paid_from_account_id');
        $paymentDate = $this->request->input('payment_date', date('Y-m-d'));
        $amount = (float)$this->request->input('amount', 0.00);
        $paymentMode = $this->request->input('payment_mode', 'cash');
        $chequeNo = trim($this->request->input('cheque_no', ''));
        $utrNo = trim($this->request->input('utr_no', ''));
        $notes = trim($this->request->input('notes', ''));

        if ((!$vendorId && !$expenseAccountId) || $paidFromAccountId <= 0 || $amount <= 0) {
            $this->flash('danger', 'Please specify payee (vendor or expense account), payment source, and amount.');
            $this->redirect('/vouchers/payments/create');
        }

        $db = Database::getInstance();

        try {
            $paymentId = $db->transaction(function(Database $db) use (
                $companyId, $fyId, $userId, $vendorId, $expenseAccountId, $paidFromAccountId,
                $paymentDate, $amount, $paymentMode, $chequeNo, $utrNo, $notes
            ) {
                $paymentNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'payment');

                $sql = "INSERT INTO `payments` 
                    (`company_id`, `financial_year_id`, `payment_number`, `payment_date`, `vendor_id`, `expense_account_id`, `paid_from_account_id`, `amount`, `payment_mode`, `cheque_no`, `utr_no`, `notes`, `status`, `created_by`) 
                    VALUES 
                    (:comp_id, :fy_id, :pnum, :pdate, :vid, :exp_acc, :from_acc, :amount, :pmode, :cheque, :utr, :notes, 'active', :uid)";

                $db->execute($sql, [
                    'comp_id' => $companyId,
                    'fy_id' => $fyId,
                    'pnum' => $paymentNumber,
                    'pdate' => $paymentDate,
                    'vid' => $vendorId,
                    'exp_acc' => $expenseAccountId,
                    'from_acc' => $paidFromAccountId,
                    'amount' => $amount,
                    'pmode' => $paymentMode,
                    'cheque' => $chequeNo ?: null,
                    'utr' => $utrNo ?: null,
                    'notes' => $notes ?: null,
                    'uid' => $userId
                ]);

                $paymentId = $db->lastInsertId();

                // Target debit account
                if ($vendorId) {
                    $vendor = $this->vendorModel->find($vendorId);
                    $debitAccountId = AccountingEngine::getVendorAccountId($companyId, $vendorId, $vendor['name']);
                    $partyName = $vendor['name'];
                } else {
                    $debitAccountId = $expenseAccountId;
                    $expAcc = $this->accountModel->find($expenseAccountId);
                    $partyName = $expAcc['account_name'];
                }

                // Double-Entry Ledger: Debit Vendor/Expense, Credit Cash/Bank
                $ledgerLines = [
                    ['account_id' => $debitAccountId, 'debit' => $amount, 'credit' => 0.00, 'narration' => "Payment {$paymentNumber} to {$partyName}"],
                    ['account_id' => $paidFromAccountId, 'debit' => 0.00, 'credit' => $amount, 'narration' => "Paid via {$paymentNumber}"]
                ];

                AccountingEngine::postVoucher($companyId, $fyId, 'payment', $paymentId, $paymentNumber, $paymentDate, $ledgerLines);

                AuditLog::log('Payment Created', 'Payments', $paymentId, ['amount' => $amount, 'party' => $partyName]);
                return $paymentId;
            });

            $this->flash('success', "Payment voucher recorded successfully.");
            $this->redirect('/vouchers/payments');

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/vouchers/payments/create');
        }
    }

    // ==========================================
    // 3. JOURNAL ENTRIES
    // ==========================================
    public function journalsIndex(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();
        $journals = $db->fetchAll(
            "SELECT * FROM `journals` WHERE `company_id` = :comp_id ORDER BY `journal_date` DESC, `id` DESC",
            ['comp_id' => $companyId]
        );

        $this->view('transactions.vouchers.journals_index', [
            'pageTitle' => 'Journal Vouchers',
            'journals' => $journals
        ]);
    }

    public function createJournal(): void
    {
        $companyId = Session::getCompanyId();
        $accounts = $this->accountModel->all(['status' => 'active'], 'account_name ASC');

        $this->view('transactions.vouchers.journal_form', [
            'pageTitle' => 'New Journal Voucher',
            'accounts' => $accounts
        ]);
    }

    public function storeJournal(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();
        $userId = Session::getUserId();

        $journalDate = $this->request->input('journal_date', date('Y-m-d'));
        $narration = trim($this->request->input('narration', ''));
        $rawLines = $this->request->input('lines', []);

        if (empty($narration) || empty($rawLines)) {
            $this->flash('danger', 'Narration and journal lines are required.');
            $this->redirect('/vouchers/journals/create');
        }

        $db = Database::getInstance();

        try {
            $journalId = $db->transaction(function(Database $db) use ($companyId, $fyId, $userId, $journalDate, $narration, $rawLines) {
                $journalNumber = NumberingEngine::getNextNumber($companyId, $fyId, 'journal');

                $totalDebit = 0.00;
                $totalCredit = 0.00;
                $validLines = [];

                foreach ($rawLines as $l) {
                    $accId = (int)($l['account_id'] ?? 0);
                    $debit = round((float)($l['debit'] ?? 0), 2);
                    $credit = round((float)($l['credit'] ?? 0), 2);
                    $lineNarration = trim($l['narration'] ?? '') ?: $narration;

                    if ($accId <= 0 || ($debit == 0 && $credit == 0)) {
                        continue;
                    }

                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    $validLines[] = [
                        'account_id' => $accId,
                        'debit' => $debit,
                        'credit' => $credit,
                        'narration' => $lineNarration
                    ];
                }

                if (count($validLines) < 2) {
                    throw new Exception("Journal must contain at least 2 lines (one debit and one credit).");
                }

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new Exception("Total Debit (₹{$totalDebit}) must equal Total Credit (₹{$totalCredit}).");
                }

                // Insert Journal Header
                $db->execute(
                    "INSERT INTO `journals` (`company_id`, `financial_year_id`, `journal_number`, `journal_date`, `narration`, `total_debit`, `total_credit`, `status`, `created_by`) 
                     VALUES (:comp_id, :fy_id, :jnum, :jdate, :narration, :tdebit, :tcredit, 'active', :uid)",
                    [
                        'comp_id' => $companyId,
                        'fy_id' => $fyId,
                        'jnum' => $journalNumber,
                        'jdate' => $journalDate,
                        'narration' => $narration,
                        'tdebit' => $totalDebit,
                        'tcredit' => $totalCredit,
                        'uid' => $userId
                    ]
                );

                $journalId = $db->lastInsertId();

                // Insert Journal Detail Lines
                $stmtLine = $db->getConnection()->prepare(
                    "INSERT INTO `journal_entries` (`company_id`, `journal_id`, `account_id`, `debit`, `credit`, `narration`) 
                     VALUES (:comp_id, :jid, :acc_id, :debit, :credit, :narration)"
                );

                foreach ($validLines as $vl) {
                    $stmtLine->execute([
                        'comp_id' => $companyId,
                        'jid' => $journalId,
                        'acc_id' => $vl['account_id'],
                        'debit' => $vl['debit'],
                        'credit' => $vl['credit'],
                        'narration' => $vl['narration']
                    ]);
                }

                // Post directly into General Ledger
                AccountingEngine::postVoucher($companyId, $fyId, 'journal', $journalId, $journalNumber, $journalDate, $validLines);

                AuditLog::log('Journal Created', 'Journals', $journalId, ['total' => $totalDebit]);
                return $journalId;
            });

            $this->flash('success', "Journal voucher posted successfully.");
            $this->redirect('/vouchers/journals');

        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/vouchers/journals/create');
        }
    }
}
