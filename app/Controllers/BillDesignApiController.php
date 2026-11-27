<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\BillTemplate;
use App\Models\CompanyBillSetting;
use App\Models\CompanyBillTemplateAccess;
use App\Models\BillDesignSetting;
use App\Models\Company;
use App\Models\AuditLog;
use App\Services\BillTemplateEngine;

class BillDesignApiController extends Controller
{
    private BillTemplate $billTemplateModel;
    private CompanyBillSetting $companyBillSettingModel;
    private CompanyBillTemplateAccess $companyAccessModel;
    private BillDesignSetting $billDesignSettingModel;
    private Company $companyModel;

    public function __construct()
    {
        parent::__construct();
        $this->billTemplateModel = new BillTemplate();
        $this->companyBillSettingModel = new CompanyBillSetting();
        $this->companyAccessModel = new CompanyBillTemplateAccess();
        $this->billDesignSettingModel = new BillDesignSetting();
        $this->companyModel = new Company();
    }

    // ==========================================
    // USER APIS (Tenant Scoped)
    // ==========================================

    /**
     * GET /api/bill-designs
     */
    public function index(): void
    {
        $filters = [
            'pricing_type' => $this->request->input('pricing_type'),
            'category' => $this->request->input('category'),
            'paper_size' => $this->request->input('paper_size'),
            'search' => $this->request->input('search')
        ];
        $sort = $this->request->input('sort', 'display_order ASC');

        $templates = $this->billTemplateModel->getPublished($filters, $sort);
        $this->jsonSuccess('Bill designs retrieved successfully', ['templates' => $templates]);
    }

    /**
     * GET /api/bill-designs/{id}
     */
    public function show(int $id): void
    {
        $template = $this->billTemplateModel->find($id);
        if (!$template || $template['status'] === 'archived') {
            $this->jsonError('Bill design not found', [], 404);
            return;
        }

        $companyId = Session::getCompanyId();
        $hasAccess = ($template['pricing_type'] === 'free') || ($companyId && $this->companyAccessModel->hasAccess($companyId, $id));

        $this->jsonSuccess('Bill design details', [
            'template' => $template,
            'has_access' => $hasAccess
        ]);
    }

    /**
     * GET /api/bill-designs/{id}/preview
     */
    public function previewData(int $id): void
    {
        $template = $this->billTemplateModel->find($id);
        if (!$template || $template['status'] === 'archived') {
            $this->jsonError('Bill design not found', [], 404);
            return;
        }

        $companyId = Session::getCompanyId();
        $company = $companyId ? $this->companyModel->find($companyId) : null;
        $sampleData = BillTemplateEngine::getSampleInvoiceData($company);

        $this->jsonSuccess('Preview sample data', [
            'template' => $template,
            'company' => $company,
            'sample_invoice' => $sampleData
        ]);
    }

    /**
     * POST /api/bill-designs/{id}/activate
     */
    public function activate(int $id): void
    {
        $companyId = Session::getCompanyId();
        if (!$companyId) {
            $this->jsonError('No active company selected', [], 403);
            return;
        }

        $template = $this->billTemplateModel->find($id);
        if (!$template || $template['status'] !== 'published') {
            $this->jsonError('Template is not available for activation', [], 400);
            return;
        }

        // Server-side entitlement check for Paid designs
        if ($template['pricing_type'] === 'paid') {
            if (!$this->companyAccessModel->hasAccess($companyId, $id)) {
                $whatsappUrl = $this->billDesignSettingModel->generateWhatsAppUrl(
                    $template['name'],
                    Session::get('company_name', 'My Business'),
                    $template['id']
                );
                $this->jsonError('This is a Paid Bill Design. Please contact us on WhatsApp to enquire and request access.', [
                    'whatsapp_url' => $whatsappUrl
                ], 403);
                return;
            }
        }

        $userId = Session::getUserId();
        $this->companyBillSettingModel->activateTemplate($companyId, $id, $template['version'], 'sales_invoice', $userId);

        $this->jsonSuccess("Design [{$template['name']}] activated successfully", [
            'active_template_id' => $id,
            'template_name' => $template['name'],
            'version' => $template['version']
        ]);
    }

