<?php

namespace Tests\Feature;

use App\Models\Accounts\Journal;
use App\Models\Accounts\Payment;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCustomer;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets\KitchenTicketService;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use App\Services\Public\Business\Admin\Sales\Settings\PosSettings\PosSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RestaurantQuickSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_optional_dine_in_payment_keeps_the_table_and_reservation_until_manual_close(): void
    {
        $this->freezeTime();
        [$business, $owner] = $this->tenant('quick-dining', true);
        $table = $this->table($business);
        $food = $this->food($business, 'MEAL', 300, 10);
        $reservation = Reservation::create([
            'business_id' => $business->id, 'dining_table_id' => $table->id, 'customer_name' => 'Guest', 'guest_count' => 2,
            'starts_at' => now()->subMinutes(10), 'ends_at' => now()->addHour(), 'status' => 'confirmed',
            'request_key' => (string) Str::uuid(), 'approval_status' => 'Approved',
        ]);
        $base = 'http://admin.quick-dining.saas.test/sales/restaurant-pos';
        $this->actingAs($owner, 'business_admin');
        $created = $this->postJson($base.'/orders', $this->input($food, [
            'service_type' => 'dine_in', 'dining_table_id' => $table->id, 'reservation_id' => $reservation->id,
        ]))->assertOk()->assertJsonPath('order.workflow.kitchen_mode', 'optional')->assertJsonPath('order.workflow.table_release', 'manual');
        $order = Order::findOrFail($created->json('order.id'));
        $payment = $this->payment($order, ['tendered' => 500]);

        $paid = $this->postJson($base.'/orders/'.$order->id.'/checkout', $payment)->assertOk()
            ->assertJsonPath('order.status', 'confirmed')->assertJsonPath('order.due', 0)->assertJsonPath('order.change', 200)
            ->assertJsonPath('order.closed_at', null)->assertJsonPath('order.can_close', true)
            ->assertJsonPath('order.kitchen_tracking', false)->assertJsonPath('order.items.0.kitchen_status', 'draft');

        $this->postJson($base.'/orders/'.$order->id.'/checkout', $payment)->assertOk()->assertJsonPath('order.version', $paid->json('order.version'));
        $this->getJson($base.'/tables')->assertOk()->assertJsonPath('tables.0.occupied_order_id', $order->id)
            ->assertJsonPath('tables.0.order.paid', 300)->assertJsonPath('tables.0.order.due', 0);
        $this->assertSame('seated', $reservation->fresh()->status);
        $this->assertSame(9.0, (float) $food->fresh()->stock);
        $this->assertSame(1, Payment::where('reference_type', 'order')->where('reference_id', $order->id)->count());
        $this->assertSame(1, Journal::where('source_type', 'order')->where('source_id', $order->id)->count());
        $this->assertSame(1, StockMovement::where('source_type', 'order')->where('source_id', $order->id)->count());
        $this->assertSame([], app(KitchenTicketService::class)->orders($business));

        $this->postJson($base.'/orders/'.$order->id.'/close', ['version' => $paid->json('order.version')])->assertOk()->assertJsonPath('order.can_close', false);
        $this->assertNotNull($order->fresh()->restaurant_closed_at);
        $this->assertSame('completed', $reservation->fresh()->status);
        $this->getJson($base.'/tables')->assertOk()->assertJsonPath('tables.0.occupied_order_id', null)->assertJsonPath('tables.0.reservation', null);
    }

    public function test_quick_takeaway_posts_recipe_stock_once_without_any_kitchen_status_changes(): void
    {
        [$business] = $this->tenant('quick-recipe');
        $ingredient = $this->food($business, 'RICE', 0, 10);
        $ingredient->product->update(['is_ingredient' => true]);
        $food = $this->food($business, 'RICE-MEAL', 200);
        $food->product->update(['track_stock' => false]);
        RecipeItem::create(['business_id' => $business->id, 'food_variant_id' => $food->id, 'ingredient_variant_id' => $ingredient->id, 'quantity' => .25, 'approval_status' => 'Approved']);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food, ['restaurant_workflow' => ['version' => 1, 'kitchen_mode' => 'required']]));
        $payment = $this->payment($draft);

        $posted = $pos->checkout($business, $draft->id, $payment);
        $pos->checkout($business, $draft->id, $payment);

        $this->assertNotNull($posted->restaurant_closed_at);
        $this->assertSame('optional', $posted->restaurant_workflow['kitchen_mode']);
        $this->assertSame('draft', $posted->items()->first()->kitchen_status);
        $this->assertNull($posted->items()->first()->sent_at);
        $this->assertSame(9.75, (float) $ingredient->fresh()->stock);
        $this->assertSame(1, StockMovement::where('source_type', 'order')->where('source_id', $posted->id)->where('kind', 'ingredient-consumption')->count());
        $this->assertSame(1, Payment::where('reference_type', 'order')->count());
        $this->assertSame([], app(KitchenTicketService::class)->orders($business));
    }

    public function test_saved_workflow_is_frozen_and_new_orders_read_the_latest_locked_business_settings(): void
    {
        [$business] = $this->tenant('quick-freeze');
        $food = $this->food($business, 'TEA', 40, 10);
        $pos = app(RestaurantPosService::class);
        $first = $pos->save($business, $this->input($food));
        app(PosSettingsService::class)->save($business, ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => true]);

        $first = $pos->save($business, $this->input($food, ['version' => $first->restaurant_version, 'restaurant_workflow' => ['version' => 2, 'kitchen_mode' => 'required', 'auto_send_to_kitchen' => true]]), $first->id);
        $second = $pos->save($business, $this->input($food));
        $firstPaid = $pos->checkout($business, $first->id, $this->payment($first));
        $secondPaid = $pos->checkout($business, $second->id, $this->payment($second));

        $this->assertSame('optional', $firstPaid->restaurant_workflow['kitchen_mode']);
        $this->assertSame('draft', $firstPaid->items()->first()->kitchen_status);
        $this->assertSame('required', $secondPaid->restaurant_workflow['kitchen_mode']);
        $this->assertTrue($secondPaid->restaurant_workflow['auto_send_to_kitchen']);
        $this->assertSame('queued', $secondPaid->items()->first()->kitchen_status);
    }

    public function test_required_tracking_allows_payment_but_requires_serving_then_manual_table_close(): void
    {
        [$business] = $this->tenant('required-dining');
        app(PosSettingsService::class)->save($business, ['kitchen_mode' => 'required', 'auto_send_to_kitchen' => false]);
        $food = $this->food($business, 'STEAK', 300, 10);
        $table = $this->table($business);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id]));

        $paid = $pos->checkout($business, $draft->id, $this->payment($draft));

        $this->assertSame('confirmed', $paid->status);
        $this->assertSame('draft', $paid->items()->first()->kitchen_status);
        $this->assertFalse($pos->snapshot($business, $paid)['can_close']);
        $this->invalid(fn () => $pos->close($business, $paid->id, $paid->restaurant_version), 'order');
        $queued = $pos->send($business, $paid->id, $paid->restaurant_version);
        $itemId = $queued->items()->first()->id;
        $kitchen = app(KitchenTicketService::class);
        $preparing = $kitchen->update($business, $queued->id, $itemId, $queued->restaurant_version, 'preparing');
        $ready = $kitchen->update($business, $queued->id, $itemId, $preparing->restaurant_version, 'ready');
        $served = $kitchen->update($business, $queued->id, $itemId, $ready->restaurant_version, 'served');
        $this->assertNull($served->restaurant_closed_at);
        $this->assertTrue($pos->snapshot($business, $served)['can_close']);
        $this->assertNotNull($pos->close($business, $served->id, $served->restaurant_version)->restaurant_closed_at);
        $this->assertSame(9.0, (float) $food->fresh()->stock);
    }

    public function test_kitchen_off_rejects_sending_and_rolls_back_a_combined_save_and_send(): void
    {
        [$business] = $this->tenant('kitchen-off');
        app(PosSettingsService::class)->save($business, ['kitchen_mode' => 'off', 'auto_send_to_kitchen' => true]);
        $food = $this->food($business, 'SNACK', 50, 10);
        $pos = app(RestaurantPosService::class);

        $this->invalid(fn () => $pos->save($business, $this->input($food, ['send_to_kitchen' => true])), 'kitchen');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $draft = $pos->save($business, $this->input($food));
        $this->assertFalse($draft->restaurant_workflow['auto_send_to_kitchen']);
        $this->assertFalse($pos->snapshot($business, $draft)['can_send']);
        $this->invalid(fn () => $pos->send($business, $draft->id, $draft->restaurant_version), 'kitchen');
        $this->assertSame($draft->restaurant_version, $draft->fresh()->restaurant_version);
        $posted = $pos->checkout($business, $draft->id, $this->payment($draft));
        $this->assertSame('draft', $posted->items()->first()->kitchen_status);
        $this->assertSame(9.0, (float) $food->fresh()->stock);
    }

    public function test_optional_takeaway_tickets_remain_actionable_after_checkout_completes_the_sale(): void
    {
        [$business] = $this->tenant('optional-ticket');
        $food = $this->food($business, 'PIZZA', 250, 10);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food));
        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $paid = $pos->checkout($business, $queued->id, $this->payment($queued));
        $kitchen = app(KitchenTicketService::class);

        $tickets = $kitchen->orders($business);

        $this->assertNotNull($paid->restaurant_closed_at);
        $this->assertSame($paid->id, $tickets[0]['id']);
        $this->assertSame('queued', $tickets[0]['items'][0]['kitchen_status']);
        $this->assertTrue($tickets[0]['kitchen_tracking']);
        $itemId = $tickets[0]['items'][0]['id'];
        $preparing = $kitchen->update($business, $paid->id, $itemId, $paid->restaurant_version, 'preparing');
        $ready = $kitchen->update($business, $paid->id, $itemId, $preparing->restaurant_version, 'ready');
        $pos->serve($business, $paid->id, $ready->restaurant_version);
        $this->assertSame([], $kitchen->orders($business));
        $this->assertSame(9.0, (float) $food->fresh()->stock);
        $this->assertSame(1, Payment::where('reference_type', 'order')->count());
    }

    public function test_optional_dine_in_can_close_with_a_queued_ticket_without_faking_served_status(): void
    {
        [$business] = $this->tenant('optional-close');
        $food = $this->food($business, 'CURRY', 200, 10);
        $table = $this->table($business);
        $pos = app(RestaurantPosService::class);
        $queued = $pos->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id, 'send_to_kitchen' => true]));
        $paid = $pos->checkout($business, $queued->id, $this->payment($queued));

        $closed = $pos->close($business, $paid->id, $paid->restaurant_version);

        $this->assertNotNull($closed->restaurant_closed_at);
        $this->assertSame('queued', $closed->items()->first()->kitchen_status);
        $this->assertNull($closed->items()->first()->served_at);
        $this->assertCount(1, app(KitchenTicketService::class)->orders($business));
    }

    #[TestWith([false], 'inactive ingredient')]
    #[TestWith([true], 'changed food stock tracking')]
    public function test_sending_a_paid_order_preserves_the_posted_recipe_after_catalog_changes(bool $trackStockAfterPayment): void
    {
        [$business] = $this->tenant('paid-recipe-ticket');
        $ingredient = $this->food($business, 'FLOUR', 0, 10);
        $ingredient->product->update(['is_ingredient' => true]);
        $food = $this->food($business, 'BREAD', 100);
        $food->product->update(['track_stock' => false]);
        RecipeItem::create(['business_id' => $business->id, 'food_variant_id' => $food->id, 'ingredient_variant_id' => $ingredient->id, 'quantity' => .25, 'approval_status' => 'Approved']);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food));
        $paid = $pos->checkout($business, $draft->id, $this->payment($draft));
        $postedItem = $paid->items()->first();
        $this->assertNotEmpty($postedItem->recipe_snapshot);
        $ingredient->update(['is_active' => false]);
        $food->product->update(['track_stock' => $trackStockAfterPayment]);

        $queued = $pos->send($business, $paid->id, $paid->restaurant_version);

        $queuedItem = $queued->items()->first();
        $this->assertSame('queued', $queuedItem->kitchen_status);
        $this->assertSame($postedItem->recipe_snapshot, $queuedItem->recipe_snapshot);
        $this->assertSame($postedItem->cost, $queuedItem->cost);
        $this->assertSame(9.75, (float) $ingredient->fresh()->stock);
        $this->assertSame(1, StockMovement::where('source_type', 'order')->where('source_id', $paid->id)->count());
        $this->assertSame(1, Journal::where('source_type', 'order')->where('source_id', $paid->id)->count());
        $this->assertSame(1, Payment::where('reference_type', 'order')->where('reference_id', $paid->id)->count());
    }

    public function test_legacy_null_workflow_retains_automatic_queueing_despite_new_business_settings(): void
    {
        [$business] = $this->tenant('legacy-check');
        app(PosSettingsService::class)->save($business, ['kitchen_mode' => 'off']);
        $food = $this->food($business, 'LEGACY', 100, 10);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food));
        $draft->update(['restaurant_workflow' => null]);

        $paid = $pos->checkout($business, $draft->id, $this->payment($draft));

        $snapshot = $pos->snapshot($business, $paid);
        $this->assertSame(1, $snapshot['workflow']['version']);
        $this->assertSame('automatic', $snapshot['workflow']['table_release']);
        $this->assertSame('required', $snapshot['workflow']['kitchen_mode']);
        $this->assertSame('queued', $snapshot['items'][0]['kitchen_status']);
        $this->assertNull($paid->restaurant_workflow);
    }

    public function test_print_only_kot_renders_unsent_food_without_mutating_the_order(): void
    {
        [$business, $owner] = $this->tenant('print-only', true);
        app(PosSettingsService::class)->save($business, ['kitchen_mode' => 'off']);
        $food = $this->food($business, 'SANDWICH', 120, 10);
        $draft = app(RestaurantPosService::class)->save($business, $this->input($food));
        $this->actingAs($owner, 'business_admin');

        $this->get('http://admin.print-only.saas.test/sales/restaurant-pos/orders/'.$draft->id.'/kot')
            ->assertOk()->assertSee('SANDWICH')->assertSee('Print only')->assertSee('Printing does not change their status.');

        $this->assertSame($draft->restaurant_version, $draft->fresh()->restaurant_version);
        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertSame('draft', $draft->items()->first()->kitchen_status);
        $this->assertNull($draft->items()->first()->sent_at);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(10.0, (float) $food->fresh()->stock);
    }

    public function test_closing_a_partial_credit_bill_releases_the_table_and_preserves_later_payment(): void
    {
        [$business] = $this->tenant('quick-credit');
        $food = $this->food($business, 'LUNCH', 300, 10);
        $table = $this->table($business);
        $customer = BusinessCustomer::create(['business_id' => $business->id, 'name' => 'Regular guest', 'phone' => '01700000000', 'approval_status' => 'Approved']);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id, 'customer_id' => $customer->id]));
        $partial = $pos->checkout($business, $draft->id, $this->payment($draft, ['payment_mode' => 'partial', 'tendered' => 100]));

        $closed = $pos->close($business, $partial->id, $partial->restaurant_version);

        $this->assertSame(200.0, $pos->snapshot($business, $closed)['due']);
        $next = $pos->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id]));
        $this->assertNotSame($closed->id, $next->id);
        $payment = $this->payment($closed, ['tendered' => 200]);
        $paid = $pos->pay($business, $closed->id, $payment);
        $pos->pay($business, $closed->id, $payment);
        $this->assertSame(0, $pos->snapshot($business, $paid)['due']);
        $this->assertSame(300.0, (float) $paid->paid);
        $this->assertSame(2, Payment::where('reference_type', 'order')->where('reference_id', $closed->id)->count());
        $this->assertSame(9.0, (float) $food->fresh()->stock);
    }

    public function test_partially_returned_unsent_food_cannot_be_dispatched_or_printed_as_a_new_ticket(): void
    {
        [$business, $owner] = $this->tenant('returned-unsent', true);
        $food = $this->food($business, 'RETURNED-SNACK', 100, 10);
        $pos = app(RestaurantPosService::class);
        $input = $this->input($food);
        $input['items'][0]['quantity'] = 2;
        $draft = $pos->save($business, $input);
        $paid = $pos->checkout($business, $draft->id, $this->payment($draft));
        $item = $paid->items()->first();
        app(Returns::class)->create($business, ['order_id' => $paid->id, 'items' => [['order_item_id' => $item->id, 'quantity' => 1]]]);
        $paid->refresh();
        $this->actingAs($owner, 'business_admin');

        $this->postJson('http://admin.returned-unsent.saas.test/sales/restaurant-pos/orders/'.$paid->id.'/send', ['version' => $paid->restaurant_version])
            ->assertUnprocessable()->assertJsonValidationErrors('kitchen')->assertJsonPath('errors.kitchen.0', 'Unsent food on this order has been returned. Review the return and create a corrected order before sending new food to the kitchen.');

        $this->assertFalse($pos->snapshot($business, $paid)['can_send']);
        $this->assertSame('confirmed', $paid->fresh()->status);
        $this->assertSame($paid->restaurant_version, $paid->fresh()->restaurant_version);
        $this->assertSame('draft', $item->fresh()->kitchen_status);
        $this->assertSame(2.0, (float) $item->fresh()->quantity);
        $this->assertSame(1.0, (float) $item->fresh()->returned_quantity);
        $this->assertSame(100.0, (float) $paid->fresh()->credited);
        $this->assertSame(9.0, (float) $food->fresh()->stock);
        $this->assertSame([], app(KitchenTicketService::class)->orders($business));
        $this->assertSame(1, Payment::where('reference_type', 'order')->where('reference_id', $paid->id)->count());
        $this->assertSame(1, Journal::where('source_type', 'return')->count());
        $this->get('http://admin.returned-unsent.saas.test/sales/restaurant-pos/orders/'.$paid->id.'/kot')
            ->assertOk()->assertSee('Ticket unavailable: unsent food in this group has been returned.')->assertDontSee('RETURNED-SNACK');
    }

    public function test_partial_returns_do_not_rewrite_existing_queued_kitchen_ticket_history(): void
    {
        [$business, $owner] = $this->tenant('returned-queued', true);
        $food = $this->food($business, 'QUEUED-SNACK', 100, 10);
        $pos = app(RestaurantPosService::class);
        $input = $this->input($food, ['send_to_kitchen' => true]);
        $input['items'][0]['quantity'] = 2;
        $queued = $pos->save($business, $input);
        $paid = $pos->checkout($business, $queued->id, $this->payment($queued));
        $item = $paid->items()->first();
        app(Returns::class)->create($business, ['order_id' => $paid->id, 'items' => [['order_item_id' => $item->id, 'quantity' => 1]]]);
        $paid->refresh();
        $this->actingAs($owner, 'business_admin');

        $this->get('http://admin.returned-queued.saas.test/sales/restaurant-pos/orders/'.$paid->id.'/kot?batch=1')
            ->assertOk()->assertSee('QUEUED-SNACK')->assertDontSee('Ticket unavailable:');

        $preparing = app(KitchenTicketService::class)->update($business, $paid->id, $item->id, $paid->restaurant_version, 'preparing');
        $this->assertSame('preparing', $preparing->items()->first()->kitchen_status);
        $this->assertSame(2.0, (float) $item->fresh()->quantity);
        $this->assertSame(1.0, (float) $item->fresh()->returned_quantity);
        $this->assertSame(9.0, (float) $food->fresh()->stock);
    }

    private function tenant(string $domain, bool $navigation = false): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Restaurant', 'slug' => 'restaurant-'.$domain, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['user_id' => $owner->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Owner', 'email' => $owner->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        if ($navigation) {
            $module = Module::create(['name' => 'Sales', 'slug' => 'sales', 'approval_status' => 'Approved']);
            $menu = Menu::create(['module_id' => $module->id, 'name' => 'Restaurant POS', 'slug' => 'restaurant-pos', 'approval_status' => 'Approved']);
            BusinessCategoryModule::create(['business_category_id' => $category->id, 'module_id' => $module->id, 'approval_status' => 'Approved']);
            BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $menu->id, 'approval_status' => 'Approved']);
            app(BusinessSubscriptionService::class)->activateBasic($business);
        }

        return [$business, $admin];
    }

    private function food(Business $business, string $sku, float $price, float $stock = 0): Variant
    {
        $product = Product::create(['business_id' => $business->id, 'name' => $sku, 'sku' => $sku, 'price' => $price, 'cost' => 40, 'track_stock' => true]);
        $variant = Variant::create(['business_id' => $business->id, 'product_id' => $product->id, 'sku' => $sku.'-1', 'price' => $price, 'cost' => 40, 'stock' => 0]);
        if ($stock > 0) {
            DB::transaction(fn () => app(Inventory::class)->move($business, $variant, $stock, 'opening', 40, 'opening', $variant->id));
        }

        return $variant->fresh();
    }

    private function table(Business $business): DiningTable
    {
        $area = DiningArea::create(['business_id' => $business->id, 'name' => 'Main dining']);

        return DiningTable::create(['business_id' => $business->id, 'name' => 'Table 1', 'dining_area_id' => $area->id, 'capacity' => 4]);
    }

    private function input(Variant $food, array $overrides = []): array
    {
        return array_merge(['request_key' => (string) Str::uuid(), 'service_type' => 'takeaway', 'guest_count' => 1, 'items' => [['variant_id' => $food->id, 'quantity' => 1, 'modifier_ids' => [], 'kitchen_note' => '']]], $overrides);
    }

    private function payment(Order $order, array $overrides = []): array
    {
        return array_merge(['version' => $order->restaurant_version, 'expected_total' => (float) $order->total, 'payment_mode' => 'full', 'payment_method' => 'cash', 'tendered' => (float) $order->total, 'request_key' => (string) Str::uuid()], $overrides);
    }

    private function invalid(callable $action, string $field): void
    {
        try {
            $action();
            $this->fail('Expected validation error for '.$field);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
