<?php

namespace App\Models\Sales;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use ErpCrud;

    protected $table = 'orders';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'restaurant_closed_at' => 'datetime',
            'restaurant_version' => 'integer',
            'restaurant_workflow' => 'array',
            'guest_count' => 'integer',
            'restaurant_tendered' => 'decimal:2',
            'restaurant_change' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
