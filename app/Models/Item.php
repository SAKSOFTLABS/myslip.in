<?php
namespace App\Models;

use App\Core\Model;

class Item extends Model
{
    protected string $table = 'items';
    protected bool $tenantScoped = true;
    protected bool $useSoftDelete = true;

    public function getItemsWithDetails(?string $search = null, ?int $groupId = null): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT i.*, ig.name as group_name, u.short_name as unit_name,
                       COALESCE((SELECT SUM(qty_in - qty_out) FROM `stock_movements` sm WHERE sm.item_id = i.id AND sm.is_cancelled = 0), 0) as current_stock
                FROM `items` i
                LEFT JOIN `item_groups` ig ON i.group_id = ig.id
                LEFT JOIN `units` u ON i.unit_id = u.id
                WHERE i.company_id = :comp_id AND i.is_deleted = 0";
        
        $params = ['comp_id' => $companyId];

        if ($groupId) {
            $sql .= " AND i.group_id = :group_id";
            $params['group_id'] = $groupId;
        }

        if ($search) {
            $sql .= " AND (i.name LIKE :search OR i.code LIKE :search OR i.barcode LIKE :search OR i.hsn_sac LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " ORDER BY i.name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function findByBarcode(string $barcode): ?array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT i.*, u.short_name as unit_name,
                       COALESCE((SELECT SUM(qty_in - qty_out) FROM `stock_movements` sm WHERE sm.item_id = i.id AND sm.is_cancelled = 0), 0) as current_stock
                FROM `items` i
                LEFT JOIN `units` u ON i.unit_id = u.id
                WHERE i.company_id = :comp_id AND i.barcode = :barcode AND i.is_deleted = 0
                LIMIT 1";
        return $this->db->fetchOne($sql, ['comp_id' => $companyId, 'barcode' => $barcode]);
    }
}
