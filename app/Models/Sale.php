<?php
namespace App\Models;

use App\Core\Model;

class Sale extends Model
{
    protected string $table = 'sales';
    protected bool $tenantScoped = true;

    public function getSalesList(array $filters = []): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT s.*, c.mobile as customer_phone
                FROM `sales` s
                LEFT JOIN `customers` c ON s.customer_id = c.id
                WHERE s.company_id = :comp_id";
        
        $params = ['comp_id' => $companyId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND s.invoice_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND s.invoice_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND s.customer_id = :cust_id";
            $params['cust_id'] = $filters['customer_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (s.invoice_number LIKE :search OR s.customer_name LIKE :search OR s.customer_mobile LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY s.invoice_date DESC, s.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function getSaleWithItems(int $id): ?array
    {
        $companyId = $this->getCompanyId();
        $sale = $this->find($id);
        if (!$sale) return null;

        $items = $this->db->fetchAll(
            "SELECT si.*, i.code as item_code 
             FROM `sales_items` si 
             LEFT JOIN `items` i ON si.item_id = i.id 
             WHERE si.sale_id = :sale_id AND si.company_id = :comp_id",
            ['sale_id' => $id, 'comp_id' => $companyId]
        );

        $sale['items'] = $items;
        return $sale;
    }

    public function getSaleWithItemsPublic(int $id): ?array
    {
        $sale = $this->db->fetchOne("SELECT * FROM `{$this->table}` WHERE `id` = :id AND `status` = 'active'", ['id' => $id]);
        if (!$sale) return null;

        $items = $this->db->fetchAll(
            "SELECT si.*, i.code as item_code 
             FROM `sales_items` si 
             LEFT JOIN `items` i ON si.item_id = i.id 
             WHERE si.sale_id = :sale_id AND si.company_id = :comp_id",
            ['sale_id' => $id, 'comp_id' => $sale['company_id']]
        );

        $sale['items'] = $items;
        return $sale;
    }
}
