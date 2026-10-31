
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_member_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_member_id');
            $table->unsignedBigInteger('app_menu_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->foreign('business_member_id', 'bmmp_member_fk')
                ->references('id')->on('business_members')->cascadeOnDelete();

            $table->foreign('app_menu_id', 'bmmp_menu_fk')
                ->references('id')->on('app_menus')->cascadeOnDelete();

            $table->unique(['business_member_id', 'app_menu_id'], 'bm_menu_unique');
        });

        Schema::create('business_member_child_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_member_id');
            $table->unsignedBigInteger('app_child_menu_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->foreign('business_member_id', 'bmcmp_member_fk')
                ->references('id')->on('business_members')->cascadeOnDelete();

            $table->foreign('app_child_menu_id', 'bmcmp_child_fk')
                ->references('id')->on('app_child_menus')->cascadeOnDelete();

            $table->unique(
                ['business_member_id', 'app_child_menu_id'],
                'bm_child_menu_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_member_child_menu_permissions');
        Schema::dropIfExists('business_member_menu_permissions');
    }
};
