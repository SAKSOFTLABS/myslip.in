<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Core\Response;

class GstReportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!Session::get('gst_enabled', true)) {
            $this->flash('warning', 'GST reporting is only available when GST mode is enabled for the company.');
            $this->redirect('/reports');
        }
    }

    public function index(): void
    {
        $this->gstr1();
    }

    public function gstr1(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = parseDateToDb($this->request->input('start_date')) ?: date('Y-m-01');
        $endDate = parseDateToDb($this->request->input('end_date')) ?: date('Y-m-t');

        // 1. B2B Invoices (Customers with GSTIN)
        $b2bInvoices = $db->fetchAll(
            "SELECT * FROM `sales` 
             WHERE `company_id` = :comp_id 
               AND `invoice_date` BETWEEN :st AND :en 
               AND `customer_gstin` IS NOT NULL 
               AND `customer_gstin` != '' 
               AND `status` = 'active'
             ORDER BY `invoice_date` ASC",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        // 2. B2C Invoices (Unregistered / Consumers)
        $b2cInvoices = $db->fetchAll(
            "SELECT * FROM `sales` 
             WHERE `company_id` = :comp_id 
               AND `invoice_date` BETWEEN :st AND :en 
               AND (`customer_gstin` IS NULL OR `customer_gstin` = '') 
               AND `status` = 'active'
             ORDER BY `invoice_date` ASC",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        // 3. HSN Summary of Outward Supplies
        $hsnSummary = $db->fetchAll(
            "SELECT si.hsn_sac, u.short_name as unit_name,
                    SUM(si.quantity) as total_qty,
                    SUM(si.total_amount) as total_val,
                    SUM(si.taxable_amount) as total_taxable,
                    SUM(si.cgst_amount) as total_cgst,
                    SUM(si.sgst_amount) as total_sgst,
                    SUM(si.igst_amount) as total_igst
             FROM `sales_items` si
             JOIN `sales` s ON si.sale_id = s.id
             LEFT JOIN `units` u ON si.unit_name = u.short_name
             WHERE s.company_id = :comp_id AND s.invoice_date BETWEEN :st AND :en AND s.status = 'active'
             GROUP BY si.hsn_sac, u.short_name
             ORDER BY si.hsn_sac ASC",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        $this->view('reports.gst.gstr1', [
            'pageTitle' => 'GSTR-1 Outward Supplies Report',
            'b2bInvoices' => $b2bInvoices,
            'b2cInvoices' => $b2cInvoices,
            'hsnSummary' => $hsnSummary,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function gstr3b(): void
    {
        $companyId = Session::getCompanyId();
        $db = Database::getInstance();

        $startDate = parseDateToDb($this->request->input('start_date')) ?: date('Y-m-01');
        $endDate = parseDateToDb($this->request->input('end_date')) ?: date('Y-m-t');

        // 3.1 Outward Taxable Supplies (from active Sales)
        $outward = $db->fetchOne(
            "SELECT COALESCE(SUM(taxable_amount), 0) as taxable,
                    COALESCE(SUM(cgst_amount), 0) as cgst,
                    COALESCE(SUM(sgst_amount), 0) as sgst,
                    COALESCE(SUM(igst_amount), 0) as igst,
                    COALESCE(SUM(total_tax_amount), 0) as total_tax
             FROM `sales`
             WHERE `company_id` = :comp_id AND `invoice_date` BETWEEN :st AND :en AND `status` = 'active'",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        // 4. Eligible Input Tax Credit (ITC from active Purchases)
        $inwardItc = $db->fetchOne(
            "SELECT COALESCE(SUM(taxable_amount), 0) as taxable,
                    COALESCE(SUM(cgst_amount), 0) as cgst,
                    COALESCE(SUM(sgst_amount), 0) as sgst,
                    COALESCE(SUM(igst_amount), 0) as igst,
                    COALESCE(SUM(total_tax_amount), 0) as total_tax
             FROM `purchases`
             WHERE `company_id` = :comp_id AND `purchase_date` BETWEEN :st AND :en AND `status` = 'active'",
            ['comp_id' => $companyId, 'st' => $startDate, 'en' => $endDate]
        );

        $this->view('reports.gst.gstr3b', [
            'pageTitle' => 'GSTR-3B Tax Summary Report',
            'outward' => $outward,
            'inwardItc' => $inwardItc,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
