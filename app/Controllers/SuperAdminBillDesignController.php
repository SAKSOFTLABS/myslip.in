<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\BillTemplate;
use App\Models\CompanyBillSetting;
use App\Models\CompanyBillTemplateAccess;
use App\Models\BillDesignSetting;
use App\Models\Company;
use App\Models\InvoiceSetting;
use App\Models\AuditLog;
use App\Services\BillTemplateEngine;

class SuperAdminBillDesignController extends Controller
{
    private BillTemplate $billTemplateModel;
    private CompanyBillTemplateAccess $companyAccessModel;
    private BillDesignSetting $billDesignSettingModel;
    private Company $companyModel;
    private InvoiceSetting $invoiceSettingModel;

    public function __construct()
    {
        parent::__construct();
        $this->billTemplateModel = new BillTemplate();
        $this->companyAccessModel = new CompanyBillTemplateAccess();
        $this->billDesignSettingModel = new BillDesignSetting();
        $this->companyModel = new Company();
        $this->invoiceSettingModel = new InvoiceSetting();
    }

    /**
     * Super Admin: List all Bill Designs
     */
    public function index(): void
    {
        $status = $this->request->input('status', 'all');
        $pricing = $this->request->input('pricing_type', 'all');
        $search = trim($this->request->input('search', ''));

        $sql = "SELECT bt.*, 
                       (SELECT COUNT(*) FROM `company_bill_settings` cbs WHERE cbs.active_template_id = bt.id) as active_companies_count,
                       (SELECT COUNT(*) FROM `company_bill_template_access` cbta WHERE cbta.template_id = bt.id AND cbta.access_status = 'active') as active_licenses_count
                FROM `bill_templates` bt 
                WHERE 1=1";
        $params = [];

        if ($status !== 'all' && in_array($status, ['draft', 'published', 'archived'])) {
            $sql .= " AND bt.`status` = :st";
            $params['st'] = $status;
        }

        if ($pricing !== 'all' && in_array($pricing, ['free', 'paid'])) {
            $sql .= " AND bt.`pricing_type` = :pt";
            $params['pt'] = $pricing;
        }

        if ($search !== '') {
            $sql .= " AND (bt.`name` LIKE :search OR bt.`template_key` LIKE :search OR bt.`category` LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY bt.`display_order` ASC, bt.`id` DESC";

        $db = \App\Core\Database::getInstance();
        $templates = $db->fetchAll($sql, $params);

        $this->view('superadmin.bill_designs.index', [
            'pageTitle' => 'Manage Bill Designs',
            'templates' => $templates,
            'status' => $status,
            'pricing' => $pricing,
            'search' => $search
        ], 'superadmin');
    }

    /**
     * Super Admin: Add New Bill Design Form
     */
    public function create(): void
    {
        $this->view('superadmin.bill_designs.form', [
            'pageTitle' => 'Add New Bill Design',
            'template' => null
        ], 'superadmin');
    }

    /**
     * Super Admin: Store New Bill Design
     */
    public function store(): void
    {
        $this->validateCsrf();
        $userId = Session::getUserId();

        $name = trim($this->request->input('name', ''));
        $key = strtolower(trim($this->request->input('template_key', '')));
        $key = preg_replace('/[^a-z0-9_-]/', '-', $key);
        $description = trim($this->request->input('description', ''));
        $category = trim($this->request->input('category', 'GST Invoice'));
        $paperSize = trim($this->request->input('paper_size', 'A4'));
        $pricingType = $this->request->input('pricing_type', 'free');
        $price = (float)$this->request->input('price', 0.00);
        $currency = trim($this->request->input('currency', 'INR'));
        $isFeatured = (int)$this->request->input('is_featured', 0);
        $displayOrder = (int)$this->request->input('display_order', 0);
        $status = $this->request->input('status', 'published');
        $primaryColor = trim($this->request->input('primary_color', '#1e3a8a'));
        $accentColor = trim($this->request->input('accent_color', '#3b82f6'));

        if (empty($name) || empty($key)) {
            $this->flash('danger', 'Design name and unique template key are required.');
            $this->redirect('/admin/bill-designs/create');
            return;
        }

        if ($this->billTemplateModel->findByKey($key)) {
            $this->flash('danger', "Template key [{$key}] already exists. Please choose a unique key.");
            $this->redirect('/admin/bill-designs/create');
            return;
        }

        // Preview Image handling
        $previewImage = trim($this->request->input('preview_image_url', ''));
        if (!empty($_FILES['preview_image_file']['tmp_name'])) {
            $uploadedPath = $this->handleImageUpload($_FILES['preview_image_file'], $key);
            if ($uploadedPath) {
                $previewImage = $uploadedPath;
            }
        }

        if (empty($previewImage)) {
            $previewImage = '/assets/images/templates/classic-gst.svg';
        }

        $config = json_encode([
            'primary_color' => $primaryColor,
            'accent_color' => $accentColor,
            'font_family' => 'Inter, sans-serif',
            'show_hsn' => ($category === 'GST Invoice' || $category === 'Wholesale Invoice'),
            'show_qr' => true
        ]);

        $id = $this->billTemplateModel->create([
            'name' => $name,
            'template_key' => $key,
            'description' => $description,
            'category' => $category,
            'paper_size' => $paperSize,
            'preview_image' => $previewImage,
            'template_config' => $config,
            'version' => '1.0.0',
            'pricing_type' => $pricingType,
            'price' => ($pricingType === 'paid') ? $price : 0.00,
            'currency' => $currency,
            'is_featured' => $isFeatured,
            'display_order' => $displayOrder,
            'status' => $status,
            'created_by' => $userId
        ]);

        AuditLog::log("SuperAdmin created Bill Design [{$name}] (Key: {$key})", 'BillDesigns', $id);
        $this->flash('success', "Bill design [{$name}] created successfully.");
        $this->redirect('/admin/bill-designs');
    }

    /**
     * Super Admin: Edit Form
     */
    public function edit(int $id): void
    {
        $template = $this->billTemplateModel->find($id);
        if (!$template) {
            $this->flash('danger', 'Bill design template not found.');
            $this->redirect('/admin/bill-designs');
            return;
        }

        $this->view('superadmin.bill_designs.form', [
            'pageTitle' => 'Edit Bill Design — ' . $template['name'],
            'template' => $template
        ], 'superadmin');
    }

    /**
     * Super Admin: Update Bill Design
     */
    public function update(int $id): void
    {
        $this->validateCsrf();
        $template = $this->billTemplateModel->find($id);
        if (!$template) {
            $this->flash('danger', 'Bill design template not found.');
            $this->redirect('/admin/bill-designs');
            return;
        }

        $name = trim($this->request->input('name', $template['name']));
        $description = trim($this->request->input('description', $template['description']));
        $category = trim($this->request->input('category', $template['category']));
        $paperSize = trim($this->request->input('paper_size', $template['paper_size']));
        $pricingType = $this->request->input('pricing_type', $template['pricing_type']);
        $price = (float)$this->request->input('price', $template['price']);
        $version = trim($this->request->input('version', $template['version']));
        $isFeatured = (int)$this->request->input('is_featured', 0);
        $displayOrder = (int)$this->request->input('display_order', $template['display_order']);
        $status = $this->request->input('status', $template['status']);
        $primaryColor = trim($this->request->input('primary_color', '#1e3a8a'));
        $accentColor = trim($this->request->input('accent_color', '#3b82f6'));

        $previewImage = trim($this->request->input('preview_image_url', $template['preview_image']));
        if (!empty($_FILES['preview_image_file']['tmp_name'])) {
            $uploadedPath = $this->handleImageUpload($_FILES['preview_image_file'], $template['template_key']);
            if ($uploadedPath) {
                $previewImage = $uploadedPath;
            }
        }

        $existingConfig = json_decode($template['template_config'] ?? '', true) ?: [];
        $existingConfig['primary_color'] = $primaryColor;
        $existingConfig['accent_color'] = $accentColor;

        $this->billTemplateModel->update($id, [
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'paper_size' => $paperSize,
            'preview_image' => $previewImage,
            'template_config' => json_encode($existingConfig),
            'version' => $version,
            'pricing_type' => $pricingType,
            'price' => ($pricingType === 'paid') ? $price : 0.00,
            'is_featured' => $isFeatured,
            'display_order' => $displayOrder,
            'status' => $status
        ]);

        AuditLog::log("SuperAdmin updated Bill Design [{$name}] (ID: {$id})", 'BillDesigns', $id);
        $this->flash('success', "Bill design [{$name}] updated successfully.");
        $this->redirect('/admin/bill-designs');
    }

    /**
     * Publish Design
     */
    public function publish(int $id): void
    {
        $this->validateCsrf();
        $this->billTemplateModel->update($id, ['status' => 'published']);
        $this->flash('success', 'Bill design published successfully.');
        $this->redirect('/admin/bill-designs');
    }

    /**
     * Unpublish Design
     */
    public function unpublish(int $id): void
    {
        $this->validateCsrf();
        $this->billTemplateModel->update($id, ['status' => 'draft']);
        $this->flash('warning', 'Bill design unpublished (moved to draft).');
        $this->redirect('/admin/bill-designs');
    }

    /**
     * Archive Design
     */
    public function archive(int $id): void
    {
        $this->validateCsrf();
        $this->billTemplateModel->update($id, ['status' => 'archived']);
        $this->flash('info', 'Bill design archived.');
        $this->redirect('/admin/bill-designs');
    }

    /**
     * Duplicate Design
     */
    public function duplicate(int $id): void
    {
        $this->validateCsrf();
        $userId = Session::getUserId();
        $newId = $this->billTemplateModel->duplicate($id, $userId);

        if ($newId) {
            $this->flash('success', 'Bill design duplicated successfully as Draft.');
            $this->redirect('/admin/bill-designs/edit/' . $newId);
        } else {
            $this->flash('danger', 'Failed to duplicate bill design.');
            $this->redirect('/admin/bill-designs');
        }
    }

    /**
     * Super Admin Preview
     */
    public function preview(int $id): void
    {
        $template = $this->billTemplateModel->find($id);
        if (!$template) {
            $this->flash('danger', 'Template not found.');
            $this->redirect('/admin/bill-designs');
            return;
        }

        $sampleCompany = [
            'company_name' => 'SAK ENTERPRISES & TRADERS',
            'address_line1' => 'Plot 42, Technology Park, Phase 1',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'pin_code' => '400001',
            'mobile' => '9876543210',
            'email' => 'contact@sakenterprises.in',
            'gstin' => '27AAACS1234A1Z5',
            'pan' => 'AAACS1234A',
            'gst_enabled' => 1,
            'bank_name' => 'HDFC Bank Ltd',
            'bank_account_no' => '50200012345678',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Fort Corporate',
            'upi_id' => 'sakenterprises@hdfcbank'
        ];

        $sampleSale = BillTemplateEngine::getSampleInvoiceData($sampleCompany);
        $settings = [
            'primary_color' => '#1e3a8a',
            'show_bank_details' => 1,
            'show_upi_qr' => 1,
            'terms_conditions' => "1. Goods once sold will not be accepted back.\n2. Subject to local jurisdiction only.",
            'declaration' => "We certify that the particulars furnished above are true and correct.",
            'signature_label' => 'Authorized Signatory'
        ];

        if (!empty($template['template_config'])) {
            $tplConfig = json_decode($template['template_config'], true) ?: [];
            $settings = array_merge($settings, $tplConfig);
        }

        $templateView = BillTemplateEngine::getViewPath($template['template_key']);

        $this->view('superadmin.bill_designs.preview', [
            'pageTitle' => 'Admin Preview — ' . $template['name'],
            'template' => $template,
            'sampleSale' => $sampleSale,
            'sampleCompany' => $sampleCompany,
            'settings' => $settings,
            'templateView' => $templateView
        ], 'superadmin');
    }

    /**
     * Super Admin: Manage Company Entitlements
     */
    public function entitlements(): void
    {
        $companyId = (int)$this->request->input('company_id', 0);
        $templateId = (int)$this->request->input('template_id', 0);
        $status = $this->request->input('status', 'all');

        $entitlements = $this->companyAccessModel->getEntitlementsList([
            'company_id' => $companyId ?: null,
            'template_id' => $templateId ?: null,
            'status' => ($status !== 'all') ? $status : null
        ]);

        $companies = $this->companyModel->all([], 'company_name ASC');
        $paidTemplates = $this->billTemplateModel->all(['pricing_type' => 'paid'], 'name ASC');

        $this->view('superadmin.bill_designs.entitlements', [
            'pageTitle' => 'Company Bill Design Entitlements',
            'entitlements' => $entitlements,
            'companies' => $companies,
            'paidTemplates' => $paidTemplates,
            'filterCompanyId' => $companyId,
            'filterTemplateId' => $templateId,
            'filterStatus' => $status
        ], 'superadmin');
    }

    /**
     * Super Admin: Grant Access to Company
     */
    public function grantAccess(): void
    {
        $this->validateCsrf();
        $adminId = Session::getUserId();

        $companyId = (int)$this->request->input('company_id');
        $templateId = (int)$this->request->input('template_id');
        $notes = trim($this->request->input('notes', 'Access granted via WhatsApp request'));

        if ($companyId <= 0 || $templateId <= 0) {
            $this->flash('danger', 'Please select a company and a paid bill design.');
            $this->redirect('/admin/bill-designs/entitlements');
            return;
        }

        $company = $this->companyModel->find($companyId);
        $template = $this->billTemplateModel->find($templateId);

        if (!$company || !$template) {
            $this->flash('danger', 'Invalid company or template selected.');
            $this->redirect('/admin/bill-designs/entitlements');
            return;
        }

        $this->companyAccessModel->grantAccess($companyId, $templateId, $adminId, $notes);
        AuditLog::log("SuperAdmin granted access to [{$template['name']}] for company [{$company['company_name']}]", 'BillDesigns', $companyId);

        $this->flash('success', "Access to [{$template['name']}] has been granted for [{$company['company_name']}]. The company can now activate this design.");
        $this->redirect('/admin/bill-designs/entitlements');
    }

    /**
     * Super Admin: Revoke Access from Company
     */
    public function revokeAccess(int $id): void
    {
        $this->validateCsrf();
        $db = \App\Core\Database::getInstance();
        $record = $db->fetchOne("SELECT * FROM `company_bill_template_access` WHERE id = :id", ['id' => $id]);

        if ($record) {
            $this->companyAccessModel->revokeAccess((int)$record['company_id'], (int)$record['template_id']);
            AuditLog::log("SuperAdmin revoked design access ID: {$id}", 'BillDesigns', (int)$record['company_id']);
            $this->flash('warning', 'Design access entitlement has been revoked.');
        }

        $this->redirect('/admin/bill-designs/entitlements');
    }

    /**
     * Super Admin: WhatsApp Settings Form
     */
    public function whatsappSettings(): void
    {
        $settings = $this->billDesignSettingModel->getSettings();

        $this->view('superadmin.bill_designs.settings', [
            'pageTitle' => 'WhatsApp Contact Settings',
            'settings' => $settings
        ], 'superadmin');
    }

    /**
     * Super Admin: Save WhatsApp Settings
     */
    public function updateWhatsappSettings(): void
    {
        $this->validateCsrf();
        $adminId = Session::getUserId();

        $whatsappNumber = trim($this->request->input('whatsapp_number', ''));
        $defaultMessage = trim($this->request->input('default_enquiry_message', ''));
        $businessName = trim($this->request->input('business_name', 'MYSLIP.IN'));
        $supportEmail = trim($this->request->input('support_email', 'support@myslip.in'));

        if (empty($whatsappNumber)) {
            $this->flash('danger', 'WhatsApp contact number is required.');
            $this->redirect('/admin/bill-designs/settings');
            return;
        }

        $this->billDesignSettingModel->updateSettings([
            'whatsapp_number' => $whatsappNumber,
            'default_enquiry_message' => $defaultMessage,
            'business_name' => $businessName,
            'support_email' => $supportEmail
        ], $adminId);

        AuditLog::log('SuperAdmin updated WhatsApp Contact Settings', 'SuperAdmin', $adminId);
        $this->flash('success', 'WhatsApp enquiry settings saved successfully.');
        $this->redirect('/admin/bill-designs/settings');
    }

    /**
     * Secure image upload handler
     */
    private function handleImageUpload(array $file, string $key): ?string
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes)) {
            return null;
        }

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'png'
        };

        $dir = dirname(__DIR__, 2) . '/public/assets/images/templates';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $key . '_' . time() . '.' . $ext;
        $dest = $dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/assets/images/templates/' . $filename;
        }

        return null;
    }
}
