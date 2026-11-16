<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\BusinessSubscription;
use App\Models\Module;
use App\Models\Menu;
use App\Models\ChildMenu;
class BusinessSubscriptionFeature extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            BusinessSubscription::class,
            'business_subscription_id'
        );
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
