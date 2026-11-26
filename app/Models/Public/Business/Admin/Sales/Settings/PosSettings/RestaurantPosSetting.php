<?php

namespace App\Models\Public\Business\Admin\Sales\Settings\PosSettings;

use App\Models\Business;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantPosSetting extends Model
{
    use ErpCrud;

    protected $fillable = ['business_id', 'kitchen_mode', 'auto_send_to_kitchen', 'default_service'];

    protected function casts(): array
    {
        return ['business_id' => 'integer', 'auto_send_to_kitchen' => 'boolean'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
