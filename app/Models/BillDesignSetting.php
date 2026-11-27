<?php
namespace App\Models;

use App\Core\Model;

class BillDesignSetting extends Model
{
    protected string $table = 'bill_design_settings';
    protected bool $tenantScoped = false;

    /**
     * Get single global bill design setting row
     */
    public function getSettings(): array
    {
        $setting = $this->db->fetchOne("SELECT * FROM `bill_design_settings` ORDER BY id ASC LIMIT 1");
        if (!$setting) {
            return [
                'id' => 1,
                'whatsapp_number' => '919876543210',
                'default_enquiry_message' => "Hello, I am interested in the bill design \"{{design_name}}\" in your billing software.\n\nCompany: {{company_name}}\n\nPlease provide the pricing and activation details.",
                'business_name' => 'MYSLIP.IN Billing SaaS',
                'support_email' => 'support@myslip.in'
            ];
        }
        return $setting;
    }

    /**
     * Update global WhatsApp & enquiry settings
     */
    public function updateSettings(array $data, int $userId): bool
    {
        $existing = $this->db->fetchOne("SELECT id FROM `bill_design_settings` ORDER BY id ASC LIMIT 1");
        
        // Clean whatsapp number to digits only (international format)
        $cleanPhone = preg_replace('/[^0-9]/', '', $data['whatsapp_number'] ?? '');
        $message = trim($data['default_enquiry_message'] ?? '');
        $businessName = trim($data['business_name'] ?? 'MYSLIP.IN');
        $supportEmail = trim($data['support_email'] ?? 'support@myslip.in');

        if ($existing) {
            $this->db->execute(
                "UPDATE `bill_design_settings` 
                 SET `whatsapp_number` = :phone, 
                     `default_enquiry_message` = :msg, 
                     `business_name` = :bname, 
                     `support_email` = :email, 
                     `updated_by` = :uid 
                 WHERE id = :id",
                [
                    'phone' => $cleanPhone,
                    'msg' => $message,
                    'bname' => $businessName,
                    'email' => $supportEmail,
                    'uid' => $userId,
                    'id' => $existing['id']
                ]
            );
        } else {
            $this->db->execute(
                "INSERT INTO `bill_design_settings` 
                 (`whatsapp_number`, `default_enquiry_message`, `business_name`, `support_email`, `updated_by`) 
                 VALUES (:phone, :msg, :bname, :email, :uid)",
                [
                    'phone' => $cleanPhone,
                    'msg' => $message,
                    'bname' => $businessName,
                    'email' => $supportEmail,
                    'uid' => $userId
                ]
            );
        }

        return true;
    }

    /**
     * Generate valid WhatsApp Click-to-Chat URL with encoded prefilled message
     */
    public function generateWhatsAppUrl(string $designName, string $companyName, int|string $templateId): string
    {
        $settings = $this->getSettings();
        $number = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '');
        if (empty($number)) {
            $number = '919876543210';
        }

        $template = $settings['default_enquiry_message'] ?? "Hello, I am interested in the bill design \"{{design_name}}\" in your billing software.\n\nCompany: {{company_name}}\n\nPlease provide the pricing and activation details.";

        $replacements = [
            '{{design_name}}' => $designName,
            '{{company_name}}' => $companyName,
            '{{template_id}}' => (string)$templateId
        ];

        $message = str_replace(array_keys($replacements), array_values($replacements), $template);

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}
