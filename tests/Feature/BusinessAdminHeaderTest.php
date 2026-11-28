<?php
namespace Tests\Feature;

use App\Models\{Business, BusinessCategory, BusinessRole, BusinessUser, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessAdminHeaderTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $domain): BusinessUser
    {
        $category = BusinessCategory::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'approval_status' => 'Approved']);
        $global = User::create(['name' => 'Owner', 'email' => $domain.'@owner.test', 'approval_status' => 'Approved']);
        $business = Business::create(['name' => $domain, 'domain' => $domain, 'owner_id' => $global->id, 'category_id' => $category->id, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['name' => 'Owner', 'business_id' => $business->id, 'is_owner' => true, 'approval_status' => 'Approved']);
        return BusinessUser::create(['name' => 'Owner', 'email' => $global->email, 'user_id' => $global->id, 'business_id' => $business->id, 'role_id' => $role->id, 'password' => 'password123', 'approval_status' => 'Approved']);
    }

    private function notifyUser(BusinessUser $user): void
    {
        $user->notify(new class extends Notification {
            public function via(object $notifiable): array { return ['database']; }
            public function toDatabase(object $notifiable): array { return ['title' => 'Order received', 'message' => 'A customer placed an order.']; }
        });
    }

    public function test_header_has_profile_dropdown_and_notification_bell(): void
    {
        $admin = $this->tenant('shawondine');
        $this->actingAs($admin, 'business_admin')->get('http://admin.shawondine.saas.test/dashboard')
            ->assertOk()->assertSee('saas-profile-trigger')->assertSee('saas-notif-trigger')
            ->assertSee('saas-password-dialog');
    }

    public function test_notifications_are_scoped_to_authenticated_tenant_user(): void
    {
        $admin = $this->tenant('shawondine');
        $other = $this->tenant('otherdine');
        $this->notifyUser($admin);
        $this->notifyUser($other);
        $mine = $admin->notifications()->firstOrFail();
        $theirs = $other->notifications()->firstOrFail();
        $base = 'http://admin.shawondine.saas.test/settings/business/notifications';

        $this->actingAs($admin, 'business_admin')->get($base.'/latest')
            ->assertOk()->assertJsonPath('unread', 1)->assertJsonCount(1, 'items');
        $this->post($base.'/'.$theirs->id.'/read')->assertNotFound();
        $this->post($base.'/'.$mine->id.'/read')->assertOk();
        $this->assertNotNull($mine->fresh()->read_at);
        $this->assertNull($theirs->fresh()->read_at);
        $this->get('http://admin.otherdine.saas.test/settings/business/notifications/latest')->assertForbidden();
    }

    public function test_admin_can_change_only_their_own_password(): void
    {
        $admin = $this->tenant('shawondine');
        $url = 'http://admin.shawondine.saas.test/settings/business/profile/password';
        $this->actingAs($admin, 'business_admin')->post($url, [
            'current_password' => 'wrong', 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors(['current_password'], null, 'profilePassword');
        $this->post($url, [
            'current_password' => 'password123', 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $admin->fresh()->password));
    }
}
