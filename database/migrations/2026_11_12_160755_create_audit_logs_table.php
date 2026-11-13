<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('actor_id', 100)->nullable();
            $table->string('actor_type', 191)->nullable();
            $table->string('actor_name', 191)->nullable();
            $table->string('actor_guard', 50)->nullable();
            $table->string('action', 30);
            $table->string('subject_type', 191); // model class name
            $table->string('subject_table', 100); // table name
            $table->string('subject_id', 100)->nullable(); // model primary key
            $table->string('subject_code', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 80)->nullable();
            $table->string('operating_system', 80)->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('hostname', 191)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('route_name', 191)->nullable();
            $table->string('request_path', 1024)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['actor_type', 'actor_id'], 'idx_actor');
            $table->index(['subject_type', 'subject_id'], 'idx_subject');
            $table->index(['business_id', 'created_at'], 'idx_business_date');
            $table->index(['company_id', 'created_at'], 'idx_company_date');
            $table->index(['action', 'created_at'], 'idx_action_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};