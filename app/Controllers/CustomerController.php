<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Services\AccountingEngine;

class CustomerController extends Controller
{
    private Customer $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
    }

    public function index(): void
    {
        $customers = $this->customerModel->getCustomersWithOutstanding();
        $this->view('masters.customers.index', [
            'pageTitle' => 'Customers Directory',
            'customers' => $customers
        ]);
    }

    public function create(): void
    {
        $this->view('masters.customers.form', [
            'pageTitle' => 'Add New Customer',
            'customer' => null,
            'states' => INDIAN_STATES
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();

        $name = trim($this->request->input('name', ''));
        $contactPerson = trim($this->request->input('contact_person', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $alternateMobile = trim($this->request->input('alternate_mobile', ''));
        $email = trim($this->request->input('email', ''));
        $billingAddress = trim($this->request->input('billing_address') ?: $this->request->input('address', ''));
        $city = trim($this->request->input('city', ''));
        $district = trim($this->request->input('district', ''));
        $state = $this->request->input('state', Session::get('company_state', 'Maharashtra'));
        $pinCode = trim($this->request->input('pin_code', ''));
        $gstin = strtoupper(trim($this->request->input('gstin', '')));
        $pan = strtoupper(trim($this->request->input('pan', '')));
        $creditLimit = (float)$this->request->input('credit_limit', 0.00);
        $creditDays = (int)$this->request->input('credit_days', 0);
        $openingBalance = (float)$this->request->input('opening_balance', 0.00);
        $balanceType = $this->request->input('balance_type', 'DR');
        $notes = trim($this->request->input('notes', ''));

        if (empty($name)) {
            $this->flash('danger', 'Customer Name is required.');
            $this->redirect('/customers/create');
        }

        // Determine state code
        $stateCode = '27';
        if (!empty($gstin) && strlen($gstin) >= 2) {
            $stateCode = substr($gstin, 0, 2);
        } else {
            foreach (INDIAN_STATES as $code => $stName) {
                if (strcasecmp($stName, $state) === 0) {
                    $stateCode = $code;
                    break;
                }
            }
        }

        $customerId = $this->customerModel->create([
            'company_id' => $companyId,
            'name' => $name,
            'contact_person' => $contactPerson ?: null,
            'mobile' => $mobile ?: null,
            'alternate_mobile' => $alternateMobile ?: null,
            'email' => $email ?: null,
            'billing_address' => $billingAddress ?: null,
            'city' => $city ?: null,
            'district' => $district ?: null,
            'state' => $state,
            'state_code' => $stateCode,
            'pin_code' => $pinCode ?: null,
            'gstin' => $gstin ?: null,
            'pan' => $pan ?: null,
            'credit_limit' => $creditLimit,
            'credit_days' => $creditDays,
            'opening_balance' => $openingBalance,
            'balance_type' => $balanceType,
            'notes' => $notes ?: null,
            'status' => 'active'
        ]);

        // Auto-create ledger account for customer
        AccountingEngine::getCustomerAccountId($companyId, $customerId, $name);

        AuditLog::log('Customer Created', 'Customers', $customerId);
        $this->flash('success', "Customer [{$name}] added successfully.");
        $this->redirect('/customers');
    }

    public function quickStore(): void
    {
        $token = $this->request->input('_token');
        if (!Security::validateCsrfToken($token)) {
            $this->jsonError('Invalid or expired security token. Please refresh the page.');
            return;
        }

        $companyId = Session::getCompanyId();
        $name = trim($this->request->input('name', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $state = $this->request->input('state', Session::get('company_state', 'Maharashtra'));
        $gstin = strtoupper(trim($this->request->input('gstin', '')));
        $email = trim($this->request->input('email', ''));
        $billingAddress = trim($this->request->input('billing_address') ?: $this->request->input('address', ''));

        if (empty($name)) {
            $this->jsonError('Customer Name is required.');
            return;
        }

        $stateCode = '27';
        if (!empty($gstin) && strlen($gstin) >= 2) {
            $stateCode = substr($gstin, 0, 2);
        } else {
            foreach (INDIAN_STATES as $code => $stName) {
                if (strcasecmp($stName, $state) === 0) {
                    $stateCode = $code;
                    break;
                }
            }
        }

        try {
            $customerId = $this->customerModel->create([
                'company_id' => $companyId,
                'name' => $name,
                'mobile' => $mobile ?: null,
                'email' => $email ?: null,
                'billing_address' => $billingAddress ?: null,
                'state' => $state,
                'state_code' => $stateCode,
                'gstin' => $gstin ?: null,
                'credit_limit' => 0.00,
                'credit_days' => 0,
                'opening_balance' => 0.00,
                'balance_type' => 'DR',
                'status' => 'active'
            ]);

            AccountingEngine::getCustomerAccountId($companyId, $customerId, $name);
            AuditLog::log('Customer Created (Quick Shortcut)', 'Customers', $customerId);

            $customer = $this->customerModel->find($customerId);
            $customer['invoice_outstanding'] = 0.00;

            $this->jsonSuccess("Customer [{$name}] created successfully.", $customer);
        } catch (\Throwable $e) {
            error_log("Failed to quick create customer: " . $e->getMessage());
            $this->jsonError('Failed to create customer: ' . $e->getMessage());
        }
    }

    public function edit(int $id): void
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            $this->flash('danger', 'Customer not found.');
            $this->redirect('/customers');
        }

        $this->view('masters.customers.form', [
            'pageTitle' => 'Edit Customer — ' . $customer['name'],
            'customer' => $customer,
            'states' => INDIAN_STATES
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            $this->flash('danger', 'Customer not found.');
            $this->redirect('/customers');
        }

        $name = trim($this->request->input('name', ''));
        $contactPerson = trim($this->request->input('contact_person', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $alternateMobile = trim($this->request->input('alternate_mobile', ''));
        $email = trim($this->request->input('email', ''));
        $billingAddress = trim($this->request->input('billing_address') ?: $this->request->input('address', ''));
        $city = trim($this->request->input('city', ''));
        $district = trim($this->request->input('district', ''));
        $state = $this->request->input('state', 'Maharashtra');
        $pinCode = trim($this->request->input('pin_code', ''));
        $gstin = strtoupper(trim($this->request->input('gstin', '')));
        $pan = strtoupper(trim($this->request->input('pan', '')));
        $creditLimit = (float)$this->request->input('credit_limit', 0.00);
        $creditDays = (int)$this->request->input('credit_days', 0);
        $notes = trim($this->request->input('notes', ''));

        $stateCode = '27';
        if (!empty($gstin) && strlen($gstin) >= 2) {
            $stateCode = substr($gstin, 0, 2);
        } else {
            foreach (INDIAN_STATES as $code => $stName) {
                if (strcasecmp($stName, $state) === 0) {
                    $stateCode = $code;
                    break;
                }
            }
        }

        $this->customerModel->update($id, [
            'name' => $name,
            'contact_person' => $contactPerson ?: null,
            'mobile' => $mobile ?: null,
            'alternate_mobile' => $alternateMobile ?: null,
            'email' => $email ?: null,
            'billing_address' => $billingAddress ?: null,
            'city' => $city ?: null,
            'district' => $district ?: null,
            'state' => $state,
            'state_code' => $stateCode,
            'pin_code' => $pinCode ?: null,
            'gstin' => $gstin ?: null,
            'pan' => $pan ?: null,
            'credit_limit' => $creditLimit,
            'credit_days' => $creditDays,
            'notes' => $notes ?: null
        ]);

        AuditLog::log('Customer Updated', 'Customers', $id);
        $this->flash('success', "Customer [{$name}] updated successfully.");
        $this->redirect('/customers');
    }

    public function delete(int $id): void
    {
        $customer = $this->customerModel->find($id);
        if ($customer) {
            $this->customerModel->delete($id);
            AuditLog::log('Customer Deleted', 'Customers', $id);
            $this->flash('success', "Customer [{$customer['name']}] deleted.");
        }
        $this->redirect('/customers');
    }

    public function ledger(int $id): void
    {
        $companyId = Session::getCompanyId();
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            $this->flash('danger', 'Customer not found.');
            $this->redirect('/customers');
        }

        $db = Database::getInstance();
        $accountName = "Customer - " . $customer['name'];
        $acc = $db->fetchOne("SELECT id FROM `accounts` WHERE `company_id` = :comp_id AND `account_name` = :name", [
            'comp_id' => $companyId,
            'name' => $accountName
        ]);

        $ledgerEntries = [];
        if ($acc) {
            $ledgerEntries = $db->fetchAll(
                "SELECT * FROM `ledger_entries` 
                 WHERE `company_id` = :comp_id AND `account_id` = :acc_id AND `is_cancelled` = 0 
                 ORDER BY `voucher_date` ASC, `id` ASC",
                ['comp_id' => $companyId, 'acc_id' => $acc['id']]
            );
        }

        // Also fetch sales invoices
        $sales = $db->fetchAll("SELECT * FROM `sales` WHERE `company_id` = :comp_id AND `customer_id` = :cid ORDER BY `invoice_date` DESC", [
            'comp_id' => $companyId,
            'cid' => $id
        ]);

        $this->view('masters.customers.ledger', [
            'pageTitle' => 'Statement for ' . $customer['name'],
            'customer' => $customer,
            'ledgerEntries' => $ledgerEntries,
            'sales' => $sales
        ]);
    }
}
