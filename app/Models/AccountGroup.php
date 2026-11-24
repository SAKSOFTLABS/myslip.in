<?php
namespace App\Models;

use App\Core\Model;

class AccountGroup extends Model
{
    protected string $table = 'account_groups';
    protected bool $tenantScoped = false;

    public function getGroups(int $companyId): array
    {
        $sql = "SELECT * FROM `account_groups` WHERE `company_id` = :comp_id OR `company_id` IS NULL ORDER BY `id` ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }
}
