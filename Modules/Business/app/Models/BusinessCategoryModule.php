<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\App\Models\AppModule;

class BusinessCategoryModule extends Model
{
    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(AppModule::class, 'app_module_id');
    }
}
