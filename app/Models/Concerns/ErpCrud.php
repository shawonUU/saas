<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 * @method static void creating(callable $callback)
 * @method static void updating(callable $callback)
 * @method static void deleting(callable $callback)
 */
trait ErpCrud
{
    protected static function bootErpCrud(): void
    {
        static::creating(function (Model $model) {
            if (blank($model->getAttribute('code'))) $model->setAttribute('code', getGenerateCode(get_class($model), 'code', erpCodePrefix($model->getTable())));
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
            $model->created_ip = request()->ip();
            $model->updated_ip = request()->ip();
            $model->created_user_agent = request()->userAgent();
            $model->updated_user_agent = request()->userAgent();
        });

        static::updating(function (Model $model) {
            $model->updated_by = Auth::id();
            $model->updated_ip = request()->ip();
            $model->updated_user_agent = request()->userAgent();
        });

        static::deleting(function (Model $model) {
            $model->deleted_by = Auth::id();
            $model->deleted_ip = request()->ip();

            if (in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
                $model->saveQuietly();
            }
        });
    }
}