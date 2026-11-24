<?php
namespace App\Models;

use App\Core\Model;

class PurchaseItem extends Model
{
    protected string $table = 'purchase_items';
    protected bool $tenantScoped = true;
}
