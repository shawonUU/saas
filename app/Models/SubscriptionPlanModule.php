<?php

namespace App\Models;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlanModule extends Model
{
    use ErpCrud, SoftDeletes;

    protected $guarded = [];
    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id'); }
    public function category() { return $this->belongsTo(BusinessCategory::class, 'business_category_id'); }
    public function module() { return $this->belongsTo(Module::class); }
}
