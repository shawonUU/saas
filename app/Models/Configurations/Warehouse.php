<?php

namespace App\Models\Configurations;

use App\Models\Concerns\ErpCrud;
use App\Models\Inventory\WarehouseStock;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use ErpCrud;

    protected $table = 'warehouses';

    protected $guarded = [];

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class, 'warehouse_id');
    }
}
