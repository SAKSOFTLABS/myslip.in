<?php
namespace App\Models;

use App\Core\Model;

class Receipt extends Model
{
    protected string $table = 'receipts';
    protected bool $tenantScoped = true;

    public function getReceiptList(array $filters = []): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT r.*, c.name as customer_name, a.account_name as deposit_account_name
                FROM `receipts` r
                JOIN `customers` c ON r.customer_id = c.id
                JOIN `accounts` a ON r.deposit_account_id = a.id
                WHERE r.company_id = :comp_id";
        
        $params = ['comp_id' => $companyId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND r.receipt_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND r.receipt_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND r.customer_id = :cust_id";
            $params['cust_id'] = $filters['customer_id'];
        }

        $sql .= " ORDER BY r.receipt_date DESC, r.id DESC";
        return $this->db->fetchAll($sql, $params);
    }
}
