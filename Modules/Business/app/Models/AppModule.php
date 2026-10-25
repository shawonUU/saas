<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Business\App\Models\BusinessCategoryModule;
use Modules\Business\App\Models\BusinessMemberPermission;

// use App\Models\BusinessCategoryModule;
// use App\Models\BusinessMemberPermission;
class AppModule extends Model
{
    protected $table = 'app_modules';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function categoryModules(): HasMany
    {
        return $this->hasMany(BusinessCategoryModule::class, 'app_module_id');
    }

    public function memberPermissions(): HasMany
    {
        return $this->hasMany(BusinessMemberPermission::class, 'app_module_id');
    }


    public function menus(): HasMany
    {
        return $this->hasMany(AppMenu::class, 'app_module_id')
            ->orderBy('sort_order');
    }
}
