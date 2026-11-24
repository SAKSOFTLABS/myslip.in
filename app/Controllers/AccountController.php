<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AuditLog;

class AccountController extends Controller
{
    private Account $accountModel;
    private AccountGroup $groupModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account();
        $this->groupModel = new AccountGroup();
    }

    public function index(): void
    {
        $accounts = $this->accountModel->getAccountsWithBalances();
        $this->view('masters.accounts.index', [
            'pageTitle' => 'Chart of Accounts',
            'accounts' => $accounts
        ]);
    }

    public function create(): void
    {
        $companyId = Session::getCompanyId();
        $groups = $this->groupModel->getGroups($companyId);

        $this->view('masters.accounts.form', [
            'pageTitle' => 'Add Ledger Account',
            'account' => null,
            'groups' => $groups
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();

        $accountName = trim($this->request->input('account_name', ''));
        $groupId = (int)$this->request->input('group_id');
        $openingBalance = (float)$this->request->input('opening_balance', 0.00);
        $balanceType = $this->request->input('balance_type', 'DR');
        $isBank = (int)$this->request->input('is_bank', 0);
        $isCash = (int)$this->request->input('is_cash', 0);
        $bankAccountNo = trim($this->request->input('bank_account_no', ''));
        $bankIfsc = strtoupper(trim($this->request->input('bank_ifsc', '')));

        if (empty($accountName) || empty($groupId)) {
            $this->flash('danger', 'Account Name and Group are required.');
            $this->redirect('/accounts/create');
        }

        $accId = $this->accountModel->create([
            'company_id' => $companyId,
            'group_id' => $groupId,
            'account_name' => $accountName,
            'opening_balance' => $openingBalance,
            'balance_type' => $balanceType,
            'is_bank' => $isBank,
            'is_cash' => $isCash,
            'bank_account_no' => $bankAccountNo ?: null,
            'bank_ifsc' => $bankIfsc ?: null,
            'is_system' => 0,
            'status' => 'active'
        ]);

        AuditLog::log('Account Created', 'Accounts', $accId);
        $this->flash('success', "Account [{$accountName}] created successfully.");
        $this->redirect('/accounts');
    }

    public function edit(int $id): void
    {
        $companyId = Session::getCompanyId();
        $account = $this->accountModel->find($id);
        if (!$account) {
            $this->flash('danger', 'Account not found.');
            $this->redirect('/accounts');
        }

        $groups = $this->groupModel->getGroups($companyId);
        $this->view('masters.accounts.form', [
            'pageTitle' => 'Edit Account — ' . $account['account_name'],
            'account' => $account,
            'groups' => $groups
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $account = $this->accountModel->find($id);
        if (!$account) {
            $this->flash('danger', 'Account not found.');
            $this->redirect('/accounts');
        }

        $accountName = trim($this->request->input('account_name', ''));
        $groupId = (int)$this->request->input('group_id');
        $isBank = (int)$this->request->input('is_bank', 0);
        $isCash = (int)$this->request->input('is_cash', 0);
        $bankAccountNo = trim($this->request->input('bank_account_no', ''));
        $bankIfsc = strtoupper(trim($this->request->input('bank_ifsc', '')));

        $this->accountModel->update($id, [
            'group_id' => $groupId,
            'account_name' => $accountName,
            'is_bank' => $isBank,
            'is_cash' => $isCash,
            'bank_account_no' => $bankAccountNo ?: null,
            'bank_ifsc' => $bankIfsc ?: null
        ]);

        AuditLog::log('Account Updated', 'Accounts', $id);
        $this->flash('success', "Account [{$accountName}] updated.");
        $this->redirect('/accounts');
    }

    public function delete(int $id): void
    {
        $account = $this->accountModel->find($id);
        if (!$account) {
            $this->flash('danger', 'Account not found.');
            $this->redirect('/accounts');
        }

        if ($account['is_system']) {
            $this->flash('danger', 'System standard accounts cannot be deleted.');
            $this->redirect('/accounts');
        }

        // Check if account has ledger transactions
        $db = Database::getInstance();
        $txCount = (int)$db->fetchValue("SELECT COUNT(*) FROM `ledger_entries` WHERE `account_id` = :id", ['id' => $id]);
        if ($txCount > 0) {
            $this->flash('danger', 'Cannot delete account because it contains posted transactions.');
            $this->redirect('/accounts');
        }

        $this->accountModel->delete($id);
        AuditLog::log('Account Deleted', 'Accounts', $id);
        $this->flash('success', "Account [{$account['account_name']}] deleted.");
        $this->redirect('/accounts');
    }

    public function ledger(int $id): void
    {
        $companyId = Session::getCompanyId();
        $account = $this->accountModel->find($id);
        if (!$account) {
            $this->flash('danger', 'Account not found.');
            $this->redirect('/accounts');
        }

        $db = Database::getInstance();
        $ledgerEntries = $db->fetchAll(
            "SELECT * FROM `ledger_entries` 
             WHERE `company_id` = :comp_id AND `account_id` = :acc_id AND `is_cancelled` = 0 
             ORDER BY `voucher_date` ASC, `id` ASC",
            ['comp_id' => $companyId, 'acc_id' => $id]
        );

        $this->view('masters.accounts.ledger', [
            'pageTitle' => 'General Ledger: ' . $account['account_name'],
            'account' => $account,
            'ledgerEntries' => $ledgerEntries
        ]);
    }
}
