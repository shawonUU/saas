<?php

namespace Modules\Branch\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\App\Models\Business;

class Branch extends Model
{
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
