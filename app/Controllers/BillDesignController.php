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
use App\Services\BillTemplateEngine;

class BillDesignController extends Controller
{
    private BillTemplate $billTemplateModel;
    private CompanyBillSetting $companyBillSettingModel;
    private CompanyBillTemplateAccess $companyAccessModel;
    private BillDesignSetting $billDesignSettingModel;
    private Company $companyModel;
    private InvoiceSetting $invoiceSettingModel;

    public function __construct()
    {
        parent::__construct();
        $this->billTemplateModel = new BillTemplate();
        $this->companyBillSettingModel = new CompanyBillSetting();
        $this->companyAccessModel = new CompanyBillTemplateAccess();
        $this->billDesignSettingModel = new BillDesignSetting();
        $this->companyModel = new Company();
        $this->invoiceSettingModel = new InvoiceSetting();
    }

    /**
     * Bill Design Gallery for authenticated tenant company
     */
    public function gallery(): void
    {
        $companyId = Session::getCompanyId();
        $company = $this->companyModel->find($companyId);
        $companyName = $company['company_name'] ?? 'My Business';

        // Filters & Search
        $filters = [
            'pricing_type' => $this->request->input('pricing_type', 'all'),
            'category' => $this->request->input('category', 'all'),
            'paper_size' => $this->request->input('paper_size', 'all'),
            'search' => trim($this->request->input('search', ''))
        ];
        $sort = $this->request->input('sort', 'display_order ASC');

        $templates = $this->billTemplateModel->getPublished($filters, $sort);

        // Company's currently active template
        $activeSetting = $this->companyBillSettingModel->getActiveTemplate($companyId, 'sales_invoice');
        $activeTemplateId = (int)($activeSetting['active_template_id'] ?? 1);

        // Company's unlocked/entitled paid templates
        $entitledTemplateIds = $this->companyAccessModel->getCompanyEntitledTemplateIds($companyId);

        // WhatsApp Click-to-Chat URL generator
        foreach ($templates as &$tpl) {
            $isPaid = $tpl['pricing_type'] === 'paid';
            $hasAccess = in_array((int)$tpl['id'], $entitledTemplateIds);
            $isActive = ((int)$tpl['id'] === $activeTemplateId);

            $tpl['is_active'] = $isActive;
            $tpl['has_access'] = $hasAccess || !$isPaid;
            $tpl['whatsapp_url'] = $this->billDesignSettingModel->generateWhatsAppUrl(
                $tpl['name'],
                $companyName,
                $tpl['id']
            );
        }
        unset($tpl);

        $whatsappSettings = $this->billDesignSettingModel->getSettings();

        $this->view('bill_designs.gallery', [
            'pageTitle' => 'Bill Design Gallery',
            'templates' => $templates,
            'activeTemplateId' => $activeTemplateId,
            'entitledTemplateIds' => $entitledTemplateIds,
            'filters' => $filters,
            'sort' => $sort,
            'company' => $company,
            'whatsappSettings' => $whatsappSettings
        ]);
    }

    /**
     * Live Preview of a Bill Design with sample/company invoice data
     */
    public function preview(int $id): void
    {
        $companyId = Session::getCompanyId();
        $company = $this->companyModel->find($companyId);
        $template = $this->billTemplateModel->find($id);

        if (!$template || $template['status'] === 'archived') {
            $this->flash('danger', 'Bill design template not found.');
            $this->redirect('/settings/bill-designs');
            return;
        }

        $activeSetting = $this->companyBillSettingModel->getActiveTemplate($companyId, 'sales_invoice');
        $isActive = ((int)($activeSetting['active_template_id'] ?? 0) === $id);

        $isPaid = $template['pricing_type'] === 'paid';
        $hasAccess = !$isPaid || $this->companyAccessModel->hasAccess($companyId, $id);

        $whatsappUrl = $this->billDesignSettingModel->generateWhatsAppUrl(
            $template['name'],
            $company['company_name'] ?? 'My Business',
            $template['id']
        );

        $sampleSale = BillTemplateEngine::getSampleInvoiceData($company);
        $settings = $this->invoiceSettingModel->getSettingsForCompany($companyId);

        if (!empty($template['template_config'])) {
            $tplConfig = json_decode($template['template_config'], true) ?: [];
            $settings = array_merge($settings, $tplConfig);
        }

        $templateView = BillTemplateEngine::getViewPath($template['template_key']);

        $this->view('bill_designs.preview', [
            'pageTitle' => 'Preview Design — ' . $template['name'],
            'template' => $template,
            'isActive' => $isActive,
            'hasAccess' => $hasAccess,
            'whatsappUrl' => $whatsappUrl,
            'sampleSale' => $sampleSale,
            'company' => $company,
            'settings' => $settings,
            'templateView' => $templateView
        ]);
    }

    /**
     * Activate a bill design for the authenticated company
     */
    public function activate(int $id): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $userId = Session::getUserId();

        $template = $this->billTemplateModel->find($id);
        if (!$template || $template['status'] !== 'published') {
            $this->flash('danger', 'Selected bill design is not available for activation.');
            $this->redirect('/settings/bill-designs');
            return;
        }

        // Server-side entitlement validation for Paid templates
        if ($template['pricing_type'] === 'paid') {
            if (!$this->companyAccessModel->hasAccess($companyId, $id)) {
                $this->flash('danger', 'This is a Paid Bill Design. Please contact us on WhatsApp to enquire and obtain an activation license before activating.');
                $this->redirect('/settings/bill-designs');
                return;
            }
        }

        $this->companyBillSettingModel->activateTemplate(
            $companyId,
            $id,
            $template['version'],
            'sales_invoice',
            $userId
        );

        $this->flash('success', "Bill Design [{$template['name']}] has been activated as your company's active invoice format!");
        $this->redirect('/settings/bill-designs');
    }
}
