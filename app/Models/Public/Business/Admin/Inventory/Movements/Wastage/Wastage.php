<?php

namespace App\Models\Public\Business\Admin\Inventory\Movements\Wastage;

use App\Models\Concerns\ErpCrud;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wastage extends Model
{
    use ErpCrud;

    protected $table = 'restaurant_wastages';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:2', 'posted_at' => 'datetime'];
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
