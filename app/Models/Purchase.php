<?php
namespace App\Models;

use App\Core\Model;

class Purchase extends Model
{
    protected string $table = 'purchases';
    protected bool $tenantScoped = true;

    public function getPurchaseList(array $filters = []): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT p.*, v.mobile as vendor_phone
                FROM `purchases` p
                LEFT JOIN `vendors` v ON p.vendor_id = v.id
                WHERE p.company_id = :comp_id";
        
        $params = ['comp_id' => $companyId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND p.purchase_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND p.purchase_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['vendor_id'])) {
            $sql .= " AND p.vendor_id = :vend_id";
            $params['vend_id'] = $filters['vendor_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.purchase_number LIKE :search OR p.supplier_invoice_number LIKE :search OR p.vendor_name LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY p.purchase_date DESC, p.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function getPurchaseWithItems(int $id): ?array
    {
        $companyId = $this->getCompanyId();
        $purchase = $this->find($id);
        if (!$purchase) return null;

        $items = $this->db->fetchAll(
            "SELECT pi.*, i.code as item_code 
             FROM `purchase_items` pi 
             LEFT JOIN `items` i ON pi.item_id = i.id 
             WHERE pi.purchase_id = :pur_id AND pi.company_id = :comp_id",
            ['pur_id' => $id, 'comp_id' => $companyId]
        );

        $purchase['items'] = $items;
        return $purchase;
    }
}
