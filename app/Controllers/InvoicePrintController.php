<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Sale;
use App\Models\Company;
use App\Models\InvoiceSetting;

class InvoicePrintController extends Controller
{
    private Sale $saleModel;
    private Company $companyModel;
    private InvoiceSetting $invoiceSettingModel;

    public function __construct()
    {
        parent::__construct();
        $this->saleModel = new Sale();
        $this->companyModel = new Company();
        $this->invoiceSettingModel = new InvoiceSetting();
    }

    public function printInvoice(int $id): void
    {
        $companyId = Session::getCompanyId();
        $sale = $this->saleModel->getSaleWithItems($id);
        
        if (!$sale) {
            $this->flash('danger', 'Invoice not found.');
            $this->redirect('/sales');
        }

        $company = $this->companyModel->find($companyId);
        $settings = $this->invoiceSettingModel->getSettingsForCompany($companyId);

        // Check format override from URL parameter (e.g. ?format=thermal or ?format=a5)
        $format = $this->request->input('format', $settings['default_format'] ?? 'a4');

        $viewFile = match($format) {
            'thermal' => 'invoices.thermal',
            'a5' => 'invoices.a5',
            default => 'invoices.a4'
        };

        $this->view($viewFile, [
            'pageTitle' => 'Invoice ' . $sale['invoice_number'],
            'sale' => $sale,
            'company' => $company,
            'settings' => $settings,
            'format' => $format
        ], 'print');
    }
}
