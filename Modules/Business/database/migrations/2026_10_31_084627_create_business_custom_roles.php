
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['business_id', 'slug'], 'br_business_slug_uq');
            $table->index('business_id');
        });

        Schema::create('business_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_role_id');
            $table->string('permission_type', 20);
            $table->unsignedBigInteger('permission_item_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(
                ['business_role_id', 'permission_type', 'permission_item_id'],
                'brp_role_type_item_uq'
            );
            $table->index('business_role_id');
        });

        Schema::table('business_members', function (Blueprint $table) {
            $table->unsignedBigInteger('business_role_id')
                ->nullable()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('business_members', function (Blueprint $table) {
            $table->dropIndex(['business_role_id']);
            $table->dropColumn('business_role_id');
        });
        Schema::dropIfExists('business_role_permissions');
        Schema::dropIfExists('business_roles');
    }
};
