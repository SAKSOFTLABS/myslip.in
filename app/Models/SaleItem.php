<?php
namespace App\Models;

use App\Core\Model;

class SaleItem extends Model
{
    protected string $table = 'sales_items';
    protected bool $tenantScoped = true;
}
