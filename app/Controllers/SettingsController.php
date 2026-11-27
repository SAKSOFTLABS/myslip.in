<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Company;
use App\Models\InvoiceSetting;
use App\Models\User;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    private Company $companyModel;
    private InvoiceSetting $invoiceSettingModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->companyModel = new Company();
        $this->invoiceSettingModel = new InvoiceSetting();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->company();
    }

    public function company(): void
    {
        $companyId = Session::getCompanyId();
        $company = $this->companyModel->find($companyId);

        $this->view('settings.company', [
            'pageTitle' => 'Company Profile & Settings',
            'company' => $company,
            'states' => INDIAN_STATES
        ]);
    }

    public function updateCompany(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();

        $companyName = trim($this->request->input('company_name', ''));
        $businessName = trim($this->request->input('business_name', ''));
        $rawGst = $this->request->input('gst_enabled');
        $gstEnabled = ($rawGst === '1' || $rawGst === 1 || $rawGst === 'on' || $rawGst === true) ? 1 : 0;
        $gstin = $gstEnabled ? strtoupper(trim($this->request->input('gstin', ''))) : '';
        $pan = strtoupper(trim($this->request->input('pan', '')));
        $state = $this->request->input('state', 'Maharashtra');
        $addressLine1 = trim($this->request->input('address_line1', ''));
        $addressLine2 = trim($this->request->input('address_line2', ''));
        $city = trim($this->request->input('city', ''));
        $pinCode = trim($this->request->input('pin_code', ''));
        $mobile = trim($this->request->input('mobile', ''));
        $email = trim($this->request->input('email', ''));
        $bankName = trim($this->request->input('bank_name', ''));
        $bankAccountNo = trim($this->request->input('bank_account_no', ''));
        $bankIfsc = strtoupper(trim($this->request->input('bank_ifsc', '')));
        $bankBranch = trim($this->request->input('bank_branch', ''));
        $upiId = trim($this->request->input('upi_id', ''));

        if (empty($companyName)) {
            $this->flash('danger', 'Company Name is required.');
            $this->redirect('/settings/company');
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

        $this->companyModel->update($companyId, [
            'company_name' => $companyName,
            'business_name' => $businessName ?: null,
            'gst_enabled' => $gstEnabled,
            'gstin' => $gstin ?: null,
            'pan' => $pan ?: null,
            'state' => $state,
            'state_code' => $stateCode,
            'address_line1' => $addressLine1 ?: null,
            'address_line2' => $addressLine2 ?: null,
            'city' => $city ?: null,
            'pin_code' => $pinCode ?: null,
            'mobile' => $mobile,
            'email' => $email ?: null,
            'bank_name' => $bankName ?: null,
            'bank_account_no' => $bankAccountNo ?: null,
            'bank_ifsc' => $bankIfsc ?: null,
            'bank_branch' => $bankBranch ?: null,
            'upi_id' => $upiId ?: null
        ]);

        // Refresh tenant context in session
        Session::set('company_name', $companyName);
        Session::set('company_state', $state);
        Session::set('company_state_code', $stateCode);
        Session::set('gst_enabled', (bool)$gstEnabled);

        AuditLog::log('Company Profile Updated', 'Settings', $companyId);
        $this->flash('success', 'Company profile and tax configurations saved successfully.');
        $this->redirect('/settings/company');
    }

    public function invoice(): void
    {
        $companyId = Session::getCompanyId();
        $settings = $this->invoiceSettingModel->getSettingsForCompany($companyId);

        $this->view('settings.invoice', [
            'pageTitle' => 'Invoice Customization & Print Settings',
            'settings' => $settings
        ]);
    }

    public function updateInvoice(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();

        $defaultFormat = $this->request->input('default_format', 'a4');
        $primaryColor = $this->request->input('primary_color', '#1e3a8a');
        $terms = trim($this->request->input('terms_conditions', ''));
        $declaration = trim($this->request->input('declaration', ''));
        $signatureLabel = trim($this->request->input('signature_label', 'Authorized Signatory'));
        $thermalFooter = trim($this->request->input('thermal_footer_note', ''));
        $showBank = (int)$this->request->input('show_bank_details', 0);
        $showUpi = (int)$this->request->input('show_upi_qr', 0);

        $db = Database::getInstance();
        $exists = $db->fetchOne("SELECT id FROM `invoice_settings` WHERE `company_id` = :comp_id", ['comp_id' => $companyId]);

        if ($exists) {
            $this->invoiceSettingModel->update($exists['id'], [
                'default_format' => $defaultFormat,
                'primary_color' => $primaryColor,
                'terms_conditions' => $terms,
                'declaration' => $declaration,
                'signature_label' => $signatureLabel,
                'thermal_footer_note' => $thermalFooter,
                'show_bank_details' => $showBank,
                'show_upi_qr' => $showUpi
            ]);
        } else {
            $this->invoiceSettingModel->create([
                'company_id' => $companyId,
                'default_format' => $defaultFormat,
                'primary_color' => $primaryColor,
                'terms_conditions' => $terms,
                'declaration' => $declaration,
                'signature_label' => $signatureLabel,
                'thermal_footer_note' => $thermalFooter,
                'show_bank_details' => $showBank,
                'show_upi_qr' => $showUpi
            ]);
        }

        AuditLog::log('Invoice Settings Updated', 'Settings', $companyId);
        $this->flash('success', 'Invoice layout and printing settings updated.');
        $this->redirect('/settings/invoice');
    }

    public function users(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $companyUsers = $db->fetchAll(
            "SELECT u.id, u.name, u.email, u.mobile, u.status, r.name as role_name, u.created_at 
             FROM `company_users` cu 
             JOIN `users` u ON cu.user_id = u.id 
             JOIN `roles` r ON cu.role_id = r.id 
             WHERE cu.company_id = :comp_id",
            ['comp_id' => $companyId]
        );

        $roles = $db->fetchAll("SELECT * FROM `roles` WHERE `is_system` = 1 ORDER BY `id` ASC");

        $this->view('settings.users', [
            'pageTitle' => 'Team & Staff Users',
            'users' => $companyUsers,
            'roles' => $roles
        ]);
    }
}
