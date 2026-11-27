<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Sale;
use App\Models\Company;
use App\Models\InvoiceSetting;
use App\Models\CompanyBillSetting;
use App\Models\BillTemplate;
use App\Services\BillTemplateEngine;

class InvoicePrintController extends Controller
{
    private Sale $saleModel;
    private Company $companyModel;
    private InvoiceSetting $invoiceSettingModel;
    private CompanyBillSetting $companyBillSettingModel;
    private BillTemplate $billTemplateModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new Sale();
        $this->companyModel = new Company();
        $this->invoiceSettingModel = new InvoiceSetting();
        $this->companyBillSettingModel = new CompanyBillSetting();
        $this->billTemplateModel = new BillTemplate();
    }

    public function printInvoice(int $id): void
    {
        $companyId = Session::getCompanyId();
        $sale = $this->saleModel->getSaleWithItems($id);
        
        if (!$sale) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
            return;
        }

        $company = $this->companyModel->find($companyId);
        $settings = $this->invoiceSettingModel->getSettingsForCompany($companyId);

        // Check if specific template_key or format is requested
        $templateKey = $this->request->input('template_key');
        $format = $this->request->input('format');

        if (!$templateKey) {
            if ($format) {
                $templateKey = match($format) {
                    'thermal' => 'thermal-80mm',
                    'a5' => 'retail-pos',
                    default => 'classic-gst'
                };
            } else {
                // Read company's active bill design setting
                $activeSetting = $this->companyBillSettingModel->getActiveTemplate($companyId, 'sales_invoice');
                if ($activeSetting && !empty($activeSetting['template_key'])) {
                    $templateKey = $activeSetting['template_key'];
                }
            }
        }

        if (empty($templateKey)) {
            $templateKey = 'classic-gst';
        }

        $template = $this->billTemplateModel->findByKey($templateKey);
        if ($template && !empty($template['template_config'])) {
            $tplConfig = json_decode($template['template_config'], true) ?: [];
            $settings = array_merge($settings, $tplConfig);
        }

        $viewFile = BillTemplateEngine::getViewPath($templateKey);

        $this->view($viewFile, [
            'pageTitle' => 'Invoice ' . $sale['invoice_number'],
            'sale' => $sale,
            'company' => $company,
            'settings' => $settings,
            'format' => $format ?: ($template['paper_size'] ?? 'A4'),
            'template' => $template,
            'isPreview' => false
        ], 'print');
    }
}
