<?php
namespace Database\Seeders;
use App\Models\ShopCategory;
use Illuminate\Database\Seeder;
class ShopCategorySeeder extends Seeder
{
    public function run(): void
    {
        ShopCategory::firstOrCreate(['code' => 'general'], ['name' => 'General', 'approval_status' => 'Approved']);
    }
}
