<?php

namespace App\Models\Public\Business\Admin\Settings\Business\BusinessDate;

use Illuminate\Database\Eloquent\Model;

class BusinessDaySetting extends Model
{
    protected $table = 'business_day_settings';
    protected $guarded = [];
    protected function casts(): array
    {
        return ['manual_mode' => 'boolean', 'open_date' => 'date:Y-m-d'];
    }
}
