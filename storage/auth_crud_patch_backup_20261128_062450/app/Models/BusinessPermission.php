<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPermission extends Model
{
    protected static function booted(): void {
        static::creating(function ($model) {
            if (blank($model->code)) $model->code = getGenerateCode(static::class, 'code', 'BP');
        });
    }
}
