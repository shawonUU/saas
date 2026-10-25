<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppMenu extends Model
{
    protected $table = 'app_menus';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(AppModule::class, 'app_module_id');
    }

    public function childMenus(): HasMany
    {
        return $this->hasMany(AppChildMenu::class, 'app_menu_id')
            ->orderBy('sort_order');
    }
}