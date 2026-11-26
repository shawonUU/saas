<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('restaurant_pos_settings')) {
            Schema::create('restaurant_pos_settings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('business_id')->unique();
                $table->string('kitchen_mode', 16)->default('optional');
                $table->boolean('auto_send_to_kitchen')->default(false);
                $table->string('default_service', 20)->default('takeaway');
                $table->erpCommonColumns();
            });
        }

        if (! Schema::hasColumn('businesses', 'restaurant_pos_settings')) {
            return;
        }

        DB::table('businesses')->select(['id', 'restaurant_pos_settings'])
            ->whereNotNull('restaurant_pos_settings')->chunkById(200, function (Collection $businesses): void {
                foreach ($businesses as $business) {
                    $legacy = json_decode($business->restaurant_pos_settings, true);
                    $settings = is_array($legacy) ? $legacy : [];
                    $mode = in_array($settings['kitchen_mode'] ?? null, ['off', 'optional', 'required'], true)
                        ? $settings['kitchen_mode'] : 'optional';

                    DB::table('restaurant_pos_settings')->insertOrIgnore([
                        'business_id' => $business->id,
                        'code' => 'RESTAURANT-POS-SETTINGS-'.$business->id,
                        'kitchen_mode' => $mode,
                        'auto_send_to_kitchen' => $mode !== 'off' && in_array($settings['auto_send_to_kitchen'] ?? false, [true, 1, '1'], true),
                        'default_service' => in_array($settings['default_service'] ?? null, ['takeaway', 'dine_in', 'delivery'], true)
                            ? $settings['default_service'] : 'takeaway',
                        'is_active' => true,
                        'approval_status' => 'Approved',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep saved settings and the legacy JSON backup intact during a rollback.
    }
};
