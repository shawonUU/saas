<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppChildMenu extends Model
{
    protected $table = 'app_child_menus';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(AppMenu::class, 'app_menu_id');
    }
}