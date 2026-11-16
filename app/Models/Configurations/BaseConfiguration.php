<?php

namespace App\Models\Configurations;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Shared model behaviors only. Each configuration has its own model and table. */
abstract class BaseConfiguration extends Model
{
    use ErpCrud, SoftDeletes;

    protected $guarded = [];
    protected $attributes = ['is_active' => 1, 'approval_status' => 'Approved'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_default' => 'boolean',
            'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