    /**
     * GET /api/company/bill-design-settings
     */
    public function companySettings(): void
    {
        $companyId = Session::getCompanyId();
        if (!$companyId) {
            $this->jsonError('No active company selected', [], 403);
            return;
        }

        $setting = $this->companyBillSettingModel->getActiveTemplate($companyId, 'sales_invoice');
        $this->jsonSuccess('Active company bill design settings', ['settings' => $setting]);
    }

    /**
     * GET /api/company/available-bill-designs
     */
    public function availableDesigns(): void
    {
        $companyId = Session::getCompanyId();
        if (!$companyId) {
            $this->jsonError('No active company selected', [], 403);
            return;
        }

        $company = $this->companyModel->find($companyId);
        $companyName = $company['company_name'] ?? 'My Business';

        $templates = $this->billTemplateModel->getPublished();
        $activeSetting = $this->companyBillSettingModel->getActiveTemplate($companyId, 'sales_invoice');
        $activeId = (int)($activeSetting['active_template_id'] ?? 1);
        $entitledIds = $this->companyAccessModel->getCompanyEntitledTemplateIds($companyId);

        foreach ($templates as &$t) {
            $isPaid = $t['pricing_type'] === 'paid';
            $t['is_active'] = ((int)$t['id'] === $activeId);
            $t['has_access'] = !$isPaid || in_array((int)$t['id'], $entitledIds);
            $t['whatsapp_url'] = $this->billDesignSettingModel->generateWhatsAppUrl($t['name'], $companyName, $t['id']);
        }
        unset($t);

        $this->jsonSuccess('Available bill designs for company', [
            'active_template_id' => $activeId,
            'templates' => $templates
        ]);
    }

    // ==========================================
    // SUPER ADMIN APIS
    // ==========================================

    /**
     * POST /api/admin/bill-designs
     */
    public function adminStore(): void
    {
        $name = trim($this->request->input('name', ''));
        $key = strtolower(trim($this->request->input('template_key', '')));
        $key = preg_replace('/[^a-z0-9_-]/', '-', $key);

        if (empty($name) || empty($key)) {
            $this->jsonError('Name and unique template_key are required', [], 422);
            return;
        }

        if ($this->billTemplateModel->findByKey($key)) {
            $this->jsonError('Template key already exists', [], 422);
            return;
        }

        $id = $this->billTemplateModel->create([
            'name' => $name,
            'template_key' => $key,
            'description' => trim($this->request->input('description', '')),
            'category' => trim($this->request->input('category', 'GST Invoice')),
            'paper_size' => trim($this->request->input('paper_size', 'A4')),
            'preview_image' => trim($this->request->input('preview_image', '/assets/images/templates/classic-gst.svg')),
            'template_config' => json_encode($this->request->input('template_config', ['primary_color' => '#1e3a8a'])),
            'version' => trim($this->request->input('version', '1.0.0')),
            'pricing_type' => $this->request->input('pricing_type', 'free'),
            'price' => (float)$this->request->input('price', 0.00),
            'currency' => trim($this->request->input('currency', 'INR')),
            'is_featured' => (int)$this->request->input('is_featured', 0),
            'display_order' => (int)$this->request->input('display_order', 0),
            'status' => $this->request->input('status', 'published'),
            'created_by' => Session::getUserId()
        ]);

        AuditLog::log("SuperAdmin created Bill Design [{$name}] via API", 'BillDesigns', $id);
        $this->jsonSuccess('Bill design created successfully', ['id' => $id], 201);
    }

    /**
     * PUT /api/admin/bill-designs/{id}
     */
    public function adminUpdate(int $id): void
    {
        $template = $this->billTemplateModel->find($id);
        if (!$template) {
            $this->jsonError('Template not found', [], 404);
            return;
        }

        $data = [];
        $fields = ['name', 'description', 'category', 'paper_size', 'preview_image', 'version', 'pricing_type', 'price', 'currency', 'is_featured', 'display_order', 'status'];
        foreach ($fields as $f) {
            $val = $this->request->input($f);
            if ($val !== null) {
                $data[$f] = $val;
            }
        }

        if (!empty($data)) {
            $this->billTemplateModel->update($id, $data);
            AuditLog::log("SuperAdmin updated Bill Design ID: {$id} via API", 'BillDesigns', $id);
        }

        $this->jsonSuccess('Bill design updated successfully', ['template' => $this->billTemplateModel->find($id)]);
    }

