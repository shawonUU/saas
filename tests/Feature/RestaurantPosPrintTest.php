<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\Configurations\Product;
use App\Models\Configurations\Variant;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RestaurantPosPrintTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['receipt'])]
    #[TestWith(['kot'])]
    public function test_embedded_documents_are_read_only_and_have_no_navigation_controls(string $kind): void
    {
        [$business, $admin] = $this->tenant('print-preview');
        $order = $this->order($business);
        $version = $order->restaurant_version;

        $this->actingAs($admin, 'business_admin')
            ->get('http://admin.print-preview.saas.test/sales/restaurant-pos/orders/'.$order->id.'/'.$kind.'?embed=1')
            ->assertOk()
            ->assertSee('name="rpos-print-document" content="'.$kind.'"', false)
            ->assertSee('class="rpos-print-embedded"', false)
            ->assertSee('Soup <strong>special</strong>')
            ->assertDontSee('Soup <strong>special</strong>', false)
            ->assertDontSee('class="actions"', false)
            ->assertDontSee('Back to order');

        $this->assertSame($version, $order->fresh()->restaurant_version);
        $this->assertSame('draft', $order->fresh()->status);
        $this->assertSame('draft', $order->items()->sole()->kitchen_status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_standalone_print_documents_keep_their_print_and_return_controls(): void
    {
        [$business, $admin] = $this->tenant('standalone-print');
        $order = $this->order($business);

        $this->actingAs($admin, 'business_admin')
            ->get('http://admin.standalone-print.saas.test/sales/restaurant-pos/orders/'.$order->id.'/receipt')
            ->assertOk()->assertSee('class="actions"', false)->assertSee('Back to order')->assertSee('Print bill');
    }

    public function test_embedded_preview_cannot_read_another_business_order(): void
    {
        [, $admin] = $this->tenant('our-print');
        [$other] = $this->tenant('their-print');
        $order = $this->order($other);

        $this->actingAs($admin, 'business_admin')
            ->get('http://admin.our-print.saas.test/sales/restaurant-pos/orders/'.$order->id.'/receipt?embed=1')
            ->assertNotFound()->assertDontSee('Soup');
    }

    private function tenant(string $domain): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Restaurant', 'slug' => 'restaurant-'.$domain, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['user_id' => $owner->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Owner', 'email' => $owner->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        $module = Module::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales', 'approval_status' => 'Approved']);
        $menu = Menu::firstOrCreate(['module_id' => $module->id, 'slug' => 'restaurant-pos'], ['name' => 'Restaurant POS', 'approval_status' => 'Approved']);
        BusinessCategoryModule::create(['business_category_id' => $category->id, 'module_id' => $module->id, 'approval_status' => 'Approved']);
        BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $menu->id, 'approval_status' => 'Approved']);
        app(BusinessSubscriptionService::class)->activateBasic($business);

        return [$business, $admin];
    }

    private function order(Business $business): Order
    {
        $product = Product::create(['business_id' => $business->id, 'name' => 'Soup <strong>special</strong>', 'sku' => 'SOUP', 'price' => 120, 'cost' => 40, 'track_stock' => false]);
        $variant = Variant::create(['business_id' => $business->id, 'product_id' => $product->id, 'sku' => 'SOUP-1', 'price' => 120, 'cost' => 40, 'stock' => 0]);
        $order = Order::create(['business_id' => $business->id, 'order_no' => 'SO-PRINT', 'channel' => 'restaurant', 'service_type' => 'takeaway', 'status' => 'draft', 'subtotal' => 240, 'total' => 240, 'restaurant_version' => 1]);
        $order->items()->create(['business_id' => $business->id, 'variant_id' => $variant->id, 'name' => $product->name, 'quantity' => 2, 'price' => 120, 'cost' => 40, 'line_total' => 240, 'kitchen_status' => 'draft']);

        return $order;
    }
}
