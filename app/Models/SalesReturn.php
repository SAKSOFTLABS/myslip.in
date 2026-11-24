<?php
namespace App\Models;

use App\Core\Model;

class SalesReturn extends Model
{
    protected string $table = 'sales_returns';
    protected bool $tenantScoped = true;

    public function getReturnWithItems(int $id): ?array
    {
        $companyId = $this->getCompanyId();
        $ret = $this->find($id);
        if (!$ret) return null;

        $items = $this->db->fetchAll(
            "SELECT sri.*, i.code as item_code 
             FROM `sales_return_items` sri 
             LEFT JOIN `items` i ON sri.item_id = i.id 
             WHERE sri.sales_return_id = :sr_id AND sri.company_id = :comp_id",
            ['sr_id' => $id, 'comp_id' => $companyId]
        );

        $ret['items'] = $items;
        return $ret;
    }
}
