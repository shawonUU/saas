<?php

namespace App\Models;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessSubscription extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime', 'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime', 'canceled_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class); }
}