    /**
     * POST /api/admin/bill-designs/{id}/publish
     */
    public function adminPublish(int $id): void
    {
        $this->billTemplateModel->update($id, ['status' => 'published']);
        $this->jsonSuccess('Template published');
    }

    /**
     * POST /api/admin/bill-designs/{id}/unpublish
     */
    public function adminUnpublish(int $id): void
    {
        $this->billTemplateModel->update($id, ['status' => 'draft']);
        $this->jsonSuccess('Template unpublished (moved to draft)');
    }

    /**
     * POST /api/admin/bill-designs/{id}/archive
     */
    public function adminArchive(int $id): void
    {
        $this->billTemplateModel->update($id, ['status' => 'archived']);
        $this->jsonSuccess('Template archived');
    }

    /**
     * POST /api/admin/bill-designs/{id}/duplicate
     */
    public function adminDuplicate(int $id): void
    {
        $newId = $this->billTemplateModel->duplicate($id, (int)Session::getUserId());
        if ($newId) {
            $this->jsonSuccess('Template duplicated', ['new_id' => $newId], 201);
        } else {
            $this->jsonError('Failed to duplicate template', [], 400);
        }
    }

    /**
     * POST /api/admin/company-template-access/grant
     */
    public function adminGrantAccess(): void
    {
        $companyId = (int)$this->request->input('company_id');
        $templateId = (int)$this->request->input('template_id');
        $notes = trim($this->request->input('notes', 'Granted via API'));

        if ($companyId <= 0 || $templateId <= 0) {
            $this->jsonError('company_id and template_id are required', [], 422);
            return;
        }

        $this->companyAccessModel->grantAccess($companyId, $templateId, (int)Session::getUserId(), $notes);
        AuditLog::log("SuperAdmin granted access to template {$templateId} for company {$companyId}", 'BillDesigns', $companyId);

        $this->jsonSuccess('Access granted successfully');
    }

    /**
     * POST /api/admin/company-template-access/revoke
     */
    public function adminRevokeAccess(): void
    {
        $companyId = (int)$this->request->input('company_id');
        $templateId = (int)$this->request->input('template_id');

        if ($companyId <= 0 || $templateId <= 0) {
            $this->jsonError('company_id and template_id are required', [], 422);
            return;
        }

        $this->companyAccessModel->revokeAccess($companyId, $templateId, (int)Session::getUserId());
        AuditLog::log("SuperAdmin revoked access to template {$templateId} for company {$companyId}", 'BillDesigns', $companyId);

        $this->jsonSuccess('Access revoked successfully');
    }

    /**
     * GET /api/admin/company-template-access
     */
    public function adminEntitlements(): void
    {
        $filters = [
            'company_id' => $this->request->input('company_id'),
            'template_id' => $this->request->input('template_id'),
            'status' => $this->request->input('status')
        ];
        $list = $this->companyAccessModel->getEntitlementsList($filters);
        $this->jsonSuccess('Entitlements list', ['entitlements' => $list]);
    }

    /**
     * POST /api/admin/bill-design-settings
     */
    public function adminSaveSettings(): void
    {
        $whatsappNumber = trim($this->request->input('whatsapp_number', ''));
        $defaultMessage = trim($this->request->input('default_enquiry_message', ''));
        $businessName = trim($this->request->input('business_name', 'MYSLIP.IN'));
        $supportEmail = trim($this->request->input('support_email', 'support@myslip.in'));

        if (empty($whatsappNumber)) {
            $this->jsonError('whatsapp_number is required', [], 422);
            return;
        }

        $this->billDesignSettingModel->updateSettings([
            'whatsapp_number' => $whatsappNumber,
            'default_enquiry_message' => $defaultMessage,
            'business_name' => $businessName,
            'support_email' => $supportEmail
        ], (int)Session::getUserId());

        $this->jsonSuccess('WhatsApp settings saved', [
            'settings' => $this->billDesignSettingModel->getSettings()
        ]);
    }
}
