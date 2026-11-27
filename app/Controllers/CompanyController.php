<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\InvoiceSetting;
use App\Models\AuditLog;
use App\Services\AccountingEngine;
use App\Services\SubscriptionEngine;

class CompanyController extends Controller
{
    private Company $companyModel;

    public function __construct()
    {
        parent::__construct();
        $this->companyModel = new Company();
    }

    public function create(): void
    {
        $this->showCreate();
    }

    public function showCreate(): void
    {
        $this->view('company.create', [
            'pageTitle' => 'Create Your Company',
            'states' => INDIAN_STATES,
            'businessTypes' => BUSINESS_TYPES
        ], 'auth');
    }

    public function store(): void
    {
        $this->validateCsrf();
        $userId = Session::getUserId();

        $companyName = trim($this->request->input('company_name', ''));
        $businessName = trim($this->request->input('business_name', '')) ?: $companyName;
        $businessType = $this->request->input('business_type', 'retail');
        $address1 = trim($this->request->input('address_line1', ''));
        $address2 = trim($this->request->input('address_line2', ''));
        $city = trim($this->request->input('city', ''));
        $district = trim($this->request->input('district', ''));
        $state = $this->request->input('state', 'Maharashtra');
        $pinCode = trim($this->request->input('pin_code', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $alternateMobile = trim($this->request->input('alternate_mobile', ''));
        $email = trim($this->request->input('email', ''));
        $website = trim($this->request->input('website', ''));
        
        $rawGst = $this->request->input('gst_enabled');
        $gstEnabled = ($rawGst === '1' || $rawGst === 1 || $rawGst === 'on' || $rawGst === true) ? 1 : 0;
        $gstin = $gstEnabled ? strtoupper(trim($this->request->input('gstin', ''))) : '';
        $pan = strtoupper(trim($this->request->input('pan', '')));
        $cin = trim($this->request->input('cin', ''));

        $bankName = trim($this->request->input('bank_name', ''));
        $bankAccountNo = trim($this->request->input('bank_account_no', ''));
        $bankIfsc = strtoupper(trim($this->request->input('bank_ifsc', '')));
        $bankBranch = trim($this->request->input('bank_branch', ''));
        $upiId = trim($this->request->input('upi_id', ''));
        $invoicePrefix = strtoupper(trim($this->request->input('invoice_prefix', 'INV')));

        if (empty($companyName) || empty($mobile)) {
            $this->flash('danger', 'Company Name and Mobile Number are required.');
            $this->redirect('/company/create');
        }

        // Determine 2-digit state code from GSTIN or state selection
        $stateCode = '27'; // Default Maharashtra
        if ($gstEnabled && !empty($gstin) && strlen($gstin) >= 2) {
            $stateCode = substr($gstin, 0, 2);
        } else {
            foreach (INDIAN_STATES as $code => $name) {
                if (strcasecmp($name, $state) === 0) {
                    $stateCode = $code;
                    break;
                }
            }
        }

        $db = Database::getInstance();

        // Transaction block to create tenant, FY, accounts, and 30-day trial
        $db->transaction(function(Database $db) use (
            $userId, $companyName, $businessName, $businessType, $address1, $address2, $city, $district,
            $state, $stateCode, $pinCode, $mobile, $alternateMobile, $email, $website, $gstEnabled, $gstin,
            $pan, $cin, $bankName, $bankAccountNo, $bankIfsc, $bankBranch, $upiId, $invoicePrefix
        ) {
            // 1. Insert Company
            $sql = "INSERT INTO `companies` 
                    (`owner_user_id`, `company_name`, `business_name`, `business_type`, `address_line1`, `address_line2`, `city`, `district`, `state`, `state_code`, `pin_code`, `country`, `mobile`, `alternate_mobile`, `email`, `website`, `gst_enabled`, `gstin`, `pan`, `cin`, `bank_name`, `bank_account_no`, `bank_ifsc`, `bank_branch`, `upi_id`, `invoice_prefix`, `status`) 
                    VALUES 
                    (:owner, :cname, :bname, :btype, :addr1, :addr2, :city, :dist, :state, :scode, :pincode, 'India', :mobile, :altmobile, :email, :website, :gstenable, :gstin, :pan, :cin, :bname_b, :acc_no, :ifsc, :branch, :upi, :prefix, 'trial')";
            
            $db->execute($sql, [
                'owner' => $userId,
                'cname' => $companyName,
                'bname' => $businessName,
                'btype' => $businessType,
                'addr1' => $address1,
                'addr2' => $address2,
                'city' => $city,
                'dist' => $district,
                'state' => $state,
                'scode' => $stateCode,
                'pincode' => $pinCode,
                'mobile' => $mobile,
                'altmobile' => $alternateMobile,
                'email' => $email,
                'website' => $website,
                'gstenable' => $gstEnabled,
                'gstin' => $gstin,
                'pan' => $pan,
                'cin' => $cin,
                'bname_b' => $bankName,
                'acc_no' => $bankAccountNo,
                'ifsc' => $bankIfsc,
                'branch' => $bankBranch,
                'upi' => $upiId,
                'prefix' => $invoicePrefix
            ]);

            $companyId = $db->lastInsertId();

            // 2. Insert Default Financial Year (Current FY: 2026-2027)
            $db->execute(
                "INSERT INTO `financial_years` (`company_id`, `fy_name`, `start_date`, `end_date`, `is_current`) 
                 VALUES (:comp_id, '2026-2027', '2026-04-01', '2027-03-31', 1)",
                ['comp_id' => $companyId]
            );
            $fyId = $db->lastInsertId();

            // 3. Bind User as Company Admin (Role 1)
            $db->execute(
                "INSERT INTO `company_users` (`company_id`, `user_id`, `role_id`, `status`) 
                 VALUES (:comp_id, :user_id, 1, 'active')",
                ['comp_id' => $companyId, 'user_id' => $userId]
            );

            // 4. Start 30-Day Free Trial
            SubscriptionEngine::startTrial($companyId, 30);

            // 5. Seed Default Chart of Accounts
            AccountingEngine::seedDefaultAccountsForCompany($companyId);

            // 6. Seed Default Invoice Settings
            $db->execute(
                "INSERT INTO `invoice_settings` (`company_id`, `default_format`, `show_logo`, `show_bank_details`, `show_upi_qr`, `show_terms`, `terms_conditions`, `declaration`, `signature_label`, `primary_color`) 
                 VALUES (:comp_id, 'a4', 1, 1, 1, 1, '1. Goods once sold will not be taken back.\n2. Payment due within invoice credit period.\n3. Subject to local jurisdiction.', 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.', 'Authorized Signatory', '#1e3a8a')",
                ['comp_id' => $companyId]
            );

            // 7. Initialize Numbering Series for Sales and Purchases
            $salePrefix = $invoicePrefix !== '' ? $invoicePrefix : 'INV';
            $defaultSeries = [
                ['voucher_type' => 'sale', 'prefix' => $salePrefix],
                ['voucher_type' => 'purchase', 'prefix' => 'PUR'],
                ['voucher_type' => 'sales_return', 'prefix' => 'SR'],
                ['voucher_type' => 'purchase_return', 'prefix' => 'PR'],
                ['voucher_type' => 'receipt', 'prefix' => 'REC'],
                ['voucher_type' => 'payment', 'prefix' => 'PAY'],
                ['voucher_type' => 'journal', 'prefix' => 'JRN']
            ];

            foreach ($defaultSeries as $ser) {
                $db->execute(
                    "INSERT INTO `numbering_series` (`company_id`, `financial_year_id`, `voucher_type`, `prefix`, `next_number`, `zero_pad`) 
                     VALUES (:comp_id, :fy_id, :vtype, :prefix, 1, 5)",
                    [
                        'comp_id' => $companyId,
                        'fy_id' => $fyId,
                        'vtype' => $ser['voucher_type'],
                        'prefix' => $ser['prefix']
                    ]
                );
            }

            // Fetch company object and set active tenant in session
            $company = $db->fetchOne("SELECT * FROM `companies` WHERE id = :id", ['id' => $companyId]);
            $fy = $db->fetchOne("SELECT * FROM `financial_years` WHERE id = :id", ['id' => $fyId]);
            Session::setTenant($company, $fy, 1);

            AuditLog::log('Company Created', 'Company', $companyId);
        });

        $this->flash('success', "Company [{$companyName}] created successfully! Your 30-Day Free Trial is now active.");
        $this->redirect('/dashboard');
    }

    public function switchCompany(): void
    {
        $userId = Session::getUserId();
        $companies = $this->companyModel->getUserCompanies($userId);

        $this->view('company.switch', [
            'pageTitle' => 'Switch Company',
            'companies' => $companies
        ]);
    }

    public function selectCompany(int $companyId): void
    {
        $userId = Session::getUserId();
        $db = Database::getInstance();

        $sql = "SELECT c.*, cu.role_id 
                FROM `companies` c
                JOIN `company_users` cu ON c.id = cu.company_id
                WHERE c.id = :comp_id AND cu.user_id = :user_id AND cu.status = 'active'
                LIMIT 1";
        
        $company = $db->fetchOne($sql, ['comp_id' => $companyId, 'user_id' => $userId]);

        if (!$company) {
            $this->flash('danger', 'Access denied to the requested company.');
            $this->redirect('/dashboard');
        }

        $fy = $db->fetchOne("SELECT * FROM `financial_years` WHERE `company_id` = :comp_id AND `is_current` = 1 LIMIT 1", [
            'comp_id' => $companyId
        ]);

        Session::setTenant($company, $fy, (int)$company['role_id']);
        AuditLog::log('Switched Company', 'Company', $companyId);

        $this->flash('success', "Switched to [{$company['company_name']}].");
        $this->redirect('/dashboard');
    }
}
