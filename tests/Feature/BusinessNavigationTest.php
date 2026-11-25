<?php
namespace Tests\Feature;
use App\Models\{User,Business,BusinessUser,BusinessRole,BusinessCategory,Module,Menu,ChildMenu,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu,BusinessChildMenu,BusinessUserAccess,AuthorityUser};
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use App\Services\BusinessSubscriptionService;
use App\Models\{BusinessSubscription, SubscriptionPlanModule, SubscriptionPlanMenu, SubscriptionPlanChildMenu, BusinessModule};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class BusinessNavigationTest extends TestCase
{
    use RefreshDatabase;
    private function setupBusiness(): array
    {
        $owner = User::create(['name' => 'Owner','email' => 'owner@test.local','approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Retail','slug' => 'retail-test','approval_status' => 'Approved']);
        $business = Business::create(['name' => 'Shop A','domain'=>'shopa','code' => 'shopa','owner_id' => $owner->id,'category_id' => $category->id,'approval_status' => 'Approved']);
        $role = BusinessRole::create(['name' => 'Owner','business_id' => $business->id,'is_owner' => true,'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['name' => 'Owner','business_id' => $business->id,'user_id' => $owner->id,'role_id' => $role->id,'email' => 'owner@test.local','password' => '123456789','approval_status' => 'Approved']);
        $module = Module::create(['name' => 'Inventory','slug' => 'inventory-test','approval_status' => 'Approved']);
        $menu = Menu::create(['name' => 'Products','slug' => 'products-test','module_id' => $module->id,'approval_status' => 'Approved']);
        $child = ChildMenu::create(['name' => 'Product List','slug' => 'product-list-test','menu_id' => $menu->id,'approval_status' => 'Approved']);
        foreach ([[BusinessCategoryModule::class,'module_id',$module->id],[BusinessCategoryMenu::class,'menu_id',$menu->id],[BusinessCategoryChildMenu::class,'child_menu_id',$child->id]] as [$model,$column,$id]) {
            $model::create(['business_category_id' => $category->id,$column => $id]);
        }
        app(BusinessSubscriptionService::class)->activateBasic($business);
        return compact('business','admin','module','menu','child');
    }
    public function test_owner_automatically_gets_category_access_and_override_blocks_it(): void
    {
        extract($this->setupBusiness());
        $service = app(BusinessNavigation::class);
        $this->assertTrue($service->canView($business,$admin,$child->id));
        BusinessChildMenu::where('business_id', $business->id)->where('child_menu_id', $child->id)->update(['source' => 'Override', 'is_enabled' => false]);
        $this->assertFalse($service->canView($business,$admin,$child->id));
    }
    public function test_employee_does_not_inherit_owner_permission(): void
    {
        extract($this->setupBusiness());
        $role = BusinessRole::create(['name'=>'Staff','business_id'=>$business->id,'approval_status'=>'Approved']);
        $employeeGlobal = User::create(['name'=>'Staff','email'=>'staff@test.local','approval_status'=>'Approved']);
        $employee = BusinessUser::create(['user_id'=>$employeeGlobal->id,'name'=>'Staff','business_id'=>$business->id,'role_id'=>$role->id,'email'=>'staff@test.local','password'=>'123456789','approval_status'=>'Approved']);
        $service = app(BusinessNavigation::class);
        $this->assertFalse($service->canView($business,$employee,$child->id));
        BusinessUserAccess::create(['business_user_id'=>$employee->id,'module_id'=>$module->id,'menu_id'=>$menu->id,'child_menu_id'=>$child->id,'can_view'=>true]);
        $this->assertTrue($service->canView($business,$employee,$child->id));
        $this->actingAs($employee->refresh(),'business_admin')->get('http://admin.shopa.saas.test/'.$module->slug.'/'.$menu->slug.'/'.$child->slug)->assertOk();
        BusinessChildMenu::where('business_id',$business->id)->where('child_menu_id',$child->id)->update(['source'=>'Override','is_enabled'=>false]);
        $this->get('http://admin.shopa.saas.test/'.$module->slug.'/'.$menu->slug.'/'.$child->slug)->assertNotFound();
    }
    public function test_only_authority_can_edit_category_access(): void
    {
        extract($this->setupBusiness());
        $this->get('http://admin.saas.test/access')->assertRedirect('http://admin.saas.test/login');
        $globalAuthority = User::create(['name'=>'HQ','email'=>'hq-global@test.local','approval_status'=>'Approved']);
        $authority = AuthorityUser::create(['user_id'=>$globalAuthority->id,'name'=>'HQ','email'=>'hq@test.local','password'=>'123456789','approval_status'=>'Approved']);
        $this->actingAs($authority->refresh(),'authority')->get('http://admin.saas.test/access')->assertOk();
        $this->put('http://admin.saas.test/access/category/'.$business->category_id,['access'=>['module'=>[$module->id=>'1'],'menu'=>[$menu->id=>'1'],'child'=>[]]])->assertRedirect();
        $this->assertFalse(app(BusinessNavigation::class)->canView($business,$admin,$child->id));
    }

    public function test_basic_activation_is_free_non_expiring_and_idempotent(): void
    {
        extract($this->setupBusiness());
        $subscription = BusinessSubscription::where('business_id', $business->id)->firstOrFail();
        $this->assertEquals('active', $subscription->subscription_status);
        $this->assertNull($subscription->ends_at);
        $this->assertEquals(0, (float)$subscription->plan->price);
        $this->assertEquals($business->category_id, $subscription->plan->business_category_id);
        $this->assertDatabaseHas('subscription_plan_modules', [
            'subscription_plan_id' => $subscription->plan_id, 'module_id' => $module->id, 'is_enabled' => true,
        ]);
        $this->assertDatabaseHas('subscription_plan_menus', [
            'subscription_plan_id' => $subscription->plan_id, 'menu_id' => $menu->id, 'is_enabled' => true,
        ]);
        $this->assertDatabaseHas('subscription_plan_child_menus', [
            'subscription_plan_id' => $subscription->plan_id, 'child_menu_id' => $child->id, 'is_enabled' => true,
        ]);

        app(BusinessSubscriptionService::class)->activateBasic($business);
        $this->assertEquals(1, BusinessSubscription::where('business_id', $business->id)->count());
        $this->assertDatabaseHas('subscription_plan_modules', [
            'subscription_plan_id' => $subscription->plan_id, 'module_id' => $module->id, 'is_enabled' => true,
        ]);
        $this->assertDatabaseHas('subscription_plan_menus', [
            'subscription_plan_id' => $subscription->plan_id, 'menu_id' => $menu->id, 'is_enabled' => true,
        ]);
        $this->assertDatabaseHas('subscription_plan_child_menus', [
            'subscription_plan_id' => $subscription->plan_id, 'child_menu_id' => $child->id, 'is_enabled' => true,
        ]);
    }

    public function test_subscription_is_required_even_for_owner(): void
    {
        extract($this->setupBusiness());
        $service = app(BusinessNavigation::class);
        $this->assertTrue($service->canView($business, $admin, $child->id));
        BusinessSubscription::where('business_id', $business->id)->update(['subscription_status' => 'canceled']);
        $this->assertFalse($service->canView($business, $admin, $child->id));
    }

    public function test_expired_subscription_hides_features(): void
    {
        extract($this->setupBusiness());
        BusinessSubscription::where('business_id', $business->id)->update(['ends_at' => now()->subDay()]);
        $this->assertFalse(app(BusinessNavigation::class)->canView($business, $admin, $child->id));
    }

    public function test_plan_restriction_cannot_be_overridden_by_business(): void
    {
        extract($this->setupBusiness());
        $service = app(BusinessNavigation::class);
        $this->assertTrue($service->canView($business, $admin, $child->id));
        $planId = BusinessSubscription::where('business_id', $business->id)->value('plan_id');
        SubscriptionPlanChildMenu::where('subscription_plan_id', $planId)
            ->where('child_menu_id', $child->id)->update(['is_enabled' => false]);
        $this->assertFalse($service->canView($business, $admin, $child->id));
        BusinessModule::where('business_id', $business->id)->where('module_id', $module->id)
            ->update(['source' => 'Override', 'is_enabled' => true, 'approval_status' => 'Approved']);
        $this->assertFalse($service->canView($business, $admin, $child->id));
    }

    public function test_global_inactive_parent_cannot_be_enabled_by_category_or_plan(): void
    {
        extract($this->setupBusiness());
        $module->update(['is_active' => false]);
        $this->assertFalse(app(BusinessNavigation::class)->canView($business, $admin, $child->id));
    }

    public function test_basic_plan_sync_updates_plan_mapping_when_category_changes(): void
    {
        extract($this->setupBusiness());
        $service = app(BusinessNavigation::class);
        $this->assertTrue($service->canView($business, $admin, $child->id));
        BusinessCategoryChildMenu::where('business_category_id', $business->category_id)
            ->where('child_menu_id', $child->id)->forceDelete();
        app(BusinessSubscriptionService::class)->syncBasicPlan($business->category);
        $this->assertFalse($service->canView($business, $admin, $child->id));
        $this->assertDatabaseMissing('subscription_plan_child_menus', [
            'subscription_plan_id' => BusinessSubscription::where('business_id', $business->id)->value('plan_id'),
            'business_category_id' => $business->category_id,
            'child_menu_id' => $child->id,
        ]);
    }

    public function test_configuration_module_is_assigned_to_categories_by_seeder(): void
    {
        $this->seed(\Database\Seeders\ModuleMenuSeeder::class);
        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
        $configurations = Module::where('slug', 'configurations')->firstOrFail();
        $categories = BusinessCategory::where('approval_status', 'Approved')->get();
        $this->assertGreaterThan(0, $categories->count());
        foreach ($categories as $category) {
            $this->assertDatabaseHas('business_category_modules', [
                'business_category_id' => $category->id, 'module_id' => $configurations->id,
            ]);
            $plan = \App\Models\SubscriptionPlan::where('slug', $category->slug . '-basic')->firstOrFail();
            $this->assertEquals(0, (float)$plan->price);
            $this->assertDatabaseHas('subscription_plan_modules', [
                'subscription_plan_id' => $plan->id,
                'business_category_id' => $category->id,
                'module_id' => $configurations->id,
            ]);
        }
    }

    public function test_navigation_does_not_require_business_feature_snapshots(): void
    {
        extract($this->setupBusiness());
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('business_subscription_features'));
        $this->assertTrue(app(BusinessNavigation::class)->canView($business, $admin, $child->id));
    }


    public function test_basic_plan_copies_only_the_existing_category_mapping(): void
    {
        extract($this->setupBusiness());
        $otherModule = Module::create(['name' => 'Unassigned', 'slug' => 'unassigned-test', 'approval_status' => 'Approved']);
        $otherMenu = Menu::create(['name' => 'Hidden', 'slug' => 'hidden-test',
            'module_id' => $otherModule->id, 'approval_status' => 'Approved']);
        $otherChild = ChildMenu::create(['name' => 'Hidden page', 'slug' => 'hidden-page-test',
            'menu_id' => $otherMenu->id, 'approval_status' => 'Approved']);
        $service = app(BusinessSubscriptionService::class);
        $plan = $service->syncBasicPlan($business->category);
        $this->assertDatabaseHas('subscription_plan_modules', ['subscription_plan_id' => $plan->id, 'module_id' => $module->id]);
        $this->assertDatabaseHas('subscription_plan_menus', ['subscription_plan_id' => $plan->id, 'menu_id' => $menu->id]);
        $this->assertDatabaseHas('subscription_plan_child_menus', ['subscription_plan_id' => $plan->id, 'child_menu_id' => $child->id]);
        $this->assertDatabaseMissing('subscription_plan_modules', ['subscription_plan_id' => $plan->id, 'module_id' => $otherModule->id]);
        $this->assertDatabaseMissing('subscription_plan_menus', ['subscription_plan_id' => $plan->id, 'menu_id' => $otherMenu->id]);
        $this->assertDatabaseMissing('subscription_plan_child_menus', ['subscription_plan_id' => $plan->id, 'child_menu_id' => $otherChild->id]);
        $this->assertDatabaseMissing('business_category_modules', ['business_category_id' => $business->category_id, 'module_id' => $otherModule->id]);
        $this->assertDatabaseHas('business_modules', ['business_id' => $business->id, 'module_id' => $module->id, 'source' => 'Plan']);
        $this->assertDatabaseHas('business_menus', ['business_id' => $business->id, 'menu_id' => $menu->id, 'source' => 'Plan']);
        $this->assertDatabaseHas('business_child_menus', ['business_id' => $business->id, 'child_menu_id' => $child->id, 'source' => 'Plan']);
    }

    public function test_basic_plan_seeder_creates_one_plan_per_category_without_new_mappings(): void
    {
        $first = BusinessCategory::create(['name'=>'First','slug'=>'first-only','approval_status'=>'Approved']);
        $second = BusinessCategory::create(['name'=>'Second','slug'=>'second-only','approval_status'=>'Approved']);
        $module = Module::create(['name'=>'Independent','slug'=>'independent','approval_status'=>'Approved']);
        BusinessCategoryModule::create(['business_category_id' => $first->id, 'module_id' => $module->id]);
        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
        $this->assertDatabaseCount('subscription_plans', 2);
        $this->assertDatabaseHas('subscription_plan_modules', ['business_category_id'=>$first->id, 'module_id'=>$module->id]);
        $this->assertDatabaseMissing('subscription_plan_modules', ['business_category_id'=>$second->id, 'module_id'=>$module->id]);
        $this->assertDatabaseMissing('business_category_modules', ['business_category_id'=>$second->id, 'module_id'=>$module->id]);
    }

}
