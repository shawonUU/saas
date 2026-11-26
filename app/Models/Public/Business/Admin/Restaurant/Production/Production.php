<?php

namespace App\Models\Public\Business\Admin\Restaurant\Production;

use App\Models\Concerns\ErpCrud;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use ErpCrud;

    protected $table = 'restaurant_productions';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'total_cost' => 'decimal:2', 'ingredients' => 'array', 'posted_at' => 'datetime'];
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'food_variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
