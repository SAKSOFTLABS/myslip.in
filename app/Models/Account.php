<?php
namespace App\Models;

use App\Core\Model;

class Account extends Model
{
    protected string $table = 'accounts';
    protected bool $tenantScoped = true;
    protected bool $useSoftDelete = true;

    public function getAccountsWithBalances(?int $financialYearId = null): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT a.*, ag.name as group_name, ag.category,
                       COALESCE((SELECT SUM(debit) FROM `ledger_entries` le WHERE le.account_id = a.id AND le.is_cancelled = 0), 0) as total_debit,
                       COALESCE((SELECT SUM(credit) FROM `ledger_entries` le WHERE le.account_id = a.id AND le.is_cancelled = 0), 0) as total_credit
                FROM `accounts` a
                JOIN `account_groups` ag ON a.group_id = ag.id
                WHERE a.company_id = :comp_id AND a.is_deleted = 0
                ORDER BY ag.category ASC, a.account_name ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }

    public function getCashAndBankAccounts(): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT * FROM `accounts` 
                WHERE `company_id` = :comp_id AND (`is_cash` = 1 OR `is_bank` = 1) AND `status` = 'active' AND `is_deleted` = 0 
                ORDER BY `is_cash` DESC, `account_name` ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }
}
