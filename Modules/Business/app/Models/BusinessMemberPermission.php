<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Business\App\Models\AppModule;
use Modules\Business\App\Models\BusinessMember;
// use App\Models\AppModule;
class BusinessMemberPermission extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(BusinessMember::class, 'business_member_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(AppModule::class, 'app_module_id');
    }
}
