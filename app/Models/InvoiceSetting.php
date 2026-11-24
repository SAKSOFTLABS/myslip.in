<?php
namespace App\Models;

use App\Core\Model;

class InvoiceSetting extends Model
{
    protected string $table = 'invoice_settings';
    protected bool $tenantScoped = true;

    public function getSettingsForCompany(int $companyId): array
    {
        $settings = $this->db->fetchOne("SELECT * FROM `invoice_settings` WHERE `company_id` = :comp_id", ['comp_id' => $companyId]);
        if (!$settings) {
            // Default template settings
            return [
                'company_id' => $companyId,
                'default_format' => 'a4',
                'show_logo' => 1,
                'show_bank_details' => 1,
                'show_upi_qr' => 1,
                'show_terms' => 1,
                'terms_conditions' => "1. Goods once sold will not be taken back.\n2. Interest @ 18% p.a. will be charged if payment is not made within due date.\n3. Subject to local jurisdiction.",
                'declaration' => "We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.",
                'signature_label' => 'Authorized Signatory',
                'print_copies' => 1,
                'primary_color' => '#1e3a8a',
                'thermal_footer_note' => 'Thank you for your business!'
            ];
        }
        return $settings;
    }
}
