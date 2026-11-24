<?php
namespace App\Models;

use App\Core\Model;

class Customer extends Model
{
    protected string $table = 'customers';
    protected bool $tenantScoped = true;
    protected bool $useSoftDelete = true;

    public function getCustomersWithOutstanding(): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT c.*,
                       COALESCE((SELECT SUM(balance_amount) FROM `sales` s WHERE s.customer_id = c.id AND s.status = 'active'), 0) as invoice_outstanding
                FROM `customers` c
                WHERE c.company_id = :comp_id AND c.is_deleted = 0
                ORDER BY c.name ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }

    public function getOutstandingBalance(int $customerId): float
    {
        $companyId = $this->getCompanyId();
        $cust = $this->find($customerId);
        if (!$cust) return 0.00;

        $opening = ($cust['balance_type'] === 'DR') ? (float)$cust['opening_balance'] : -(float)$cust['opening_balance'];

        $sql = "SELECT COALESCE(SUM(balance_amount), 0) FROM `sales` WHERE `company_id` = :comp_id AND `customer_id` = :cust_id AND `status` = 'active'";
        $invOutstanding = (float)$this->db->fetchValue($sql, ['comp_id' => $companyId, 'cust_id' => $customerId]);

        return round($opening + $invOutstanding, 2);
    }
}
