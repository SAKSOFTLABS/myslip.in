<?php
namespace App\Models;

use App\Core\Model;

class ItemGroup extends Model
{
    protected string $table = 'item_groups';
    protected bool $tenantScoped = true;
}
