<?php
namespace App\Models;

use App\Core\Model;

class Vendor extends Model
{
    protected string $table = 'vendors';
    protected bool $tenantScoped = true;
    protected bool $useSoftDelete = true;

    public function getVendorsWithOutstanding(): array
    {
        $companyId = $this->getCompanyId();
        $sql = "SELECT v.*,
                       COALESCE((SELECT SUM(balance_amount) FROM `purchases` p WHERE p.vendor_id = v.id AND p.status = 'active'), 0) as bill_outstanding
                FROM `vendors` v
                WHERE v.company_id = :comp_id AND v.is_deleted = 0
                ORDER BY v.name ASC";
        return $this->db->fetchAll($sql, ['comp_id' => $companyId]);
    }

    public function getOutstandingPayable(int $vendorId): float
    {
        $companyId = $this->getCompanyId();
        $vend = $this->find($vendorId);
        if (!$vend) return 0.00;

        $opening = ($vend['balance_type'] === 'CR') ? (float)$vend['opening_balance'] : -(float)$vend['opening_balance'];

        $sql = "SELECT COALESCE(SUM(balance_amount), 0) FROM `purchases` WHERE `company_id` = :comp_id AND `vendor_id` = :vend_id AND `status` = 'active'";
        $billOutstanding = (float)$this->db->fetchValue($sql, ['comp_id' => $companyId, 'vend_id' => $vendorId]);

        return round($opening + $billOutstanding, 2);
    }
}
