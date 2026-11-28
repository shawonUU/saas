<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\BusinessUserAccess;
use App\Models\ChildMenu;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Public\Business\Admin\Settings\Business\GeneralSettings\BrandSetting;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantWorkflow;
use App\Services\Public\Business\Admin\Sales\Settings\PosSettings\PosSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RestaurantPosSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_shows_quick_sale_defaults_without_writing_them(): void
    {
        [$business, $owner] = $this->tenant('quick-settings');

        $this->actingAs($owner, 'business_admin')->get($this->url($business))
            ->assertOk()
            ->assertViewIs('public.business.admin.sales.settings.pos-settings.index')
            ->assertViewHas('restaurantPosSettings', ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'])
            ->assertSee('Kitchen tracking')
            ->assertSee('Send to kitchen when billing')
            ->assertSee('Changes apply to new orders only.');

        $this->assertDatabaseCount('restaurant_pos_settings', 0);
    }

    public function test_owner_saves_restaurant_settings_separately_without_changing_branding_or_another_tenant(): void
    {
        [$business, $owner] = $this->tenant('save-settings');
        [$otherBusiness] = $this->tenant('untouched-settings');
        $settings = ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'default_service' => 'dine_in'];

        $this->actingAs($owner, 'business_admin')->from($this->url($business))
            ->put($this->url($business), $this->input(['restaurant_pos' => $settings]))
            ->assertRedirect($this->url($business))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id] + $settings);
        $this->assertDatabaseMissing('restaurant_pos_settings', ['business_id' => $otherBusiness->id]);
        $this->assertNull($business->fresh()->restaurant_pos_settings);
        $this->assertNull($otherBusiness->fresh()->restaurant_pos_settings);
        $this->assertSame('save-settings', $business->fresh()->name);
        $this->assertDatabaseCount('business_brand_settings', 0);

        $settings['default_service'] = 'delivery';
        $this->put($this->url($business), $this->input(['restaurant_pos' => $settings]))
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id] + $settings);
        $this->assertDatabaseCount('restaurant_pos_settings', 1);
    }

    public function test_kitchen_off_also_disables_automatic_sending(): void
    {
        [$business, $owner] = $this->tenant('kitchen-off');

        $this->actingAs($owner, 'business_admin')->put($this->url($business), $this->input([
            'restaurant_pos' => ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => '1', 'default_service' => 'delivery'],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id, 'kitchen_mode' => 'off', 'auto_send_to_kitchen' => false, 'default_service' => 'delivery']);
    }

    public function test_general_settings_no_longer_exposes_or_writes_restaurant_policy_and_preserves_branding(): void
    {
        [$business, $owner] = $this->tenant('brand-only');
        $settings = ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'default_service' => 'dine_in'];
        app(PosSettingsService::class)->save($business, $settings);

        BrandSetting::create(['business_id' => $business->id, 'primary_color' => '#5145e5', 'about' => 'Original about']);
        $this->actingAs($owner, 'business_admin')->get($this->generalSettingsUrl($business))
            ->assertOk()->assertDontSee('name="restaurant_pos[kitchen_mode]"', false)
            ->assertDontSee('name="primary_color"', false);
        $this->put($this->generalSettingsUrl($business), [
            'name' => 'Updated restaurant', 'about' => 'Updated about', 'primary_color' => '#ff0000',
            'restaurant_pos' => ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => false, 'default_service' => 'delivery'],
        ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $business->refresh();
        $this->assertSame('Updated restaurant', $business->name);
        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id] + $settings);
        $this->assertDatabaseHas('business_brand_settings', ['business_id' => $business->id,
            'primary_color' => '#5145e5', 'about' => 'Updated about']);
        $this->assertNull($business->restaurant_pos_settings);
    }

    #[DataProvider('invalidSettings')]
    public function test_invalid_or_unrecognized_restaurant_settings_are_rejected_without_partial_writes(array $settings, string $field, string $message): void
    {
        [$business, $owner] = $this->tenant('invalid-policy');

        $response = $this->actingAs($owner, 'business_admin')->putJson($this->url($business), $this->input(['restaurant_pos' => $settings]));

        $response->assertUnprocessable()->assertJsonValidationErrors([$field => $message]);
        $this->assertDatabaseCount('restaurant_pos_settings', 0);
        $this->assertSame('invalid-policy', $business->fresh()->name);
        $this->assertDatabaseCount('business_brand_settings', 0);
    }

    public static function invalidSettings(): array
    {
        $valid = ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'];

        return [
            'unknown kitchen mode' => [array_replace($valid, ['kitchen_mode' => 'skip-all']), 'restaurant_pos.kitchen_mode', 'Choose Off, Optional or Required for kitchen tracking.'],
            'invalid automatic send flag' => [array_replace($valid, ['auto_send_to_kitchen' => 'yes']), 'restaurant_pos.auto_send_to_kitchen', 'Choose whether billing sends items to the kitchen.'],
            'unknown default service' => [array_replace($valid, ['default_service' => 'counter-custom']), 'restaurant_pos.default_service', 'Choose Counter / Takeaway, Dine-in or Delivery as the default service.'],
            'unexpected client policy key' => [$valid + ['table_release' => 'automatic'], 'restaurant_pos', 'Choose only the available restaurant POS settings.'],
            'empty settings object' => [[], 'restaurant_pos', 'Choose the kitchen mode, automatic kitchen sending and default service.'],
        ];
    }

    public function test_settings_editor_can_update_restaurant_policy_without_cashier_access(): void
    {
        [$business, $owner, $module, $menu, $child] = $this->tenant('settings-editor');
        $staff = $this->staff($business, $module, $menu, $child, true);

        $this->actingAs($staff, 'business_admin')->get($this->url($business))
            ->assertOk()->assertViewIs('public.business.admin.sales.settings.pos-settings.index');
        $this->put($this->url($business), $this->input([
            'restaurant_pos' => ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'dine_in'],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id, 'default_service' => 'dine_in']);
    }

    public function test_view_only_settings_staff_cannot_change_restaurant_policy(): void
    {
        [$business, $owner, $module, $menu, $child] = $this->tenant('readonly-settings');
        $staff = $this->staff($business, $module, $menu, $child, false);

        $this->actingAs($staff, 'business_admin')->get($this->url($business))
            ->assertOk()->assertViewHas('permissions', ['create' => false, 'update' => false, 'delete' => false]);
        $this->putJson($this->url($business), $this->input([
            'restaurant_pos' => ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'],
        ]))->assertForbidden();

        $this->assertDatabaseCount('restaurant_pos_settings', 0);
        $this->assertDatabaseCount('business_brand_settings', 0);
    }

    public function test_another_tenant_and_unauthenticated_requests_cannot_change_settings(): void
    {
        [$business] = $this->tenant('private-settings');
        [$otherBusiness, $otherOwner] = $this->tenant('foreign-settings');
        $input = $this->input(['restaurant_pos' => ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway']]);

        $this->putJson($this->url($business), $input)->assertUnauthorized();
        session()->flush();
        $this->actingAs($otherOwner, 'business_admin')->putJson($this->url($business), $input)->assertForbidden();

        $this->assertDatabaseCount('restaurant_pos_settings', 0);
        $this->assertDatabaseCount('business_brand_settings', 0);
    }

    public function test_retail_keeps_its_existing_pos_settings_placeholder_and_cannot_set_restaurant_policy(): void
    {
        [$business, $owner] = $this->tenant('retail-settings', false);

        $this->actingAs($owner, 'business_admin')->get($this->url($business))
            ->assertOk()->assertViewIs('public.business.admin.feature')->assertDontSee('name="restaurant_pos[kitchen_mode]"', false);
        $this->putJson($this->url($business), $this->input([
            'restaurant_pos' => ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'],
        ]))->assertForbidden();
        $this->put($this->generalSettingsUrl($business), ['name' => 'Retail name', 'primary_color' => '#5145e5'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('restaurant_pos_settings', 0);
        $this->assertSame('Retail name', $business->fresh()->name);
    }

    public function test_general_settings_permission_does_not_grant_sales_pos_settings_access(): void
    {
        [$business] = $this->tenant('general-editor');
        $module = Module::where('slug', 'settings')->firstOrFail();
        $menu = Menu::where('module_id', $module->id)->where('slug', 'business')->firstOrFail();
        $child = ChildMenu::where('menu_id', $menu->id)->where('slug', 'general-settings')->firstOrFail();
        $staff = $this->staff($business, $module, $menu, $child, true);

        $this->actingAs($staff, 'business_admin')->get($this->generalSettingsUrl($business))->assertOk();
        $this->get($this->url($business))->assertForbidden();
        $this->putJson($this->url($business), $this->input())->assertForbidden();

        $this->assertDatabaseCount('restaurant_pos_settings', 0);
    }

    public function test_runtime_reads_only_the_dedicated_table_even_when_legacy_json_changes(): void
    {
        [$business] = $this->tenant('sole-settings-source');
        $business->update(['restaurant_pos_settings' => ['kitchen_mode' => 'off', 'default_service' => 'delivery']]);
        $workflow = app(RestaurantWorkflow::class);

        $this->assertSame(['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'], $workflow->settings($business));

        $settings = ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'default_service' => 'dine_in'];
        app(PosSettingsService::class)->save($business, $settings);
        $business->update(['restaurant_pos_settings' => ['kitchen_mode' => 'optional', 'default_service' => 'takeaway']]);

        $this->assertSame($settings, $workflow->settings($business));
        $this->assertSame(['version' => 2, 'kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'table_release' => 'manual'], $workflow->snapshot($business));
    }

    public function test_migration_copies_legacy_settings_and_reruns_preserve_newer_settings(): void
    {
        [$business] = $this->tenant('migrate-settings');
        [$offBusiness] = $this->tenant('migrate-off-settings');
        [$invalidBusiness] = $this->tenant('migrate-invalid-settings');
        $legacy = ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => true, 'default_service' => 'dine_in'];
        $business->update(['restaurant_pos_settings' => $legacy]);
        $offBusiness->update(['restaurant_pos_settings' => ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => true, 'default_service' => 'delivery']]);
        $invalidBusiness->update(['restaurant_pos_settings' => ['kitchen_mode' => 'unknown', 'auto_send_to_kitchen' => 'yes', 'default_service' => 'invalid']]);
        $migration = require database_path('migrations/2026_11_26_040824_create_restaurant_pos_settings_table.php');

        $migration->up();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id] + $legacy);
        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $offBusiness->id, 'kitchen_mode' => 'off', 'auto_send_to_kitchen' => false, 'default_service' => 'delivery']);
        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $invalidBusiness->id, 'kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway']);
        $this->assertSame($legacy, $business->fresh()->restaurant_pos_settings);

        $updated = ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway'];
        app(PosSettingsService::class)->save($business, $updated);
        $migration->up();
        $migration->down();

        $this->assertDatabaseHas('restaurant_pos_settings', ['business_id' => $business->id] + $updated);
        $this->assertDatabaseCount('restaurant_pos_settings', 3);
        $this->assertSame($legacy, $business->fresh()->restaurant_pos_settings);
    }

    private function tenant(string $domain, bool $restaurant = true): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => $restaurant ? 'Restaurant' : 'Retail', 'slug' => $domain, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['user_id' => $owner->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Owner', 'email' => $owner->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        $module = Module::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales', 'approval_status' => 'Approved']);
        $menu = Menu::firstOrCreate(['module_id' => $module->id, 'slug' => 'settings'], ['name' => 'Settings', 'approval_status' => 'Approved']);
        $child = ChildMenu::firstOrCreate(['menu_id' => $menu->id, 'slug' => 'pos-settings'], ['name' => 'POS Settings', 'approval_status' => 'Approved']);
        BusinessCategoryModule::create(['business_category_id' => $category->id, 'module_id' => $module->id, 'approval_status' => 'Approved']);
        BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $menu->id, 'approval_status' => 'Approved']);
        BusinessCategoryChildMenu::create(['business_category_id' => $category->id, 'child_menu_id' => $child->id, 'approval_status' => 'Approved']);
        $generalModule = Module::firstOrCreate(['slug' => 'settings'], ['name' => 'Settings', 'approval_status' => 'Approved']);
        $generalMenu = Menu::firstOrCreate(['module_id' => $generalModule->id, 'slug' => 'business'], ['name' => 'Business', 'approval_status' => 'Approved']);
        $generalChild = ChildMenu::firstOrCreate(['menu_id' => $generalMenu->id, 'slug' => 'general-settings'], ['name' => 'General Settings', 'approval_status' => 'Approved']);
        BusinessCategoryModule::create(['business_category_id' => $category->id, 'module_id' => $generalModule->id, 'approval_status' => 'Approved']);
        BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $generalMenu->id, 'approval_status' => 'Approved']);
        BusinessCategoryChildMenu::create(['business_category_id' => $category->id, 'child_menu_id' => $generalChild->id, 'approval_status' => 'Approved']);
        if ($restaurant) {
            $pos = Menu::firstOrCreate(['module_id' => $module->id, 'slug' => 'restaurant-pos'], ['name' => 'Restaurant POS', 'approval_status' => 'Approved']);
            BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $pos->id, 'approval_status' => 'Approved']);
        }
        app(BusinessSubscriptionService::class)->activateBasic($business);

        return [$business, $admin, $module, $menu, $child];
    }

    private function staff(Business $business, Module $module, Menu $menu, ChildMenu $child, bool $canUpdate): BusinessUser
    {
        $global = User::create(['name' => 'Editor', 'email' => 'editor@'.$business->domain.'.test', 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Settings staff', 'is_owner' => false, 'approval_status' => 'Approved']);
        $staff = BusinessUser::create(['user_id' => $global->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Editor', 'email' => $global->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        BusinessUserAccess::create(['business_user_id' => $staff->id, 'module_id' => $module->id, 'menu_id' => $menu->id, 'child_menu_id' => $child->id, 'can_view' => true, 'can_create' => false, 'can_update' => $canUpdate, 'approval_status' => 'Approved']);

        return $staff;
    }

    private function url(Business $business): string
    {
        return 'http://admin.'.$business->domain.'.saas.test/sales/settings/pos-settings';
    }

    private function generalSettingsUrl(Business $business): string
    {
        return 'http://admin.'.$business->domain.'.saas.test/settings/business/general-settings';
    }

    private function input(array $overrides = []): array
    {
        return array_replace(['restaurant_pos' => ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway']], $overrides);
    }
}
