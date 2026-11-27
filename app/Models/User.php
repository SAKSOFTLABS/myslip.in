<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected bool $tenantScoped = false;

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `users` WHERE `email` = :email", ['email' => $email]);
    }

    public function findByMobile(string $mobile): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `users` WHERE `mobile` = :mobile", ['mobile' => $mobile]);
    }
}
