<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    protected bool $tenantScoped = true;

    public function getPaymentList(array $filters = []): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT p.*, v.name as vendor_name, exp.account_name as expense_account_name, from_acc.account_name as paid_from_name
                FROM `payments` p
                LEFT JOIN `vendors` v ON p.vendor_id = v.id
                LEFT JOIN `accounts` exp ON p.expense_account_id = exp.id
                JOIN `accounts` from_acc ON p.paid_from_account_id = from_acc.id
                WHERE p.company_id = :comp_id";
        
        $params = ['comp_id' => $companyId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND p.payment_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND p.payment_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";
        return $this->db->fetchAll($sql, $params);
    }
}
