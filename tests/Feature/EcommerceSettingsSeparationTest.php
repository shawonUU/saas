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
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceSettingsSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ecommerce_menu_is_enabled_for_existing_entitled_plan_without_changing_brand_records(): void
    {
        [$business, $owner] = $this->tenant('split-nav');
        $brand = BrandSetting::create(['business_id' => $business->id, 'primary_color' => '#ad4567',
            'tagline' => 'Existing tagline', 'about' => 'Company info']);

        $migration = require database_path('migrations/2026_11_28_150000_register_settings_ecommerce_general_settings.php');
        $migration->up();
        $migration->up(); // Idempotent.
        $module = Module::where('slug', 'settings')->firstOrFail();
        $menu = Menu::where('module_id', $module->id)->where('slug', 'ecommerce')->sole();
        $child = ChildMenu::where('menu_id', $menu->id)->where('slug', 'general-settings')->sole();
        $navigation = app(BusinessNavigation::class)->forBusiness($business, $owner);

        $this->assertTrue($navigation->contains(fn ($item) => $item->id === $module->id
            && $item->menus->contains(fn ($row) => $row->id === $menu->id && $row->children->contains('id', $child->id))));
        $this->assertDatabaseCount('business_brand_settings', 1);
        $this->assertSame('#ad4567', $brand->fresh()->primary_color);
    }

    public function test_ecommerce_form_reads_existing_branding_and_business_form_does_not_expose_storefront_fields(): void
    {
        [$business, $owner] = $this->tenant('split-forms');
        $this->enableSettings();
        BrandSetting::create(['business_id' => $business->id, 'primary_color' => '#123456',
            'tagline' => 'Old line', 'about' => 'Existing about', 'banner' => 'banner-old.jpg']);

        $this->actingAs($owner, 'business_admin')->get($this->ecommerceUrl($business))
            ->assertOk()->assertViewIs('public.business.admin.settings.ecommerce.general-settings.index')
            ->assertSee('name="primary_color"', false)->assertSee('Old line');
        $this->get($this->businessUrl($business))->assertOk()
            ->assertDontSee('name="primary_color"', false)
            ->assertDontSee('name="banner"', false)
            ->assertDontSee('name="tagline"', false)
            ->assertSee('Existing about');
    }

    public function test_business_update_cannot_change_storefront_fields_but_can_update_about(): void
    {
        [$business, $owner] = $this->tenant('split-business');
        $this->enableSettings();
        BrandSetting::create(['business_id' => $business->id, 'primary_color' => '#112233',
            'tagline' => 'Keep me', 'about' => 'Old about']);

        $this->actingAs($owner, 'business_admin')->put($this->businessUrl($business), [
            'name' => 'New company name', 'about' => 'Updated about',
            'primary_color' => '#999999', 'tagline' => 'Do not use',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('New company name', $business->fresh()->name);
        $this->assertDatabaseHas('business_brand_settings', ['business_id' => $business->id,
            'primary_color' => '#112233', 'tagline' => 'Keep me', 'about' => 'Updated about']);
    }

    public function test_ecommerce_update_changes_only_storefront_branding_for_current_tenant(): void
    {
        [$business, $owner] = $this->tenant('split-ecommerce');
        [$otherBusiness] = $this->tenant('split-other');
        $this->enableSettings();
        BrandSetting::create(['business_id' => $business->id, 'primary_color' => '#112233',
            'tagline' => 'Old slogan', 'about' => 'Keep about', 'banner' => 'old-banner.jpg']);
        BrandSetting::create(['business_id' => $otherBusiness->id, 'primary_color' => '#445566', 'tagline' => 'Other company']);

        $this->actingAs($owner, 'business_admin')->put($this->ecommerceUrl($business), [
            'primary_color' => '#aabbcc', 'tagline' => 'New slogan', 'about' => 'Malicious override',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('business_brand_settings', ['business_id' => $business->id,
            'primary_color' => '#aabbcc', 'tagline' => 'New slogan', 'about' => 'Keep about', 'banner' => 'old-banner.jpg']);
        $this->assertDatabaseHas('business_brand_settings', ['business_id' => $otherBusiness->id,
            'primary_color' => '#445566', 'tagline' => 'Other company']);
    }

    public function test_business_settings_permission_does_not_grant_ecommerce_edit_permission(): void
    {
        [$business] = $this->tenant('split-staff');
        $this->enableSettings();
        $settings = Module::where('slug', 'settings')->firstOrFail();
        $businessMenu = Menu::where('module_id', $settings->id)->where('slug', 'business')->firstOrFail();
        $businessChild = ChildMenu::where('menu_id', $businessMenu->id)->where('slug', 'general-settings')->firstOrFail();
        $user = User::create(['name' => 'Staff', 'email' => 'staff@split.local', 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Staff', 'approval_status' => 'Approved']);
        $staff = BusinessUser::create(['user_id' => $user->id, 'business_id' => $business->id,
            'role_id' => $role->id, 'name' => 'Staff', 'email' => $user->email,
            'password' => 'test-password-123', 'approval_status' => 'Approved']);
        BusinessUserAccess::create(['business_user_id' => $staff->id, 'module_id' => $settings->id,
            'menu_id' => $businessMenu->id, 'child_menu_id' => $businessChild->id,
            'can_view' => true, 'can_update' => true, 'is_active' => true, 'approval_status' => 'Approved']);

        $this->actingAs($staff, 'business_admin')->get($this->businessUrl($business))->assertOk();
        $this->get($this->ecommerceUrl($business))->assertForbidden();
        $this->putJson($this->ecommerceUrl($business), ['primary_color' => '#abcdef'])->assertForbidden();
    }

    private function enableSettings(): void
    {
        $migration = require database_path('migrations/2026_11_28_150000_register_settings_ecommerce_general_settings.php');
        $migration->up();
    }

    private function tenant(string $domain): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Retail', 'slug' => $domain, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $owner->id, 'category_id' => $category->id,
            'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['user_id' => $owner->id, 'business_id' => $business->id,
            'role_id' => $role->id, 'name' => 'Owner', 'email' => $owner->email,
            'password' => 'test-password-123', 'approval_status' => 'Approved']);
        $settings = Module::firstOrCreate(['slug' => 'settings'], ['name' => 'Settings', 'approval_status' => 'Approved']);
        $ecommerce = Module::firstOrCreate(['slug' => 'ecommerce'], ['name' => 'Ecommerce', 'approval_status' => 'Approved']);
        $menu = Menu::firstOrCreate(['module_id' => $settings->id, 'slug' => 'business'], ['name' => 'Business', 'approval_status' => 'Approved']);
        $child = ChildMenu::firstOrCreate(['menu_id' => $menu->id, 'slug' => 'general-settings'], ['name' => 'General Settings', 'approval_status' => 'Approved']);
        foreach ([$settings, $ecommerce] as $module) {
            BusinessCategoryModule::create(['business_category_id' => $category->id,
                'module_id' => $module->id, 'approval_status' => 'Approved']);
        }
        BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $menu->id, 'approval_status' => 'Approved']);
        BusinessCategoryChildMenu::create(['business_category_id' => $category->id, 'child_menu_id' => $child->id, 'approval_status' => 'Approved']);
        app(BusinessSubscriptionService::class)->activateBasic($business);

        return [$business, $admin];
    }

    private function ecommerceUrl(Business $business): string
    {
        return 'http://admin.'.$business->domain.'.saas.test/settings/ecommerce/general-settings';
    }

    private function businessUrl(Business $business): string
    {
        return 'http://admin.'.$business->domain.'.saas.test/settings/business/general-settings';
    }
}
