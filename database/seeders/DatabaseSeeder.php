<?php
namespace Database\Seeders;
use Database\Seeders\ModuleMenuSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // $this->call([BusinessCategorySeeder::class, ModuleMenuSeeder::class]);
        $this->call([ModuleMenuSeeder::class, SubscriptionPlanSeeder::class,]);
    }
}
