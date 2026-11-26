<?php

namespace App\Models\Sales;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use ErpCrud;

    protected $table = 'discounts';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
