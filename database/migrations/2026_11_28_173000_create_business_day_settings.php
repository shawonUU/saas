<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_day_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->unique();
            $table->boolean('manual_mode')->default(false);
            $table->date('open_date');
            $table->string('timezone', 64)->default('Asia/Dhaka');
            $table->time('alert_start_time')->default('02:00:00');
            $table->time('closing_deadline')->default('04:00:00');
            $table->timestamps();
        });
        Schema::create('business_day_closures', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->date('closed_business_date');
            $table->date('next_business_date');
            $table->timestamp('closed_at');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->string('closed_by_name', 191)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'closed_business_date'], 'business_day_unique_close');
            $table->index(['business_id','closed_at']);
        });
        foreach (DB::table('businesses')->select('id')->cursor() as $business) {
            DB::table('business_day_settings')->insertOrIgnore([
                'business_id' => $business->id, 'manual_mode' => false,
                'open_date' => now('Asia/Dhaka')->toDateString(), 'timezone' => 'Asia/Dhaka',
                'alert_start_time' => '02:00:00', 'closing_deadline' => '04:00:00',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Explicit rollback destroys day-close history; never call on production without export.
        Schema::dropIfExists('business_day_closures');
        Schema::dropIfExists('business_day_settings');
    }
};
