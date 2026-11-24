<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Vendor;
use App\Models\AuditLog;
use App\Services\AccountingEngine;

class VendorController extends Controller
{
    private Vendor $vendorModel;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel = new Vendor();
    }

    public function index(): void
    {
        $vendors = $this->vendorModel->getVendorsWithOutstanding();
        $this->view('masters.vendors.index', [
            'pageTitle' => 'Vendor Directory',
            'vendors' => $vendors
        ]);
    }

    public function create(): void
    {
        $this->view('masters.vendors.form', [
            'pageTitle' => 'Add New Vendor',
            'vendor' => null,
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
        $address = trim($this->request->input('address', ''));
        $city = trim($this->request->input('city', ''));
        $district = trim($this->request->input('district', ''));
        $state = $this->request->input('state', Session::get('company_state', 'Maharashtra'));
        $pinCode = trim($this->request->input('pin_code', ''));
        $gstin = strtoupper(trim($this->request->input('gstin', '')));
        $pan = strtoupper(trim($this->request->input('pan', '')));
        $creditLimit = (float)$this->request->input('credit_limit', 0.00);
        $creditDays = (int)$this->request->input('credit_days', 0);
        $openingBalance = (float)$this->request->input('opening_balance', 0.00);
        $balanceType = $this->request->input('balance_type', 'CR');
        $notes = trim($this->request->input('notes', ''));

        if (empty($name)) {
            $this->flash('danger', 'Vendor Name is required.');
            $this->redirect('/vendors/create');
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

        $vendorId = $this->vendorModel->create([
            'company_id' => $companyId,
            'name' => $name,
            'contact_person' => $contactPerson ?: null,
            'mobile' => $mobile ?: null,
            'alternate_mobile' => $alternateMobile ?: null,
            'email' => $email ?: null,
            'address' => $address ?: null,
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

        AccountingEngine::getVendorAccountId($companyId, $vendorId, $name);
        AuditLog::log('Vendor Created', 'Vendors', $vendorId);

        $this->flash('success', "Vendor [{$name}] added successfully.");
        $this->redirect('/vendors');
    }

    public function edit(int $id): void
    {
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            $this->flash('danger', 'Vendor not found.');
            $this->redirect('/vendors');
        }

        $this->view('masters.vendors.form', [
            'pageTitle' => 'Edit Vendor — ' . $vendor['name'],
            'vendor' => $vendor,
            'states' => INDIAN_STATES
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            $this->flash('danger', 'Vendor not found.');
            $this->redirect('/vendors');
        }

        $name = trim($this->request->input('name', ''));
        $contactPerson = trim($this->request->input('contact_person', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $alternateMobile = trim($this->request->input('alternate_mobile', ''));
        $email = trim($this->request->input('email', ''));
        $address = trim($this->request->input('address', ''));
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

        $this->vendorModel->update($id, [
            'name' => $name,
            'contact_person' => $contactPerson ?: null,
            'mobile' => $mobile ?: null,
            'alternate_mobile' => $alternateMobile ?: null,
            'email' => $email ?: null,
            'address' => $address ?: null,
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

        AuditLog::log('Vendor Updated', 'Vendors', $id);
        $this->flash('success', "Vendor [{$name}] updated successfully.");
        $this->redirect('/vendors');
    }

    public function delete(int $id): void
    {
        $vendor = $this->vendorModel->find($id);
        if ($vendor) {
            $this->vendorModel->delete($id);
            AuditLog::log('Vendor Deleted', 'Vendors', $id);
            $this->flash('success', "Vendor [{$vendor['name']}] deleted.");
        }
        $this->redirect('/vendors');
    }

    public function ledger(int $id): void
    {
        $companyId = Session::getCompanyId();
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            $this->flash('danger', 'Vendor not found.');
            $this->redirect('/vendors');
        }

        $db = Database::getInstance();
        $accountName = "Vendor - " . $vendor['name'];
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

        $this->view('masters.vendors.ledger', [
            'pageTitle' => 'Statement for ' . $vendor['name'],
            'vendor' => $vendor,
            'ledgerEntries' => $ledgerEntries
        ]);
    }
}
