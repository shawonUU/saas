<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use App\Services\BusinessSubscriptionService;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = app(BusinessSubscriptionService::class);
        BusinessCategory::query()
            ->where('approval_status', 'Approved')
            ->where('is_active', true)
            ->orderBy('id')
            ->chunkById(50, function ($categories) use ($subscriptions) {
                foreach ($categories as $category) {
                    $subscriptions->syncBasicPlan($category);
                }
            });
    }
}
