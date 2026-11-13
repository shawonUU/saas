<?php
namespace Tests\Feature;
use App\Models\{User,Business,BusinessUser,BusinessRole,BusinessCategory,Module,Menu,ChildMenu,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu,BusinessChildMenu,BusinessUserAccess,AuthorityUser};
use App\Services\BusinessNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class BusinessNavigationTest extends TestCase
{
    use RefreshDatabase;
    private function setupBusiness(): array
    {
        $owner = User::create(['name' => 'Owner','email' => 'owner@test.local','password' => '123456789','approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Retail','slug' => 'retail-test','approval_status' => 'Approved']);
        $business = Business::create(['name' => 'Shop A','code' => 'shopa','owner_id' => $owner->id,'category_id' => $category->id,'approval_status' => 'Approved']);
        $role = BusinessRole::create(['name' => 'Owner','business_id' => $business->id,'is_owner' => true,'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['name' => 'Owner','business_id' => $business->id,'user_id' => $owner->id,'role_id' => $role->id,'email' => 'owner@test.local','password' => '123456789','approval_status' => 'Approved']);
        $module = Module::create(['name' => 'Inventory','slug' => 'inventory-test','approval_status' => 'Approved']);
        $menu = Menu::create(['name' => 'Products','slug' => 'products-test','module_id' => $module->id,'approval_status' => 'Approved']);
        $child = ChildMenu::create(['name' => 'Product List','slug' => 'product-list-test','menu_id' => $menu->id,'approval_status' => 'Approved']);
        foreach ([[BusinessCategoryModule::class,'module_id',$module->id],[BusinessCategoryMenu::class,'menu_id',$menu->id],[BusinessCategoryChildMenu::class,'child_menu_id',$child->id]] as [$model,$column,$id]) {
            $model::create(['business_category_id' => $category->id,$column => $id]);
        }
        return compact('business','admin','module','menu','child');
    }
    public function test_owner_automatically_gets_category_access_and_override_blocks_it(): void
    {
        extract($this->setupBusiness());
        $service = app(BusinessNavigation::class);
        $this->assertTrue($service->canView($business,$admin,$child->id));
        BusinessChildMenu::create(['business_id' => $business->id,'child_menu_id' => $child->id,'is_enabled' => false]);
        $this->assertFalse($service->canView($business,$admin,$child->id));
    }
    public function test_employee_does_not_inherit_owner_permission(): void
    {
        extract($this->setupBusiness());
        $role = BusinessRole::create(['name'=>'Staff','business_id'=>$business->id,'approval_status'=>'Approved']);
        $employee = BusinessUser::create(['name'=>'Staff','business_id'=>$business->id,'role_id'=>$role->id,'email'=>'staff@test.local','password'=>'123456789','approval_status'=>'Approved']);
        $service = app(BusinessNavigation::class);
        $this->assertFalse($service->canView($business,$employee,$child->id));
        BusinessUserAccess::create(['business_user_id'=>$employee->id,'module_id'=>$module->id,'menu_id'=>$menu->id,'child_menu_id'=>$child->id,'can_view'=>true]);
        $this->assertTrue($service->canView($business,$employee,$child->id));
        $this->actingAs($employee,'business_admin')->get('http://admin.shopa.saas.test/features/'.$child->id)->assertOk();
        BusinessChildMenu::create(['business_id'=>$business->id,'child_menu_id'=>$child->id,'is_enabled'=>false]);
        $this->get('http://admin.shopa.saas.test/features/'.$child->id)->assertForbidden();
    }
    public function test_only_authority_can_edit_category_access(): void
    {
        extract($this->setupBusiness());
        $this->get('http://admin.saas.test/access')->assertRedirect('http://admin.saas.test/login');
        $authority = AuthorityUser::create(['name'=>'HQ','email'=>'hq@test.local','password'=>'123456789','approval_status'=>'Approved']);
        $this->actingAs($authority,'authority')->get('http://admin.saas.test/access')->assertOk();
        $this->put('http://admin.saas.test/access/category/'.$business->category_id,['access'=>['module'=>[$module->id=>'1'],'menu'=>[$menu->id=>'1'],'child'=>[]]])->assertRedirect();
        $this->assertFalse(app(BusinessNavigation::class)->canView($business,$admin,$child->id));
    }
}
