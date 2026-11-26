<?php

namespace App\Models\Public\Business\Admin\Configurations\Modifiers;

use App\Models\Configurations\BaseConfiguration;
use App\Models\Configurations\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modifier extends BaseConfiguration
{
    protected $table = 'config_modifiers';

    protected function casts(): array
    {
        return parent::casts() + ['price' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
