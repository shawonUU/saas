<?php
namespace Tests\Feature;
use App\Models\{Business,BusinessCategory,BusinessUser,BusinessRole,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
class PortalPasswordResetTest extends TestCase
{
    use RefreshDatabase;
    public function test_tenant_reset_tokens_do_not_collide_for_the_same_email(): void
    {
        $category = BusinessCategory::create(['name'=>'General','slug'=>'general','approval_status'=>'Approved']);
        $global = User::create(['name'=>'Owner','email'=>'owner@example.test','approval_status'=>'Approved']);
        $users = [];
        foreach (['alpha','beta'] as $domain) {
            $shop = Business::create(['name'=>$domain,'domain'=>$domain,'owner_id'=>$global->id,'category_id'=>$category->id,'approval_status'=>'Approved']);
            $role = BusinessRole::create(['name'=>'Staff','business_id'=>$shop->id,'approval_status'=>'Approved']);
            $users[$domain] = BusinessUser::create(['user_id'=>$global->id,'business_id'=>$shop->id,'role_id'=>$role->id,'name'=>'Staff','email'=>'shared@example.test','password'=>'password123','approval_status'=>'Approved']);
        }
        $broker = Password::broker('business_users');
        $first = $broker->createToken($users['alpha']);
        $this->assertTrue($broker->tokenExists($users['alpha'], $first));
        $this->assertFalse($broker->tokenExists($users['beta'], $first));
        $second = $broker->createToken($users['beta']);
        $this->assertTrue($broker->tokenExists($users['alpha'], $first));
        $this->assertTrue($broker->tokenExists($users['beta'], $second));
    }
}
