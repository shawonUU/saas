<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCustomer;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\BusinessUserAccess;
use App\Models\Configurations\Category;
use App\Models\Configurations\Supplier;
use App\Models\Menu;
use App\Models\Module;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConfigurationMastersTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $domain, string $menuSlug='suppliers', bool $owner=true): array
    {
        $global = User::create(['name'=>'Tester','email'=>$domain.'@test.local','is_active'=>true,'approval_status'=>'Approved']);
        $category = BusinessCategory::firstOrCreate(['slug'=>'test-category'],['name'=>'Test Category','is_active'=>true,'approval_status'=>'Approved']);
        $business = Business::create(['name'=>$domain,'domain'=>$domain,'owner_id'=>$global->id,'category_id'=>$category->id,'is_active'=>true,'approval_status'=>'Approved']);
        $role = BusinessRole::create(['business_id'=>$business->id,'name'=>$owner?'Owner':'Staff','is_owner'=>$owner,'is_active'=>true,'approval_status'=>'Approved']);
        $user = BusinessUser::create(['user_id'=>$global->id,'business_id'=>$business->id,'role_id'=>$role->id,'name'=>$domain,'email'=>$domain.'@test.local','password'=>'123456789','is_active'=>true,'approval_status'=>'Approved']);
        $module = Module::firstOrCreate(['slug'=>'configurations'],['name'=>'Configurations','is_active'=>true,'approval_status'=>'Approved']);
        $menu = Menu::firstOrCreate(['module_id'=>$module->id,'slug'=>$menuSlug],['name'=>$menuSlug,'is_active'=>true,'approval_status'=>'Approved']);
        BusinessCategoryModule::firstOrCreate(['business_category_id'=>$category->id,'module_id'=>$module->id],['approval_status'=>'Approved']);
        BusinessCategoryMenu::firstOrCreate(['business_category_id'=>$category->id,'menu_id'=>$menu->id],['approval_status'=>'Approved']);
        app(BusinessSubscriptionService::class)->syncBasicPlan($category);
        app(BusinessSubscriptionService::class)->activateBasic($business);
        return compact('business','user','module','menu','category');
    }

    public function test_twelve_distinct_migrations_and_routes_exist(): void
    {
        foreach (['config_customers','config_suppliers','config_units','config_sizes','config_colors',
            'config_brands','config_categories','config_sub_categories','config_taxes','config_currencies',
            'config_payment_methods','config_shipping_methods'] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }
        foreach (['customers','suppliers','units','sizes','colors','brands','categories',
            'sub_categories','taxes','currencies','payment_methods','shipping_methods'] as $name) {
            $this->assertTrue(Route::has('business.admin.configurations.'.$name.'.index'));
        }
    }

    public function test_supplier_crud_database_filters_and_modal_visible_in_html(): void
    {
        ['business'=>$business,'user'=>$user] = $this->tenant('shopone');
        $this->actingAs($user,'business_admin');
        $url='http://admin.shopone.saas.test/configurations/supplier';
        $this->post($url,['name'=>'Alpha Supplies','phone'=>'01711000000','is_active'=>1])->assertRedirect();
        $this->post($url,['name'=>'Beta Supplies','phone'=>'01999000000','is_active'=>0])->assertRedirect();
        $this->assertDatabaseCount('config_suppliers',2);
        $this->get($url.'?f[name]=Alpha&per_page=10')->assertOk()->assertSee('Alpha Supplies')->assertDontSee('Beta Supplies')
            ->assertSee('cfg-filter-row')->assertSee('cfg-modal');
        $this->get($url.'?f[is_active]=0')->assertOk()->assertSee('Beta Supplies')->assertDontSee('Alpha Supplies');
        $row=Supplier::query()->where('business_id',$business->id)->where('name','Alpha Supplies')->firstOrFail();
        $this->put($url.'/'.$row->id,['name'=>'Alpha Updated','is_active'=>1])->assertRedirect();
        $this->assertDatabaseHas('config_suppliers',['id'=>$row->id,'name'=>'Alpha Updated']);
        $this->delete($url.'/'.$row->id)->assertRedirect();
        $this->assertSoftDeleted('config_suppliers',['id'=>$row->id]);
    }

    public function test_tenant_cannot_update_other_business_suppliers(): void
    {
        ['user'=>$userA]=$this->tenant('shopa');
        ['business'=>$businessB]=$this->tenant('shopb');
        $row=Supplier::query()->create(['business_id'=>$businessB->id,'name'=>'B only','is_active'=>true,'approval_status'=>'Approved']);
        $this->actingAs($userA,'business_admin')
            ->put('http://admin.shopa.saas.test/configurations/supplier/'.$row->id,['name'=>'Hijacked','is_active'=>1])->assertNotFound();
        $this->assertDatabaseHas('config_suppliers',['id'=>$row->id,'name'=>'B only']);
    }

    public function test_staff_requires_granular_crud_permission(): void
    {
        ['business'=>$business,'user'=>$staff,'module'=>$module,'menu'=>$menu]=$this->tenant('staffstore','suppliers',false);
        $this->actingAs($staff,'business_admin');
        $url='http://admin.staffstore.saas.test/configurations/supplier';
        $this->post($url,['name'=>'Denied','is_active'=>1])->assertForbidden();
        BusinessUserAccess::create(['business_user_id'=>$staff->id,'module_id'=>$module->id,'menu_id'=>$menu->id,
            'can_view'=>true,'can_create'=>true,'is_active'=>true,'approval_status'=>'Approved']);
        $this->post($url,['name'=>'Allowed','is_active'=>1])->assertRedirect();
        $this->assertDatabaseHas('config_suppliers',['business_id'=>$business->id,'name'=>'Allowed']);
    }

    public function test_customer_and_supplier_use_separate_models_and_login_table_is_untouched(): void
    {
        $this->assertSame('config_customers', (new BusinessCustomer)->getTable());
        $this->assertSame('config_suppliers', (new Supplier)->getTable());
        $this->assertTrue(Schema::hasTable('business_customers'));
    }

    public function test_subcategory_parent_must_belong_to_same_business(): void
    {
        ['business'=>$business,'user'=>$user]=$this->tenant('shopcat','sub-categories');
        ['business'=>$other]=$this->tenant('othercat','categories');
        $foreign=Category::query()->create(['business_id'=>$other->id,'name'=>'Foreign','approval_status'=>'Approved']);
        $this->actingAs($user,'business_admin');
        $this->post('http://admin.shopcat.saas.test/configurations/sub-categories',[
            'name'=>'Wrong','category_id'=>$foreign->id,'is_active'=>1,
        ])->assertSessionHasErrors(['category_id']);
        $this->assertDatabaseMissing('config_sub_categories',['business_id'=>$business->id,'name'=>'Wrong']);
    }
}
