<?php
namespace App\Models;

use App\Core\Model;

class CompanyBillSetting extends Model
{
    protected string $table = 'company_bill_settings';
    protected bool $tenantScoped = true;

    /**
     * Get active template for a company and document type
     */
    public function getActiveTemplate(int $companyId, string $documentType = 'sales_invoice'): ?array
    {
        $setting = $this->db->fetchOne(
            "SELECT cbs.*, bt.name as template_name, bt.template_key, bt.paper_size, bt.category, bt.preview_image, bt.template_config, bt.pricing_type, bt.version as latest_version
             FROM `company_bill_settings` cbs
             JOIN `bill_templates` bt ON cbs.active_template_id = bt.id
             WHERE cbs.company_id = :cid AND cbs.document_type = :doc LIMIT 1",
            ['cid' => $companyId, 'doc' => $documentType]
        );

        if (!$setting) {
            // Default to classic-gst if no active template record exists yet
            $defaultTpl = $this->db->fetchOne("SELECT * FROM `bill_templates` WHERE `template_key` = 'classic-gst' LIMIT 1");
            if ($defaultTpl) {
                return [
                    'company_id' => $companyId,
                    'active_template_id' => (int)$defaultTpl['id'],
                    'active_template_version' => $defaultTpl['version'],
                    'document_type' => $documentType,
                    'template_name' => $defaultTpl['name'],
                    'template_key' => $defaultTpl['template_key'],
                    'paper_size' => $defaultTpl['paper_size'],
                    'category' => $defaultTpl['category'],
                    'preview_image' => $defaultTpl['preview_image'],
                    'template_config' => $defaultTpl['template_config'],
                    'pricing_type' => $defaultTpl['pricing_type'],
                    'latest_version' => $defaultTpl['version']
                ];
            }
        }

        return $setting;
    }

    /**
     * Activate a template for a company
     */
    public function activateTemplate(int $companyId, int $templateId, string $version, string $documentType = 'sales_invoice', ?int $userId = null): bool
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM `company_bill_settings` WHERE `company_id` = :cid AND `document_type` = :doc LIMIT 1",
            ['cid' => $companyId, 'doc' => $documentType]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE `company_bill_settings` 
                 SET `active_template_id` = :tid, 
                     `active_template_version` = :ver, 
                     `updated_by` = :uid,
                     `updated_at` = NOW()
                 WHERE id = :id",
                [
                    'tid' => $templateId,
                    'ver' => $version,
                    'uid' => $userId,
                    'id' => $existing['id']
                ]
            );
        } else {
            $this->db->execute(
                "INSERT INTO `company_bill_settings` 
                 (`company_id`, `active_template_id`, `active_template_version`, `document_type`, `updated_by`, `created_at`, `updated_at`) 
                 VALUES (:cid, :tid, :ver, :doc, :uid, NOW(), NOW())",
                [
                    'cid' => $companyId,
                    'tid' => $templateId,
                    'ver' => $version,
                    'doc' => $documentType,
                    'uid' => $userId
                ]
            );
        }

        return true;
    }
}
