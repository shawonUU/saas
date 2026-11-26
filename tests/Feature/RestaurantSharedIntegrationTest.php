<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCustomer;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\ChildMenu;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use App\Services\Public\Business\Admin\Sales\Orders\OrderDetails\CancelSale;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RestaurantSharedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $domain = 'dining'): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@example.test', 'approval_status' => 'Approved']);
        $category = BusinessCategory::firstOrCreate(['slug' => 'restaurant'], ['name' => 'Restaurant', 'approval_status' => 'Approved']);
        $business = Business::create(['name' => $domain, 'domain' => $domain, 'owner_id' => $owner->id, 'category_id' => $category->id, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['business_id' => $business->id, 'user_id' => $owner->id, 'role_id' => $role->id, 'name' => 'Owner', 'email' => $domain.'@example.test', 'password' => 'test-password', 'approval_status' => 'Approved']);
        foreach ([
            ['configurations', 'customers', null], ['configurations', 'products', null],
            ['configurations', 'tables', null], ['configurations', 'kitchen-stations', null],
            ['sales', 'reports', 'sales-summary'], ['sales', 'orders', 'order-details'],
            ['sales', 'restaurant-pos', null],
            ['sales', 'returns', 'new-return'], ['finance', 'receivables', 'customer-dues'],
            ['finance', 'receivables', 'collections'],
        ] as [$moduleSlug, $menuSlug, $childSlug]) {
            $module = Module::firstOrCreate(['slug' => $moduleSlug], ['name' => $moduleSlug, 'approval_status' => 'Approved']);
            $menu = Menu::firstOrCreate(['module_id' => $module->id, 'slug' => $menuSlug], ['name' => $menuSlug, 'approval_status' => 'Approved']);
            BusinessCategoryModule::firstOrCreate(['business_category_id' => $category->id, 'module_id' => $module->id], ['approval_status' => 'Approved']);
            BusinessCategoryMenu::firstOrCreate(['business_category_id' => $category->id, 'menu_id' => $menu->id], ['approval_status' => 'Approved']);
            if ($childSlug) {
                $child = ChildMenu::firstOrCreate(['menu_id' => $menu->id, 'slug' => $childSlug], ['name' => $childSlug, 'approval_status' => 'Approved']);
                BusinessCategoryChildMenu::firstOrCreate(['business_category_id' => $category->id, 'child_menu_id' => $child->id], ['approval_status' => 'Approved']);
            }
        }
        app(BusinessSubscriptionService::class)->syncBasicPlan($category);
        app(BusinessSubscriptionService::class)->activateBasic($business);

        return [$business, $admin];
    }

    private function order(Business $business, string $number, string $status, array $extra = []): Order
    {
        return Order::create(array_merge([
            'business_id' => $business->id, 'order_no' => $number, 'channel' => 'restaurant',
            'status' => $status, 'subtotal' => 100, 'total' => 100,
        ], $extra));
    }

    private function variant(Business $business, string $sku, bool $ingredient = false, bool $tracked = false): Variant
    {
        $product = Product::create(['business_id' => $business->id, 'name' => $sku, 'sku' => $sku, 'price' => 100, 'cost' => 10, 'is_active' => true, 'is_ingredient' => $ingredient, 'track_stock' => $tracked]);

        return Variant::create(['business_id' => $business->id, 'product_id' => $product->id, 'sku' => $sku.'-one', 'price' => 100, 'cost' => 10, 'stock' => 0, 'is_active' => true]);
    }

    public function test_counter_customer_reuses_customer_table_without_creating_a_login(): void
    {
        [$business, $admin] = $this->tenant();
        $users = User::count();
        $this->actingAs($admin, 'business_admin')->post('http://admin.dining.saas.test/configurations/customers', [
            'name' => 'Counter Guest', 'name_bn' => 'কাউন্টার অতিথি', 'phone' => '01700000000',
            'address' => 'Dhaka', 'address_bn' => 'ঢাকা', 'notes' => 'Regular guest', 'is_active' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $customer = BusinessCustomer::where('business_id', $business->id)->sole();
        $this->assertNull($customer->user_id);
        $this->assertNull($customer->email);
        $this->assertSame('Dhaka', $customer->address);
        $this->assertSame('ঢাকা', $customer->address_bn);
        $this->assertSame($users, User::count());
    }

    public function test_popular_menu_uses_recent_posted_net_sales_for_this_restaurant(): void
    {
        $this->freezeTime();
        [$business, $admin] = $this->tenant('popular-menu');
        [$otherBusiness] = $this->tenant('other-menu');
        $first = $this->variant($business, 'First dish');
        $second = $this->variant($business, 'Second dish');
        $inactive = $this->variant($business, 'Unavailable dish');
        $inactive->update(['is_active' => false]);
        $foreign = $this->variant($otherBusiness, 'Foreign dish');
        $rows = [
            [$business, $first, 'RECENT-FIRST', 'confirmed', now(), 5, 1, 'draft'],
            [$business, $second, 'RECENT-SECOND', 'confirmed', now(), 2, 0, 'draft'],
            [$business, $second, 'RETURNED-SECOND', 'confirmed', now(), 50, 50, 'draft'],
            [$business, $second, 'OLD-SECOND', 'confirmed', now()->subDays(31), 90, 0, 'draft'],
            [$business, $second, 'DRAFT-SECOND', 'draft', null, 90, 0, 'draft'],
            [$business, $second, 'VOID-SECOND', 'confirmed', now(), 90, 0, 'void'],
            [$business, $inactive, 'INACTIVE-DISH', 'confirmed', now(), 90, 0, 'draft'],
            [$otherBusiness, $foreign, 'OTHER-DISH', 'confirmed', now(), 90, 0, 'draft'],
        ];
        foreach ($rows as [$owner, $variant, $number, $status, $postedAt, $quantity, $returned, $kitchenStatus]) {
            $order = $this->order($owner, $number, $status, ['posted_at' => $postedAt]);
            $order->items()->create([
                'business_id' => $owner->id, 'variant_id' => $variant->id, 'name' => $variant->sku,
                'quantity' => $quantity, 'returned_quantity' => $returned, 'price' => 100,
                'cost' => 10, 'line_total' => $quantity * 100, 'kitchen_status' => $kitchenStatus,
            ]);
        }

        $this->actingAs($admin, 'business_admin')->get('http://admin.popular-menu.saas.test/sales/restaurant-pos')
            ->assertOk()->assertViewHas('popularVariantIds', [$first->id, $second->id])
            ->assertViewHas('posSettings', ['kitchen_mode' => 'optional', 'auto_send_to_kitchen' => false, 'default_service' => 'takeaway']);
    }

    public function test_customer_email_is_unique_within_the_tenant(): void
    {
        [$business, $admin] = $this->tenant();
        $url = 'http://admin.dining.saas.test/configurations/customers';
        $this->actingAs($admin, 'business_admin')->post($url, ['name' => 'Guest', 'email' => 'GUEST@example.test', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->post($url, ['name' => 'Duplicate', 'email' => 'guest@example.test', 'is_active' => 1])->assertSessionHasErrors('email');
        $this->assertSame(1, BusinessCustomer::where('business_id', $business->id)->count());
    }

    public function test_drafts_are_excluded_from_income_dues_and_generic_bill_details(): void
    {
        [$business, $admin] = $this->tenant();
        $draft = $this->order($business, 'OPEN-CHECK', 'draft', ['total' => 900, 'subtotal' => 900]);
        $posted = $this->order($business, 'BILLED-CHECK', 'confirmed', ['created_at' => now()->subDay(), 'posted_at' => now()]);
        $this->order($business, 'VOID-CHECK', 'cancelled', ['total' => 700, 'subtotal' => 700]);
        $this->actingAs($admin, 'business_admin');
        $this->get('http://admin.dining.saas.test/sales/reports/sales-summary?from='.now()->toDateString().'&to='.now()->toDateString())
            ->assertOk()->assertViewHas('counts', fn ($counts) => $counts['orders'] === 1 && $counts['invoiced'] === 100.0)
            ->assertViewHas('records', fn ($rows) => $rows->pluck('id')->all() === [$posted->id]);
        $this->get('http://admin.dining.saas.test/finance/receivables/customer-dues')->assertOk()
            ->assertViewHas('records', fn ($rows) => $rows->pluck('id')->all() === [$posted->id]);
        $this->get('http://admin.dining.saas.test/finance/receivables/collections')->assertOk()
            ->assertViewHas('docs', fn ($rows) => $rows->pluck('id')->all() === [$posted->id]);
        $this->get('http://admin.dining.saas.test/sales/orders/order-details/'.$draft->id)->assertNotFound();
        $this->post('http://admin.dining.saas.test/finance/receivables/collections', ['document_id' => $draft->id, 'amount' => 50, 'method' => 'cash'])->assertNotFound();
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('journals', 0);
    }

    public function test_open_table_and_station_in_use_cannot_be_removed(): void
    {
        [$business, $admin] = $this->tenant();
        $area = DiningArea::create(['business_id' => $business->id, 'name' => 'Main']);
        $table = DiningTable::create(['business_id' => $business->id, 'name' => 'T1', 'dining_area_id' => $area->id, 'capacity' => 4]);
        $this->order($business, 'OPEN-TABLE', 'draft', ['dining_table_id' => $table->id]);
        $station = KitchenStation::create(['business_id' => $business->id, 'name' => 'Hot Kitchen']);
        $variant = $this->variant($business, 'Food');
        $variant->product->update(['kitchen_station_id' => $station->id]);
        $this->actingAs($admin, 'business_admin')->delete('http://admin.dining.saas.test/configurations/tables/'.$table->id)->assertSessionHasErrors('name');
        $this->delete('http://admin.dining.saas.test/configurations/kitchen-stations/'.$station->id)->assertSessionHasErrors('name');
        $this->assertNull($table->fresh()->deleted_at);
        $this->assertNull($station->fresh()->deleted_at);
    }

    public function test_product_station_assignment_is_tenant_scoped_and_preserves_bangla_name(): void
    {
        [$business, $admin] = $this->tenant();
        [$other] = $this->tenant('another');
        $own = KitchenStation::create(['business_id' => $business->id, 'name' => 'Hot']);
        $foreign = KitchenStation::create(['business_id' => $other->id, 'name' => 'Other']);
        $payload = ['name' => 'Rice', 'name_bn' => 'ভাত', 'sku' => 'RICE', 'price' => 80, 'cost' => 0, 'track_stock' => 0, 'is_active' => 1,
            'kitchen_station_id' => $foreign->id, 'variants' => [['sku' => 'RICE-ONE', 'price' => 80, 'cost' => 0]]];
        $this->actingAs($admin, 'business_admin')->post('http://admin.dining.saas.test/configurations/products', $payload)->assertSessionHasErrors('kitchen_station_id');
        $payload['kitchen_station_id'] = $own->id;
        $this->post('http://admin.dining.saas.test/configurations/products', $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['business_id' => $business->id, 'sku' => 'RICE', 'name_bn' => 'ভাত', 'kitchen_station_id' => $own->id]);
    }

    public function test_stock_tracking_cannot_change_while_sent_food_awaits_billing(): void
    {
        [$business, $admin] = $this->tenant();
        $food = $this->variant($business, 'Dinner');
        $order = $this->order($business, 'SENT-DINNER', 'draft');
        $order->items()->create(['business_id' => $business->id, 'variant_id' => $food->id, 'name' => 'Dinner', 'quantity' => 1, 'price' => 100, 'cost' => 10, 'line_total' => 100, 'kitchen_status' => 'queued', 'recipe_snapshot' => []]);
        $this->actingAs($admin, 'business_admin')->put('http://admin.dining.saas.test/configurations/products/'.$food->product_id, [
            'name' => 'Dinner', 'sku' => 'Dinner', 'price' => 100, 'cost' => 10, 'track_stock' => 1, 'is_active' => 1,
            'variants' => [['id' => $food->id, 'sku' => $food->sku, 'price' => 100, 'cost' => 10]],
        ])->assertSessionHasErrors('track_stock');
        $this->assertFalse((bool) $food->product->fresh()->track_stock);
    }

    public function test_online_recipe_cancellation_reverses_original_shared_stock_movements(): void
    {
        [$business] = $this->tenant();
        $ingredient = $this->variant($business, 'Flour', true, true);
        $food = $this->variant($business, 'Bread');
        DB::transaction(fn () => app(Inventory::class)->move($business, $ingredient, 10, 'opening', 10, 'opening', $ingredient->id));
        RecipeItem::create(['business_id' => $business->id, 'food_variant_id' => $food->id, 'ingredient_variant_id' => $ingredient->id, 'quantity' => 2, 'is_active' => true, 'approval_status' => 'Approved']);
        $order = app(Sales::class)->create($business, ['items' => [['variant_id' => $food->id, 'quantity' => 1]]], 'online');
        $this->assertEquals(8, $ingredient->fresh()->stock);
        app(CancelSale::class)->cancel($business, $order);
        $this->assertEquals(10, $ingredient->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(0, StockMovement::where('business_id', $business->id)->whereIn('source_type', ['order', 'order_cancel'])->sum('quantity'));
    }

    public function test_restaurant_bill_cannot_use_generic_unpaid_cancellation(): void
    {
        [$business] = $this->tenant();
        $order = $this->order($business, 'POSTED-RESTAURANT', 'confirmed');
        $this->expectException(ValidationException::class);
        app(CancelSale::class)->cancel($business, $order);
    }

    public function test_return_ignores_void_kitchen_lines_and_does_not_restore_recipe_ingredients(): void
    {
        [$business] = $this->tenant();
        $food = $this->variant($business, 'Soup');
        $order = $this->order($business, 'RETURN-FOOD', 'confirmed');
        $base = ['business_id' => $business->id, 'variant_id' => $food->id, 'name' => 'Soup', 'quantity' => 1, 'price' => 100, 'cost' => 15, 'returned_quantity' => 0];
        $item = $order->items()->create($base + ['line_total' => 100, 'kitchen_status' => 'served', 'recipe_snapshot' => [['ingredient_variant_id' => $food->id, 'quantity' => 1, 'cost' => 15]]]);
        $order->items()->create($base + ['line_total' => 0, 'kitchen_status' => 'void', 'void_reason' => 'Changed before cooking']);
        $food->product->update(['track_stock' => true]);
        $return = app(Returns::class)->create($business, ['order_id' => $order->id, 'items' => [['order_item_id' => $item->id, 'quantity' => 1]]]);
        $this->assertEquals(100, $return->amount);
        $this->assertSame('returned', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->restaurant_closed_at);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_draft_cannot_be_returned_and_ingredients_do_not_appear_in_store_catalog(): void
    {
        [$business] = $this->tenant();
        $this->variant($business, 'Salt', true, true);
        $food = $this->variant($business, 'Lunch');
        $this->assertSame([$food->product_id], app(CatalogService::class)->query($business)->pluck('id')->all());
        $draft = $this->order($business, 'UNPOSTED', 'draft');
        $this->expectException(ValidationException::class);
        app(Returns::class)->create($business, ['order_id' => $draft->id, 'items' => []]);
    }
}
