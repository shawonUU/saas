<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Existing SQLite database was migrated before users.code and users.approval_status were added to the original migration.
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'code')) Schema::table('users', fn (Blueprint $table) => $table->string('code')->nullable());
            if (!Schema::hasColumn('users', 'approval_status')) Schema::table('users', fn (Blueprint $table) => $table->string('approval_status')->default('Pending'));
        }
        Schema::table('business_users', function (Blueprint $table) {
            $table->string('role', 20)->default('Employee');
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->unique('code');
            $table->index('owner_id');
        });
    }
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropIndex(['owner_id']);
        });
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn(['role', 'user_id']);
        });
        // Do not remove legacy users columns: other environments may have them from the original migration.
    }
};
