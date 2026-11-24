<?php
namespace App\Models;

use App\Core\Model;

class PurchaseReturn extends Model
{
    protected string $table = 'purchase_returns';
    protected bool $tenantScoped = true;

    public function getReturnWithItems(int $id): ?array
    {
        $companyId = $this->getCompanyId();
        $ret = $this->find($id);
        if (!$ret) return null;

        $items = $this->db->fetchAll(
            "SELECT pri.*, i.code as item_code 
             FROM `purchase_return_items` pri 
             LEFT JOIN `items` i ON pri.item_id = i.id 
             WHERE pri.purchase_return_id = :pr_id AND pri.company_id = :comp_id",
            ['pr_id' => $id, 'comp_id' => $companyId]
        );

        $ret['items'] = $items;
        return $ret;
    }
}
