<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUserPermission extends Model
{
    protected static function booted(): void {
        static::creating(function ($model) {
            if (blank($model->code)) $model->code = getGenerateCode(static::class, 'code', 'BUP');
        });
    }
}
