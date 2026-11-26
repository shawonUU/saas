<?php

namespace App\Models\Public\Business\Admin\Restaurant\Dining\Reservations;

use App\Models\Concerns\ErpCrud;
use App\Models\Configurations\DiningTable;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use ErpCrud;

    protected $table = 'restaurant_reservations';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'guest_count' => 'integer'];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
