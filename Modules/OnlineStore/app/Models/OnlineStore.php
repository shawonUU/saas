<?php

namespace Modules\OnlineStore\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\App\Models\Business;
// use Modules\OnlineStore\Database\Factories\OnlineStoreFactory;

class OnlineStore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    // protected static function newFactory(): OnlineStoreFactory
    // {
    //     // return OnlineStoreFactory::new();
    // }
}
