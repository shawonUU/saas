<?php

namespace Tests\Feature;

use App\Models\Accounts\Journal;
use App\Models\Accounts\JournalLine;
use App\Models\Accounts\Payment;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCustomer;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\BusinessUserAccess;
use App\Models\ChildMenu;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\PaymentMethod;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Tax;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Public\Business\Admin\Configurations\Modifiers\Modifier;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Discount;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use App\Services\Public\Business\Admin\Finance\Payments;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets\KitchenTicketService;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RestaurantLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_kitchen_and_checkout_keep_the_same_rows_and_post_stock_once(): void
    {
        [$business] = $this->tenant('dining-one');
        $table = $this->table($business);
        $food = $this->food($business, 'STEAK', true, 300, 100, 10);
        $input = $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id]);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $input);
        $same = $pos->save($business, $input);
        $itemId = $draft->items()->first()->id;
        $this->assertSame($draft->id, $same->id);
        $this->assertSame('draft', $draft->status);
        $this->assertEquals(10, $food->fresh()->stock);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('journals', 0);

        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $this->assertSame('queued', $queued->items()->first()->kitchen_status);
        $this->assertEquals(10, $food->fresh()->stock);
        $this->assertDatabaseCount('journals', 0);
        $payment = $this->payment($queued);
        $posted = $pos->checkout($business, $queued->id, $payment);
        $replayed = $pos->checkout($business, $queued->id, $payment);
        $this->assertSame($draft->id, $posted->id);
        $this->assertSame($posted->id, $replayed->id);
        $this->assertSame($itemId, $posted->items()->first()->id);
        $this->assertSame('confirmed', $posted->status);
        $this->assertEquals(9, $food->fresh()->stock);
        $this->assertEquals(300, $posted->paid);
        $this->assertNull($posted->restaurant_closed_at);
        $this->assertEquals(1, Payment::where('reference_type', 'order')->count());
        $this->assertEquals(1, Journal::where('source_type', 'order')->count());
        $this->assertEquals(1, StockMovement::where('source_type', 'order')->count());

        $kitchen = app(KitchenTicketService::class);
        $prepared = $kitchen->update($business, $posted->id, $itemId, $posted->restaurant_version, 'preparing');
        $ready = $kitchen->update($business, $posted->id, $itemId, $prepared->restaurant_version, 'ready');
        $served = $pos->serve($business, $posted->id, $ready->restaurant_version);
        $closed = $pos->close($business, $posted->id, $served->restaurant_version);
        $this->assertNotNull($closed->restaurant_closed_at);
        $this->assertSame([], $kitchen->orders($business));
        $this->assertEquals(9, $food->fresh()->stock);
    }

    public function test_recipe_and_modifiers_use_authoritative_prices_and_balanced_shared_accounts(): void
    {
        [$business] = $this->tenant('recipe-price');
        $ingredient = $this->food($business, 'RICE', true, 0, 40, 10, true);
        $food = $this->food($business, 'MEAL', false, 200, 0);
        $this->recipe($business, $food, $ingredient, .25);
        $modifier = Modifier::create(['business_id' => $business->id, 'name' => 'Extra sauce', 'name_bn' => 'অতিরিক্ত সস', 'product_id' => $food->product_id, 'price' => 20]);
        $tax = Tax::create(['business_id' => $business->id, 'name' => 'VAT', 'rate' => 10, 'calculation_type' => 'exclusive']);
        Discount::create(['business_id' => $business->id, 'code' => 'TEN', 'type' => 'percent', 'value' => 10, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $input = $this->input($food, ['tax_id' => $tax->id, 'coupon_code' => 'TEN']);
        $input['items'][0] += ['modifier_ids' => [$modifier->id], 'price' => 1];
        $input['items'][0]['modifier_ids'] = [$modifier->id];
        $input['items'][0]['quantity'] = 2;
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $input);
        $this->assertEquals(435.6, $draft->total);
        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $food->update(['price' => 500, 'is_active' => false]);
        $food->product->update(['is_active' => false]);
        $modifier->update(['price' => 90, 'is_active' => false]);
        $posted = $pos->checkout($business, $queued->id, $this->payment($queued));
        $this->assertEquals(435.6, $posted->total);
        $this->assertEquals(9.5, $ingredient->fresh()->stock);
        $this->assertEquals(0, $food->fresh()->stock);
        $item = $posted->items()->first();
        $this->assertEquals(220, $item->price);
        $this->assertEquals(10, $item->cost);
        $this->assertSame((int) $ingredient->id, $item->recipe_snapshot[0]['ingredient_variant_id']);
        $this->assertEquals(.25, $item->recipe_snapshot[0]['quantity']);
        $this->assertDatabaseHas('stock_movements', ['source_type' => 'order', 'source_id' => $posted->id, 'kind' => 'ingredient-consumption', 'variant_id' => $ingredient->id, 'quantity' => -.5]);
        $this->assertEqualsWithDelta((float) JournalLine::sum('debit'), (float) JournalLine::sum('credit'), .001);
        $this->assertNotNull($posted->restaurant_closed_at);
    }

    public function test_tracked_prepared_food_consumes_finished_stock_without_consuming_recipe_again(): void
    {
        [$business] = $this->tenant('prepared');
        $ingredient = $this->food($business, 'FLOUR', true, 0, 20, 10, true);
        $food = $this->food($business, 'BREAD', true, 60, 15, 5);
        $this->recipe($business, $food, $ingredient, .5);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food));
        $posted = $pos->checkout($business, $draft->id, $this->payment($draft));
        $this->assertEquals(10, $ingredient->fresh()->stock);
        $this->assertEquals(4, $food->fresh()->stock);
        $this->assertSame([], $posted->items()->first()->recipe_snapshot);
        $this->assertEquals(0, StockMovement::where('kind', 'ingredient-consumption')->count());
    }

    public function test_shortage_rolls_back_all_items_payment_and_accounting(): void
    {
        [$business] = $this->tenant('shortage');
        $prepared = $this->food($business, 'SODA', true, 50, 20, 5);
        $ingredient = $this->food($business, 'CHEESE', true, 0, 100, .1, true);
        $food = $this->food($business, 'PIZZA', false, 250, 0);
        $this->recipe($business, $food, $ingredient, .5);
        $input = $this->input($prepared);
        $input['items'][] = ['variant_id' => $food->id, 'quantity' => 1, 'modifier_ids' => []];
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $input);
        $this->invalid(fn () => $pos->checkout($business, $draft->id, $this->payment($draft)), 'stock');
        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertEquals(5, $prepared->fresh()->stock);
        $this->assertEquals(.1, $ingredient->fresh()->stock);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('journals', 0);
        $this->assertEquals(0, StockMovement::where('source_type', 'order')->count());
        $this->assertNull($draft->fresh()->posted_at);
    }

    public function test_sent_recipe_is_frozen_and_fractional_food_cost_matches_actual_withdrawal(): void
    {
        [$business] = $this->tenant('frozen-recipe');
        $ingredient = $this->food($business, 'SPICE', true, 0, 1, 1000, true);
        $food = $this->food($business, 'PORTION', false, 5, 0);
        $recipe = $this->recipe($business, $food, $ingredient, .333);
        $input = $this->input($food);
        $input['items'][0]['quantity'] = 1000;
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $input);
        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $recipe->update(['quantity' => .5]);
        $ingredient->update(['cost' => 2]);
        $posted = $pos->checkout($business, $queued->id, $this->payment($queued));
        $this->assertEquals(667, $ingredient->fresh()->stock);
        $this->assertEquals(.666, (float) $posted->items()->first()->cost);
        $this->assertEquals(.333, $posted->items()->first()->recipe_snapshot[0]['quantity']);
        $cogsAccount = DB::table('accounts')->where('business_id', $business->id)->where('code', '5000')->value('id');
        $this->assertEquals(666, JournalLine::where('business_id', $business->id)->where('account_id', $cogsAccount)->sum('debit'));

        $drink = $this->food($business, 'DRINK', false, 10, 0);
        $new = $pos->save($business, $this->input($drink));
        $sentWithoutRecipe = $pos->send($business, $new->id, $new->restaurant_version);
        $this->recipe($business, $drink, $ingredient, 1);
        $pos->checkout($business, $new->id, $this->payment($sentWithoutRecipe));
        $this->assertEquals(667, $ingredient->fresh()->stock);
    }

    public function test_stale_edits_sent_mutation_and_invalid_kitchen_transitions_are_rejected(): void
    {
        [$business] = $this->tenant('stale');
        $food = $this->food($business, 'SOUP', false, 100, 0);
        $pos = app(RestaurantPosService::class);
        $input = $this->input($food);
        $draft = $pos->save($business, $input);
        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $stale = $this->editInput($pos, $business, $draft->fresh());
        $stale['version'] = 1;
        try {
            $pos->save($business, $stale, $draft->id);
            $this->fail('Expected a stale-version conflict.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $change = $this->editInput($pos, $business, $queued);
        $change['items'][0]['quantity'] = 9;
        $this->invalid(fn () => $pos->save($business, $change, $queued->id), 'items');
        $itemId = $queued->items()->first()->id;
        $kitchen = app(KitchenTicketService::class);
        $this->invalid(fn () => $kitchen->update($business, $queued->id, $itemId, $queued->restaurant_version, 'served'), 'status');
        $prepared = $kitchen->update($business, $queued->id, $itemId, $queued->restaurant_version, 'preparing');
        $this->invalid(fn () => $pos->voidItem($business, $queued->id, $itemId, $prepared->restaurant_version, 'Guest changed their mind'), 'item');
        $this->invalid(fn () => $pos->cancel($business, $queued->id, $prepared->restaurant_version, 'Guest left'), 'order');
        $this->assertEquals(1, $queued->items()->first()->quantity);
        $this->assertDatabaseCount('journals', 0);
    }

    public function test_queued_void_keeps_history_and_excludes_the_line_from_billing(): void
    {
        [$business] = $this->tenant('void');
        $food = $this->food($business, 'COFFEE', true, 100, 30, 5);
        $input = $this->input($food);
        $input['items'][] = ['variant_id' => $food->id, 'quantity' => 2, 'modifier_ids' => []];
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $input);
        $queued = $pos->send($business, $draft->id, $draft->restaurant_version);
        $itemId = $queued->items()->first()->id;
        $voided = $pos->voidItem($business, $draft->id, $itemId, $queued->restaurant_version, 'Duplicate item');
        $this->assertEquals(200, $voided->total);
        $posted = $pos->checkout($business, $draft->id, $this->payment($voided));
        $this->assertEquals(3, $food->fresh()->stock);
        $this->assertEquals(2, $posted->items()->count());
        $this->assertDatabaseHas('order_items', ['id' => $itemId, 'kitchen_status' => 'void', 'void_reason' => 'Duplicate item', 'line_total' => 0]);
    }

    public function test_table_reservation_seating_occupancy_and_completion_are_atomic(): void
    {
        [$business] = $this->tenant('reserved');
        $food = $this->food($business, 'TEA', false, 40, 0);
        $table = $this->table($business);
        $reservation = Reservation::create([
            'business_id' => $business->id, 'dining_table_id' => $table->id, 'customer_name' => 'Guest', 'guest_count' => 2,
            'starts_at' => now()->subMinutes(15), 'ends_at' => now()->addHour(), 'status' => 'confirmed',
            'request_key' => (string) Str::uuid(), 'approval_status' => 'Approved',
        ]);
        $pos = app(RestaurantPosService::class);
        $input = $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id, 'guest_count' => 2]);
        $this->invalid(fn () => $pos->save($business, $input), 'dining_table_id');
        $input['reservation_id'] = $reservation->id;
        $draft = $pos->save($business, $input);
        $this->assertSame('seated', $reservation->fresh()->status);
        $this->assertEquals($draft->id, $reservation->fresh()->order_id);
        $changedService = $this->editInput($pos, $business, $draft);
        unset($changedService['reservation_id']);
        $changedService['service_type'] = 'takeaway';
        $this->invalid(fn () => $pos->save($business, $changedService, $draft->id), 'reservation_id');
        $other = $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id]);
        $this->invalid(fn () => $pos->save($business, $other), 'dining_table_id');
        $cancelled = $pos->cancel($business, $draft->id, $draft->restaurant_version, 'Rescheduled');
        $this->assertSame('cancelled', $cancelled->status);
        $this->assertEquals(0, $pos->snapshot($business, $cancelled)['due']);
        $this->assertSame('confirmed', $reservation->fresh()->status);
        $this->assertNull($reservation->fresh()->order_id);
        $input['request_key'] = (string) Str::uuid();
        $new = $pos->save($business, $input);
        $new->update(['restaurant_workflow' => null]);
        $queued = $pos->send($business, $new->id, $new->restaurant_version);
        $itemId = $queued->items()->first()->id;
        $kitchen = app(KitchenTicketService::class);
        $prepared = $kitchen->update($business, $new->id, $itemId, $queued->restaurant_version, 'preparing');
        $ready = $kitchen->update($business, $new->id, $itemId, $prepared->restaurant_version, 'ready');
        $served = $pos->serve($business, $new->id, $ready->restaurant_version);
        $posted = $pos->checkout($business, $new->id, $this->payment($served));
        $this->assertNotNull($posted->restaurant_closed_at);
        $this->assertSame('completed', $reservation->fresh()->status);
    }

    public function test_credit_partial_and_configured_payment_retries_do_not_duplicate_receipts(): void
    {
        [$business] = $this->tenant('credit');
        $food = $this->food($business, 'LUNCH', false, 200, 0);
        $customer = BusinessCustomer::create(['business_id' => $business->id, 'name' => 'Regular Guest', 'approval_status' => 'Approved']);
        $mobile = PaymentMethod::create(['business_id' => $business->id, 'name' => 'Mobile wallet', 'method_type' => 'mobile']);
        $pos = app(RestaurantPosService::class);
        $draft = $pos->save($business, $this->input($food, ['customer_id' => $customer->id]));
        $pay = $this->payment($draft, ['payment_mode' => 'partial', 'tendered' => 50, 'payment_method_id' => $mobile->id, 'payment_reference' => 'MOB-1']);
        $partial = $pos->checkout($business, $draft->id, $pay);
        $this->assertEquals(50, $partial->paid);
        $this->assertDatabaseHas('payments', ['reference_id' => $draft->id, 'method' => 'mobile', 'configuration_method_id' => $mobile->id, 'amount' => 50]);
        $later = $this->payment($partial, ['tendered' => 200]);
        $paid = $pos->pay($business, $partial->id, $later);
        $again = $pos->pay($business, $partial->id, $later);
        $this->assertEquals(200, $paid->paid);
        $this->assertEquals(200, $again->paid);
        $this->assertEquals(50, $paid->restaurant_change);
        $this->assertEquals(250, $paid->restaurant_tendered);
        $this->assertDatabaseCount('payments', 2);
        $this->assertEqualsWithDelta((float) JournalLine::sum('debit'), (float) JournalLine::sum('credit'), .001);

        $walkIn = $pos->save($business, $this->input($food));
        $this->invalid(fn () => $pos->checkout($business, $walkIn->id, $this->payment($walkIn, ['payment_mode' => 'due', 'tendered' => 0])), 'customer_id');
        $this->assertSame('draft', $walkIn->fresh()->status);
        $this->invalid(fn () => app(Payments::class)->payOrder($business, $walkIn, 1, 'cash'), 'order');
    }

    public function test_returned_sale_can_receive_its_remaining_delivery_charge_without_overpayment(): void
    {
        [$business] = $this->tenant('returned-delivery');
        $food = $this->food($business, 'PACKED-MEAL', true, 100, 30, 1);
        $order = app(Sales::class)->create($business, [
            'items' => [['variant_id' => $food->id, 'quantity' => 1]],
            'shipping' => 25,
        ]);
        app(Returns::class)->create($business, [
            'order_id' => $order->id,
            'items' => [['order_item_id' => $order->items()->first()->id, 'quantity' => 1]],
        ]);
        $order->refresh();
        $this->assertSame('returned', $order->status);
        $this->assertEquals(100, $order->credited);
        $this->assertEquals(25, (float) $order->total - (float) $order->paid - (float) $order->credited);
        $payments = app(Payments::class);
        $this->invalid(fn () => $payments->payOrder($business, $order, 26, 'cash'), 'amount');
        $this->assertDatabaseCount('payments', 0);

        $payments->payOrder($business, $order, 25, 'cash');
        $order->refresh();
        $this->assertEquals(25, $order->paid);
        $this->assertEquals(0, (float) $order->total - (float) $order->paid - (float) $order->credited);
        $this->invalid(fn () => $payments->payOrder($business, $order, 1, 'cash'), 'amount');
        $this->assertDatabaseCount('payments', 1);
        $this->assertEqualsWithDelta((float) JournalLine::sum('debit'), (float) JournalLine::sum('credit'), .001);
    }

    public function test_foreign_tenant_resources_and_inactive_modifiers_are_rejected(): void
    {
        [$business] = $this->tenant('tenant-a');
        [$other] = $this->tenant('tenant-b');
        $food = $this->food($business, 'LOCAL', false, 100, 0);
        $foreign = $this->food($other, 'FOREIGN', false, 50, 0);
        $modifier = Modifier::create(['business_id' => $other->id, 'name' => 'Foreign extra', 'price' => 5]);
        $pos = app(RestaurantPosService::class);
        $this->invalid(fn () => $pos->save($business, $this->input($foreign)), 'items');
        $input = $this->input($food);
        $input['items'][0]['modifier_ids'] = [$modifier->id];
        $this->invalid(fn () => $pos->save($business, $input), 'items');
        $table = $this->table($other);
        $this->invalid(fn () => $pos->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $table->id])), 'dining_table_id');
        $foreignOrder = $pos->save($other, $this->input($foreign));
        try {
            $pos->checkout($business, $foreignOrder->id, $this->payment($foreignOrder));
            $this->fail('A cross-tenant order must not be loaded.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseCount('payments', 0);
        }
    }

    public function test_shared_online_sales_consume_recipes_and_drafts_keep_opening_date_at_posting(): void
    {
        [$business] = $this->tenant('online-food');
        $ingredient = $this->food($business, 'OIL', true, 0, 80, 10, true);
        $food = $this->food($business, 'FRIES', false, 80, 0);
        $this->recipe($business, $food, $ingredient, .1);
        $online = app(Sales::class)->create($business, ['request_key' => (string) Str::uuid(), 'items' => [['variant_id' => $food->id, 'quantity' => 2]]], 'online');
        $this->assertEquals(9.8, $ingredient->fresh()->stock);
        $this->assertEquals(8, $online->items()->first()->cost);
        $this->invalid(fn () => app(Sales::class)->create($business, ['items' => [['variant_id' => $ingredient->id, 'quantity' => 1]]], 'online'), 'items');
        $pos = app(RestaurantPosService::class);
        $this->travelTo(now()->startOfDay()->subMinute());
        $draft = $pos->save($business, $this->input($food));
        $createdAt = $draft->created_at->toDateString();
        $this->travel(2)->minutes();
        $posted = $pos->checkout($business, $draft->id, $this->payment($draft));
        $this->assertSame($createdAt, $posted->created_at->toDateString());
        $this->assertSame(now()->toDateString(), $posted->posted_at->toDateString());
        $this->assertNotSame($posted->created_at->toDateString(), $posted->posted_at->toDateString());
    }

    public function test_http_pages_json_permissions_and_tenant_scope_use_existing_navigation(): void
    {
        [$business, $owner, $module, $menu] = $this->tenant('http-food', true);
        $food = $this->food($business, 'BURGER', false, 180, 0);
        $this->actingAs($owner, 'business_admin');
        $base = 'http://admin.http-food.saas.test/sales/restaurant-pos';
        $this->get($base)->assertOk()->assertSee('Restaurant POS');
        $created = $this->postJson($base.'/orders', $this->input($food))->assertOk()->assertJsonPath('order.status', 'draft')->assertJsonPath('order.total', 180);
        $id = $created->json('order.id');
        $this->get($base.'/orders/'.$id.'/receipt')->assertOk()->assertSee('BURGER');
        $this->get($base.'/orders/'.$id.'/kot')->assertOk();
        $this->get('http://admin.http-food.saas.test/restaurant/kitchen-display/kitchen-tickets')->assertOk();
        $this->putJson($base.'/orders/'.$id, array_merge($this->input($food), ['version' => 99]))->assertConflict();
        $this->postJson($base.'/orders/'.$id.'/checkout', $this->payment(Order::findOrFail($id), ['expected_total' => 1]))->assertUnprocessable()->assertJsonValidationErrors('expected_total');
        $this->assertDatabaseCount('payments', 0);

        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Read only', 'is_owner' => false, 'approval_status' => 'Approved']);
        $global = User::create(['name' => 'Staff', 'email' => 'staff@http.test', 'approval_status' => 'Approved']);
        $staff = BusinessUser::create(['user_id' => $global->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Staff', 'email' => 'staff@http.test', 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        BusinessUserAccess::create(['business_user_id' => $staff->id, 'module_id' => $module->id, 'menu_id' => $menu->id, 'can_view' => true, 'can_create' => false, 'can_update' => false, 'approval_status' => 'Approved']);
        session()->flush();
        $this->actingAs($staff, 'business_admin')->getJson($base.'/orders/'.$id)->assertOk()->assertJsonMissingPath('order.password');
        $this->getJson($base.'/tables')->assertOk()->assertJsonPath('success', true);
        $this->postJson($base.'/orders', $this->input($food))->assertForbidden();
        $this->postJson($base.'/orders/'.$id.'/send', ['version' => 1])->assertForbidden();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_table_status_poll_scopes_tables_orders_and_current_reservations(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));
        [$business, $owner] = $this->tenant('table-state', true);
        [$otherBusiness] = $this->tenant('other-tables');
        $occupied = $this->table($business);
        $area = $occupied->diningArea;
        $available = DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $area->id, 'name' => 'Available', 'capacity' => 2]);
        $reserved = DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $area->id, 'name' => 'Reserved', 'capacity' => 6]);
        $otherTable = $this->table($otherBusiness);
        DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $area->id, 'name' => 'Inactive', 'capacity' => 2, 'is_active' => false]);
        DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $area->id, 'name' => 'Unapproved', 'capacity' => 2, 'approval_status' => 'Pending']);
        DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $otherTable->dining_area_id, 'name' => 'Wrong area', 'capacity' => 2]);
        foreach ([['is_active' => false], ['approval_status' => 'Pending']] as $attributes) {
            $unavailableArea = DiningArea::create(array_merge(['business_id' => $business->id, 'name' => 'Unavailable area'], $attributes));
            DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $unavailableArea->id, 'name' => 'Hidden table', 'capacity' => 2]);
        }
        $food = $this->food($business, 'TABLE-MEAL', false, 150, 0);
        $order = app(RestaurantPosService::class)->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $occupied->id, 'guest_count' => 2, 'send_to_kitchen' => true]));
        $currentReservation = Reservation::create([
            'business_id' => $business->id, 'dining_table_id' => $reserved->id, 'customer_name' => 'Reserved guest', 'guest_count' => 3,
            'customer_phone' => '01700000000',
            'starts_at' => now(), 'ends_at' => now()->addHour(), 'status' => 'confirmed', 'approval_status' => 'Approved', 'is_active' => true, 'request_key' => (string) Str::uuid(),
        ]);
        foreach ([
            ['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)],
            ['starts_at' => now()->subHour(), 'ends_at' => now()],
            ['is_active' => false], ['approval_status' => 'Pending'], ['deleted_at' => now()], ['status' => 'cancelled'],
            ['business_id' => $otherBusiness->id],
        ] as $attributes) {
            Reservation::create(array_merge([
                'business_id' => $business->id, 'dining_table_id' => $available->id, 'customer_name' => 'Not currently reserved',
                'starts_at' => now()->subMinutes(10), 'ends_at' => now()->addMinutes(50), 'approval_status' => 'Approved', 'is_active' => true, 'request_key' => (string) Str::uuid(),
            ], $attributes));
        }
        Order::create(['business_id' => $otherBusiness->id, 'order_no' => 'OTHER-TABLE-ORDER', 'channel' => 'restaurant', 'status' => 'draft', 'service_type' => 'dine_in', 'dining_table_id' => $available->id, 'subtotal' => 10, 'total' => 10]);

        $response = $this->actingAs($owner, 'business_admin')->getJson('http://admin.table-state.saas.test/sales/restaurant-pos/tables');

        $response->assertOk()->assertJsonPath('success', true)->assertJsonCount(3, 'tables')->assertHeaderContains('Cache-Control', 'no-store');
        $tables = collect($response->json('tables'))->keyBy('id');
        $this->assertSame($order->id, $tables[$occupied->id]['occupied_order_id']);
        $this->assertSame($order->order_no, $tables[$occupied->id]['order']['order_no']);
        $this->assertSame('queued', $tables[$occupied->id]['order']['items'][0]['kitchen_status']);
        $this->assertSame(2, $tables[$occupied->id]['order']['guest_count']);
        $this->assertSame(6, $tables[$reserved->id]['capacity']);
        $this->assertSame($currentReservation->id, $tables[$reserved->id]['reservation']['id']);
        $this->assertSame('Reserved guest', $tables[$reserved->id]['reservation']['customer_name']);
        $this->assertSame('01700000000', $tables[$reserved->id]['reservation']['customer_phone']);
        $this->assertNull($tables[$available->id]['reservation']);
        $this->assertNull($tables[$available->id]['order']);
        $this->assertNull($tables[$available->id]['occupied_order_id']);
        $this->assertSame(now()->toIso8601String(), $response->json('updated_at'));
    }

    public function test_table_status_poll_includes_old_occupied_checks_and_releases_closed_unpaid_tables(): void
    {
        [$business, $owner] = $this->tenant('busy-tables', true);
        $occupied = $this->table($business);
        $released = DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $occupied->dining_area_id, 'name' => 'Released', 'capacity' => 2]);
        $cancelled = DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $occupied->dining_area_id, 'name' => 'Cancelled', 'capacity' => 2]);
        $food = $this->food($business, 'OLD-CHECK', false, 75, 0);
        $oldOrder = app(RestaurantPosService::class)->save($business, $this->input($food, ['service_type' => 'dine_in', 'dining_table_id' => $occupied->id]));
        for ($index = 0; $index < 101; $index++) {
            Order::create(['business_id' => $business->id, 'order_no' => 'NEWER-'.$index, 'channel' => 'restaurant', 'status' => 'draft', 'service_type' => 'takeaway', 'subtotal' => 10, 'total' => 10]);
        }
        Order::create(['business_id' => $business->id, 'order_no' => 'CLOSED-UNPAID', 'channel' => 'restaurant', 'status' => 'confirmed', 'service_type' => 'dine_in', 'dining_table_id' => $released->id, 'subtotal' => 100, 'total' => 100, 'paid' => 0, 'restaurant_closed_at' => now()]);
        Order::create(['business_id' => $business->id, 'order_no' => 'CANCELLED-CHECK', 'channel' => 'restaurant', 'status' => 'cancelled', 'service_type' => 'dine_in', 'dining_table_id' => $cancelled->id, 'subtotal' => 30, 'total' => 30]);

        $response = $this->actingAs($owner, 'business_admin')->getJson('http://admin.busy-tables.saas.test/sales/restaurant-pos/tables');

        $response->assertOk()->assertJsonCount(3, 'tables');
        $tables = collect($response->json('tables'))->keyBy('id');
        $this->assertSame($oldOrder->id, $tables[$occupied->id]['occupied_order_id']);
        $this->assertSame('OLD-CHECK', $tables[$occupied->id]['order']['items'][0]['name']);
        $this->assertNull($tables[$released->id]['order']);
        $this->assertNull($tables[$cancelled->id]['order']);
    }

    public function test_table_status_poll_requires_authentication_and_pos_view_access(): void
    {
        [$business, $owner] = $this->tenant('private-tables', true);
        [$otherBusiness, $otherOwner] = $this->tenant('foreign-tables');
        $base = 'http://admin.private-tables.saas.test/sales/restaurant-pos/tables';
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'No POS access', 'is_owner' => false, 'approval_status' => 'Approved']);
        $global = User::create(['name' => 'Restricted', 'email' => 'restricted@tables.test', 'approval_status' => 'Approved']);
        $staff = BusinessUser::create(['user_id' => $global->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Restricted', 'email' => $global->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);

        $this->getJson($base)->assertUnauthorized();
        session()->flush();
        $this->actingAs($otherOwner, 'business_admin')->getJson($base)->assertForbidden();
        session()->flush();
        $this->actingAs($staff, 'business_admin')->getJson($base)->assertForbidden();
    }

    public function test_kitchen_poll_batches_snapshot_queries_as_order_count_grows(): void
    {
        [$business] = $this->tenant('busy-kitchen');
        $table = $this->table($business);
        $station = KitchenStation::create(['business_id' => $business->id, 'name' => 'Hot kitchen']);
        $food = $this->food($business, 'NOODLES', false, 120, 0);
        $food->product->update(['kitchen_station_id' => $station->id]);
        $pos = app(RestaurantPosService::class);
        $orders = collect();
        for ($index = 0; $index < 16; $index++) {
            $input = $this->input($food, ['send_to_kitchen' => true]);
            if ($index === 0) {
                $input['service_type'] = 'dine_in';
                $input['dining_table_id'] = $table->id;
            }
            $orders->push($pos->save($business, $input));
        }

        DB::enableQueryLog();
        try {
            DB::flushQueryLog();
            $single = $pos->snapshot($business, $orders->first());
            $singleCount = count(DB::getQueryLog());
            DB::flushQueryLog();
            $many = $pos->snapshots($business, $orders);
            $manyCount = count(DB::getQueryLog());
            DB::flushQueryLog();
            $kitchen = app(KitchenTicketService::class)->orders($business);
            $pollCount = count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        $this->assertCount(16, $many);
        $this->assertCount(16, $kitchen);
        $this->assertSame($single, $many[0]);
        $this->assertSame('Hot kitchen', $kitchen[0]['items'][0]['kitchen_station_name']);
        $this->assertLessThanOrEqual($singleCount, $manyCount, 'Snapshot reads should remain bounded when the order count grows.');
        $this->assertLessThanOrEqual($singleCount + 1, $pollCount, 'A kitchen poll adds only the order-list query.');
    }

    private function tenant(string $domain, bool $navigation = false): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['name' => 'Restaurant', 'slug' => 'restaurant-'.$domain, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $domain, 'domain' => $domain, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => 'Approved']);
        $admin = BusinessUser::create(['user_id' => $owner->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => 'Owner', 'email' => $owner->email, 'password' => 'secret-secret', 'approval_status' => 'Approved']);
        $module = null;
        $menu = null;
        if ($navigation) {
            $module = Module::create(['name' => 'Sales', 'slug' => 'sales', 'approval_status' => 'Approved']);
            $menu = Menu::create(['module_id' => $module->id, 'name' => 'Restaurant POS', 'slug' => 'restaurant-pos', 'approval_status' => 'Approved']);
            $restaurant = Module::create(['name' => 'Restaurant', 'slug' => 'restaurant', 'approval_status' => 'Approved']);
            $kitchen = Menu::create(['module_id' => $restaurant->id, 'name' => 'Kitchen display', 'slug' => 'kitchen-display', 'approval_status' => 'Approved']);
            $ticket = ChildMenu::create(['menu_id' => $kitchen->id, 'name' => 'Kitchen tickets', 'slug' => 'kitchen-tickets', 'approval_status' => 'Approved']);
            foreach ([$module, $restaurant] as $entry) {
                BusinessCategoryModule::create(['business_category_id' => $category->id, 'module_id' => $entry->id, 'approval_status' => 'Approved']);
            }
            foreach ([$menu, $kitchen] as $entry) {
                BusinessCategoryMenu::create(['business_category_id' => $category->id, 'menu_id' => $entry->id, 'approval_status' => 'Approved']);
            }
            BusinessCategoryChildMenu::create(['business_category_id' => $category->id, 'child_menu_id' => $ticket->id, 'approval_status' => 'Approved']);
            app(BusinessSubscriptionService::class)->activateBasic($business);
        }

        return [$business, $admin, $module, $menu];
    }

    private function food(Business $business, string $sku, bool $tracked, float $price, float $cost, float $stock = 0, bool $ingredient = false): Variant
    {
        $product = Product::create(['business_id' => $business->id, 'name' => $sku, 'name_bn' => 'খাবার', 'sku' => $sku, 'price' => $price, 'cost' => $cost, 'track_stock' => $tracked, 'is_ingredient' => $ingredient]);
        $variant = Variant::create(['business_id' => $business->id, 'product_id' => $product->id, 'sku' => $sku.'-1', 'price' => $price, 'cost' => $cost, 'stock' => 0]);
        if ($stock > 0) {
            DB::transaction(fn () => app(Inventory::class)->move($business, $variant, $stock, 'opening', $cost, 'opening', $variant->id));
        }

        return $variant->fresh();
    }

    private function table(Business $business): DiningTable
    {
        $area = DiningArea::create(['business_id' => $business->id, 'name' => 'Main dining']);

        return DiningTable::create(['business_id' => $business->id, 'name' => 'Table 1', 'dining_area_id' => $area->id, 'capacity' => 4]);
    }

    private function recipe(Business $business, Variant $food, Variant $ingredient, float $quantity): RecipeItem
    {
        return RecipeItem::create(['business_id' => $business->id, 'food_variant_id' => $food->id, 'ingredient_variant_id' => $ingredient->id, 'quantity' => $quantity, 'approval_status' => 'Approved']);
    }

    private function input(Variant $food, array $overrides = []): array
    {
        return array_merge(['request_key' => (string) Str::uuid(), 'service_type' => 'takeaway', 'guest_count' => 1, 'items' => [['variant_id' => $food->id, 'quantity' => 1, 'modifier_ids' => [], 'kitchen_note' => '']]], $overrides);
    }

    private function payment(Order $order, array $overrides = []): array
    {
        return array_merge(['version' => $order->restaurant_version, 'expected_total' => (float) $order->total, 'payment_mode' => 'full', 'payment_method' => 'cash', 'tendered' => (float) $order->total, 'request_key' => (string) Str::uuid()], $overrides);
    }

    private function editInput(RestaurantPosService $pos, Business $business, Order $order): array
    {
        $snapshot = $pos->snapshot($business, $order);
        $snapshot['request_key'] = (string) Str::uuid();
        $snapshot['items'] = array_map(fn (array $item): array => array_intersect_key($item, array_flip(['id', 'variant_id', 'quantity', 'modifier_ids', 'kitchen_note'])), $snapshot['items']);

        return $snapshot;
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
