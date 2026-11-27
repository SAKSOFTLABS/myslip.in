<?php
namespace App\Models;

use App\Core\Model;

class Company extends Model
{
    protected string $table = 'companies';
    protected bool $tenantScoped = false;

    public function getUserCompanies(int $userId): array
    {
        $sql = "SELECT c.*, cu.role_id, r.role_name 
                FROM `companies` c
                JOIN `company_users` cu ON c.id = cu.company_id
                JOIN `roles` r ON cu.role_id = r.id
                WHERE cu.user_id = :user_id AND cu.status = 'active'
                ORDER BY c.id DESC";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
}
