<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\SubscriptionPlan;
use App\Models\Module;
use App\Models\Menu;
use App\Models\ChildMenu;
class SubscriptionPlanFeature extends Model
{
    protected $guarded = [];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function childMenu(): BelongsTo
    {
        return $this->belongsTo(ChildMenu::class, 'child_menu_id');
    }
}
