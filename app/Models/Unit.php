<?php
namespace App\Models;

use App\Core\Model;

class Unit extends Model
{
    protected string $table = 'units';
    protected bool $tenantScoped = false; // Units can be system-wide or company-scoped

    public function getAvailableUnits(int $companyId): array
    {
        $sql = "SELECT * FROM `units` WHERE (`company_id` = :comp_id OR `company_id` IS NULL) AND `status` = 'active' ORDER BY `name` ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }
}
