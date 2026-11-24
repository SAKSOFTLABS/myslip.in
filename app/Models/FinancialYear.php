<?php
namespace App\Models;

use App\Core\Model;

class FinancialYear extends Model
{
    protected string $table = 'financial_years';
    protected bool $tenantScoped = true;

    public function getCurrentFY(int $companyId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `financial_years` WHERE `company_id` = :comp_id AND `is_current` = 1 LIMIT 1", [
            'comp_id' => $companyId
        ]);
    }
}
