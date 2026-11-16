<?php

namespace App\Models;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'billing_interval' => 'integer', 'trial_days' => 'integer', 'sort_order' => 'integer'];
    }

    public function category() { return $this->belongsTo(BusinessCategory::class, 'business_category_id'); }
    public function modules(): HasMany { return $this->hasMany(SubscriptionPlanModule::class, 'subscription_plan_id'); }
    public function menus(): HasMany { return $this->hasMany(SubscriptionPlanMenu::class, 'subscription_plan_id'); }
    public function childMenus(): HasMany { return $this->hasMany(SubscriptionPlanChildMenu::class, 'subscription_plan_id'); }
    public function subscriptions(): HasMany { return $this->hasMany(BusinessSubscription::class, 'plan_id'); }
}
