<?php

namespace App\Models\Inventory;

use App\Models\Concerns\ErpCrud;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use ErpCrud;

    protected $table = 'stock_movements';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:2'];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
