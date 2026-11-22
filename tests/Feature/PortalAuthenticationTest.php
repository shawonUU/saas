<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BusinessCategory;
use App\Models\User;
use App\Models\Business;
use App\Models\SaasUser;
use App\Models\BusinessUser;
use App\Models\AuthorityUser;
use App\Models\BusinessRole;
use App\Models\BusinessCustomer;
class PortalAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    private function business(string $domain): Business
    {
        $category = BusinessCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'slug' => 'general', 'approval_status' => 'Approved']);
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@owner.test', 'approval_status' => 'Approved']);
        return Business::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
    }
    public function test_wildcard_routes_are_tenant_resolved(): void
    {
        foreach (['rohimstore', 'fashionbd'] as $domain) {
            $business = $this->business($domain);
            $this->get('http://'.$domain.'.saas.test/')->assertOk()->assertSee($business->name);
            $this->get('http://admin.'.$domain.'.saas.test/login')->assertOk();
        }
        $this->get('http://unknown.saas.test/')->assertNotFound();
    }
    public function test_registration_uses_the_saas_guard(): void
    {
        $this->post('http://saas.test/register', ['name' => 'New User','email' => 'new@example.test','password' => 'password123','password_confirmation' => 'password123'])
            ->assertRedirect('http://saas.test/dashboard');
        $this->assertAuthenticated('saas_user');
        $this->assertGuest('authority');
    }
    public function test_business_owner_identity_uses_global_user_id(): void
    {
        $global = User::create(['name' => 'Owner','email' => 'owner@example.test','approval_status' => 'Approved']);
        $saas = SaasUser::create(['user_id'=>$global->id,'name'=>'Owner','email'=>'owner@example.test','password'=>'saaspassword1','approval_status'=>'Approved']);
        $category = BusinessCategory::create(['name' => 'General', 'slug' => 'general', 'approval_status' => 'Approved']);
        $this->actingAs($saas->refresh(), 'saas_user')->post('http://saas.test/businesss', [
            'name' => 'Rohim Store','domain' => 'rohimstore','category_id' => $category->id,
            'admin_password' => 'adminpassword1', 'admin_password_confirmation' => 'adminpassword1',
        ])->assertRedirect('http://saas.test/dashboard');
        $business = Business::where('domain','rohimstore')->firstOrFail();
        $this->assertSame((int)$global->id, (int)$business->owner_id);
        $admin = BusinessUser::where('business_id', $business->id)->firstOrFail();
        $this->assertSame((int)$global->id, (int)$admin->user_id);
        $this->post('http://admin.rohimstore.saas.test/login', ['email'=>'owner@example.test','password'=>'saaspassword1'])->assertSessionHasErrors('email');
        $this->post('http://admin.rohimstore.saas.test/login', ['email'=>'owner@example.test','password'=>'adminpassword1'])->assertRedirect('http://admin.rohimstore.saas.test/dashboard');
    }
    public function test_admin_is_reserved_and_authority_has_own_guard(): void
    {
        $global = User::create(['name'=>'Owner','email'=>'owner@example.test','approval_status'=>'Approved']);
        $saas = SaasUser::create(['user_id'=>$global->id,'name'=>'Owner','email'=>'owner@example.test','password'=>'password123','approval_status'=>'Approved']);
        $category = BusinessCategory::create(['name'=>'General','slug'=>'general','approval_status'=>'Approved']);
        $this->actingAs($saas->refresh(), 'saas_user')->post('http://saas.test/businesss', ['name'=>'Fake Admin','domain'=>'admin','category_id'=>$category->id])->assertSessionHasErrors('domain');
        $this->assertDatabaseMissing('businesses',['domain'=>'admin']);
        $authorityGlobal = User::create(['name'=>'Authority','email'=>'hq-global@example.test','approval_status'=>'Approved']);
        AuthorityUser::create(['user_id'=>$authorityGlobal->id,'name'=>'Authority','email'=>'hq@example.test','password'=>'password123','approval_status'=>'Approved']);
        $this->post('http://admin.saas.test/login',['email'=>'hq@example.test','password'=>'password123'])->assertRedirect('http://admin.saas.test/dashboard');
        $this->assertAuthenticated('authority');
    }
    public function test_business_admin_and_customer_cannot_access_another_business(): void
    {
        $a = $this->business('rohimstore');
        $this->business('anotherstore');
        $memberGlobal = User::create(['name'=>'Employee','email'=>'employee@example.test','approval_status'=>'Approved']);
        $role = BusinessRole::create(['business_id'=>$a->id,'name'=>'Staff','approval_status'=>'Approved']);
        $admin = BusinessUser::create(['user_id'=>$memberGlobal->id,'business_id'=>$a->id,'role_id'=>$role->id,'name'=>'Employee','email'=>'employee@example.test','password'=>'password123','approval_status'=>'Approved']);
        $this->actingAs($admin->refresh(),'business_admin')->get('http://admin.rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://admin.anotherstore.saas.test/dashboard')->assertForbidden();
        $customerGlobal = User::create(['name'=>'Customer','email'=>'customer@example.test','approval_status'=>'Approved']);
        $customer = BusinessCustomer::create(['user_id'=>$customerGlobal->id,'business_id'=>$a->id,'name'=>'Customer','email'=>'customer@example.test','password'=>'password123','approval_status'=>'Approved']);
        $this->actingAs($customer->refresh(),'business_customer')->get('http://rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://anotherstore.saas.test/dashboard')->assertForbidden();
    }
}
