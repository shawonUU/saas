<?php
namespace Tests\Feature;
use App\Models\AuthorityUser;
use App\Models\BusinessUser;
use App\Models\Shop;
use App\Models\ShopCategory;
use App\Models\ShopCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PortalAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    private function shop(string $code): Shop
    {
        $category = ShopCategory::firstOrCreate(['code' => 'general'], ['name' => 'General', 'approval_status' => 'Approved']);
        $owner = User::create(['name' => 'Owner', 'code' => 'U-'.$code, 'email' => $code.'@owner.test', 'password' => 'password123', 'approval_status' => 'Approved']);
        return Shop::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $code, 'code' => $code, 'approval_status' => 'Approved']);
    }
    public function test_any_newly_created_shop_is_served_by_the_existing_wildcard_routes(): void
    {
        foreach (['rohimstore', 'fashionbd', 'pharmacy24'] as $code) {
            $shop = $this->shop($code);
            $this->get('http://'.$code.'.saas.test/')->assertOk()->assertSee($shop->name);
            $this->get('http://admin.'.$code.'.saas.test/login')->assertOk();
        }
        $this->get('http://shopdoesnotexist.saas.test/')->assertNotFound();
        $this->get('http://admin.shopdoesnotexist.saas.test/login')->assertNotFound();
    }
    public function test_shop_creation_displays_automatic_subdomain_links(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U009', 'email' => 'owner9@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'fashion', 'name' => 'Fashion', 'approval_status' => 'Approved']);
        $this->actingAs($owner, 'web')->post('http://saas.test/shops', [
            'name' => 'Fashion BD', 'code' => 'fashionbd', 'category_id' => $category->id,
        ])->assertRedirect('http://saas.test/dashboard')->assertSessionHas('shop_created', 'fashionbd');
        $this->get('http://saas.test/dashboard')->assertOk()
            ->assertSee('http://fashionbd.saas.test/', false)
            ->assertSee('http://admin.fashionbd.saas.test/login', false);
    }
    public function test_saas_registration_does_not_authenticate_authority(): void
    {
        $this->post('http://saas.test/register', [
            'name' => 'New User', 'email' => 'user@example.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('http://saas.test/dashboard');
        $this->assertAuthenticated('web');
        $this->assertGuest('authority');
        $this->get('http://admin.saas.test/dashboard')->assertRedirect('http://admin.saas.test/login');
        $this->get('http://saas.test/authority/login')->assertNotFound();
    }
    public function test_admin_subdomain_is_reserved_for_authority(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U007', 'email' => 'owner7@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'general', 'name' => 'General', 'approval_status' => 'Approved']);
        $this->actingAs($owner, 'web')->post('http://saas.test/shops', [
            'name' => 'Fake Authority', 'code' => 'admin', 'category_id' => $category->id,
        ])->assertSessionHasErrors('code');
        $this->assertDatabaseMissing('shops', ['code' => 'admin']);
        $this->get('http://admin.saas.test/login')->assertOk();
        $this->get('http://admin.saas.test/register')->assertNotFound();
    }
    public function test_only_authority_can_log_in_to_admin_saas_host(): void
    {
        AuthorityUser::create(['name' => 'Authority', 'code' => 'A001', 'email' => 'hq@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $this->post('http://admin.saas.test/login', [
            'email' => 'hq@example.com', 'password' => 'password123',
        ])->assertRedirect('http://admin.saas.test/dashboard');
        $this->assertAuthenticated('authority');
        $this->assertGuest('web');
    }
    public function test_shop_model_cannot_save_reserved_subdomain(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U008', 'email' => 'owner8@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'general', 'name' => 'General', 'approval_status' => 'Approved']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        Shop::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => 'Fake Admin', 'code' => 'admin', 'approval_status' => 'Approved']);
    }
    public function test_shop_employee_cannot_access_another_shops_admin(): void
    {
        $a = $this->shop('rohimstore');
        $this->shop('othershop');
        $employee = BusinessUser::create([
            'shop_id' => $a->id, 'name' => 'Employee', 'email' => 'employee@example.com',
            'code' => 'EMP01', 'role' => 'employee', 'password' => 'password123', 'approval_status' => 'Approved',
        ]);
        $this->actingAs($employee, 'shop_admin');
        $this->get('http://admin.rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://admin.othershop.saas.test/dashboard')->assertForbidden();
    }
    public function test_customer_cannot_access_another_shops_storefront_account(): void
    {
        $a = $this->shop('rohimstore');
        $this->shop('othershop');
        $customer = ShopCustomer::create([
            'shop_id' => $a->id, 'name' => 'Customer', 'email' => 'customer@example.com',
            'code' => 'C01', 'password' => 'password123', 'approval_status' => 'Approved',
        ]);
        $this->actingAs($customer, 'shop_customer');
        $this->get('http://rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://othershop.saas.test/dashboard')->assertForbidden();
        $this->get('http://admin.rohimstore.saas.test/dashboard')->assertRedirect('http://admin.rohimstore.saas.test/login');
    }
    public function test_shop_registration_creates_independent_owner_login(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U123', 'email' => 'owner@example.com', 'password' => 'rootpassword1', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'retail', 'name' => 'Retail', 'approval_status' => 'Approved']);
        $this->actingAs($owner, 'web')->post('http://saas.test/shops', [
            'name' => 'Rohim Store', 'code' => 'rohimstore', 'category_id' => $category->id,
            'admin_password' => 'shopsecret1', 'admin_password_confirmation' => 'shopsecret1',
        ])->assertRedirect('http://saas.test/dashboard');
        $this->assertDatabaseHas('shops', ['code' => 'rohimstore', 'owner_id' => $owner->id]);
        $this->assertDatabaseHas('business_users', ['email' => 'owner@example.com', 'role' => 'owner']);
        $this->post('http://admin.rohimstore.saas.test/login', ['email' => 'owner@example.com', 'password' => 'rootpassword1'])->assertSessionHasErrors('email');
        $this->post('http://admin.rohimstore.saas.test/login', ['email' => 'owner@example.com', 'password' => 'shopsecret1'])->assertRedirect('http://admin.rohimstore.saas.test/dashboard');
    }
}
