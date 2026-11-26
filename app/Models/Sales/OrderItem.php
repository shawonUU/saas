<?php

namespace App\Models\Sales;

use App\Models\Concerns\ErpCrud;
use App\Models\Configurations\Variant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use ErpCrud;

    protected $table = 'order_items';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'modifier_snapshot' => 'array',
            'recipe_snapshot' => 'array',
            'sent_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
            'kitchen_batch' => 'integer',
            'base_price' => 'decimal:2',
            'cost' => 'decimal:5',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
