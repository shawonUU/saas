<?php

namespace App\Models\Public\Business\Admin\Settings\Business\BusinessDate;

use Illuminate\Database\Eloquent\Model;

class BusinessDayClosure extends Model
{
    protected $table = 'business_day_closures';
    protected $guarded = [];
    protected function casts(): array
    {
        return ['closed_business_date' => 'date:Y-m-d', 'next_business_date' => 'date:Y-m-d', 'closed_at' => 'datetime'];
    }
}
