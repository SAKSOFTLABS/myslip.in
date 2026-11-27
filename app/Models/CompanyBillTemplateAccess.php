<?php
namespace App\Models;

use App\Core\Model;

class CompanyBillTemplateAccess extends Model
{
    protected string $table = 'company_bill_template_access';
    protected bool $tenantScoped = false; // Queried globally by Super Admin or scoped by company

    /**
     * Check if a company has active entitlement access to a template
     */
    public function hasAccess(int $companyId, int $templateId): bool
    {
        // First check if template is free - free templates are accessible to all
        $tpl = $this->db->fetchOne("SELECT pricing_type FROM `bill_templates` WHERE id = :id", ['id' => $templateId]);
        if ($tpl && $tpl['pricing_type'] === 'free') {
            return true;
        }

        // For paid templates, check active entitlement
        $access = $this->db->fetchOne(
            "SELECT id FROM `company_bill_template_access` 
             WHERE `company_id` = :cid AND `template_id` = :tid AND `access_status` = 'active' LIMIT 1",
            ['cid' => $companyId, 'tid' => $templateId]
        );

        return (bool)$access;
    }

    /**
     * Get list of template IDs that a company has active entitlement to
     */
    public function getCompanyEntitledTemplateIds(int $companyId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT template_id FROM `company_bill_template_access` WHERE `company_id` = :cid AND `access_status` = 'active'",
            ['cid' => $companyId]
        );
        return array_map(fn($r) => (int)$r['template_id'], $rows);
    }

    /**
     * Grant access to a Paid design for a company
     */
    public function grantAccess(int $companyId, int $templateId, int $adminId, ?string $notes = null): bool
    {
        $existing = $this->db->fetchOne(
            "SELECT id FROM `company_bill_template_access` WHERE `company_id` = :cid AND `template_id` = :tid LIMIT 1",
            ['cid' => $companyId, 'tid' => $templateId]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE `company_bill_template_access` 
                 SET `access_status` = 'active', 
                     `granted_by` = :aid, 
                     `granted_date` = NOW(), 
                     `revoked_date` = NULL, 
                     `notes` = :notes, 
                     `updated_at` = NOW() 
                 WHERE id = :id",
                [
                    'aid' => $adminId,
                    'notes' => $notes,
                    'id' => $existing['id']
                ]
            );
        } else {
            $this->db->execute(
                "INSERT INTO `company_bill_template_access` 
                 (`company_id`, `template_id`, `access_status`, `granted_by`, `granted_date`, `notes`, `created_at`, `updated_at`) 
                 VALUES (:cid, :tid, 'active', :aid, NOW(), :notes, NOW(), NOW())",
                [
                    'cid' => $companyId,
                    'tid' => $templateId,
                    'aid' => $adminId,
                    'notes' => $notes
                ]
            );
        }

        return true;
    }

    /**
     * Revoke access to a Paid design from a company
     */
    public function revokeAccess(int $companyId, int $templateId, ?int $adminId = null): bool
    {
        $this->db->execute(
            "UPDATE `company_bill_template_access` 
             SET `access_status` = 'revoked', 
                 `revoked_date` = NOW(), 
                 `updated_at` = NOW() 
             WHERE `company_id` = :cid AND `template_id` = :tid",
            [
                'cid' => $companyId,
                'tid' => $templateId
            ]
        );

        // If company was actively using this template, revert them back to default classic-gst
        $active = $this->db->fetchOne(
            "SELECT id FROM `company_bill_settings` WHERE `company_id` = :cid AND `active_template_id` = :tid LIMIT 1",
            ['cid' => $companyId, 'tid' => $templateId]
        );

        if ($active) {
            $defaultTpl = $this->db->fetchOne("SELECT id, version FROM `bill_templates` WHERE `template_key` = 'classic-gst' LIMIT 1");
            if ($defaultTpl) {
                $this->db->execute(
                    "UPDATE `company_bill_settings` 
                     SET `active_template_id` = :did, `active_template_version` = :ver, `updated_at` = NOW() 
                     WHERE id = :id",
                    [
                        'did' => $defaultTpl['id'],
                        'ver' => $defaultTpl['version'],
                        'id' => $active['id']
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Fetch list of all company entitlements for Super Admin view
     */
    public function getEntitlementsList(array $filters = []): array
    {
        $sql = "SELECT cbta.*, c.company_name, c.email as company_email, c.mobile as company_phone, 
                       bt.name as template_name, bt.template_key, bt.pricing_type, bt.price,
                       u.full_name as granted_by_name
                FROM `company_bill_template_access` cbta
                JOIN `companies` c ON cbta.company_id = c.id
                JOIN `bill_templates` bt ON cbta.template_id = bt.id
                LEFT JOIN `users` u ON cbta.granted_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['company_id'])) {
            $sql .= " AND cbta.`company_id` = :cid";
            $params['cid'] = (int)$filters['company_id'];
        }

        if (!empty($filters['template_id'])) {
            $sql .= " AND cbta.`template_id` = :tid";
            $params['tid'] = (int)$filters['template_id'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'revoked'])) {
            $sql .= " AND cbta.`access_status` = :st";
            $params['st'] = $filters['status'];
        }

        $sql .= " ORDER BY cbta.id DESC";

        return $this->db->fetchAll($sql, $params);
    }
}
