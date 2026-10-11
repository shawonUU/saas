<?php

namespace Modules\Business\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Business\App\Models\BusinessCategory;

class BusinessCategorySeeder extends Seeder
{
    public function run(): void
    {
        BusinessCategory::updateOrCreate(
            ['slug' => 'retail-general-store'],
            [
                'name' => 'Retail / General Store',
                'is_active' => true,
            ]
        );
    }
}
