<?php

namespace Tests\Feature;

use App\Models\Accounts\Journal;
use App\Models\Accounts\JournalLine;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use App\Models\BusinessUserAccess;
use App\Models\ChildMenu;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Public\Business\Admin\Configurations\Modifiers\Modifier;
use App\Models\Public\Business\Admin\Inventory\Movements\Wastage\Wastage;
use App\Models\Public\Business\Admin\Restaurant\Production\Production;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\BusinessSubscriptionService;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Inventory\Movements\Wastage\WastageService;
use App\Services\Public\Business\Admin\Restaurant\Dining\Reservations\ReservationService;
use App\Services\Public\Business\Admin\Restaurant\Production\ProductionService;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RestaurantOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservations_prevent_overlap_and_capacity_excess_but_allow_adjacent_bookings(): void
    {
        [$business] = $this->tenant('reservations');
        $table = $this->table($business);
        $service = app(ReservationService::class);
        $data = $this->reservationData($table);

        $reservation = $service->save($business, $data);
        $this->assertSame($reservation->id, $service->save($business, $data)->id);
        $this->assertRejected(fn () => $service->save($business, array_replace($data, [
            'request_key' => (string) Str::uuid(), 'starts_at' => today()->addDay()->setTime(18, 30), 'ends_at' => today()->addDay()->setTime(19, 30),
        ])), 'starts_at');
        $this->assertRejected(fn () => $service->save($business, array_replace($data, [
            'request_key' => (string) Str::uuid(), 'guest_count' => 5,
        ])), 'guest_count');

        $adjacent = $service->save($business, array_replace($data, [
            'request_key' => (string) Str::uuid(), 'starts_at' => today()->addDay()->setTime(19, 0), 'ends_at' => today()->addDay()->setTime(20, 0),
        ]));
        $this->assertNotSame($reservation->id, $adjacent->id);
        $this->assertDatabaseCount('restaurant_reservations', 2);
    }

    public function test_reservations_cannot_use_another_tenants_table_or_edit_a_completed_booking(): void
    {
        [$business] = $this->tenant('reservationtenant');
        [$other] = $this->tenant('reservationother');
        $foreign = $this->table($other);
        try {
            app(ReservationService::class)->save($business, $this->reservationData($foreign));
            $this->fail('A foreign table must be rejected.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseCount('restaurant_reservations', 0);
        }

        $reservation = app(ReservationService::class)->save($business, $this->reservationData($this->table($business)));
        $reservation->update(['status' => 'completed']);
        $this->assertRejected(fn () => app(ReservationService::class)->save($business, ['status' => 'confirmed'], $reservation->id), 'status');
        $this->assertSame('completed', $reservation->fresh()->status);
    }

    public function test_seated_reservation_cannot_release_a_table_while_its_order_is_open(): void
    {
        [$business] = $this->tenant('seated');
        $table = $this->table($business);
        $reservation = app(ReservationService::class)->save($business, $this->reservationData($table));
        $order = $this->order($business, ['dining_table_id' => $table->id, 'status' => 'draft']);
        $reservation->update(['status' => 'seated', 'order_id' => $order->id]);

        $this->assertRejected(fn () => app(ReservationService::class)->save($business, ['status' => 'completed'], $reservation->id), 'status');
        $this->assertRejected(fn () => app(ReservationService::class)->save($business, ['status' => 'cancelled'], $reservation->id), 'status');
        $order->update(['restaurant_closed_at' => now()]);
        $completed = app(ReservationService::class)->save($business, ['status' => 'completed'], $reservation->id);
        $this->assertSame('completed', $completed->status);
    }

    public function test_a_confirmed_reservation_cannot_attach_an_order_without_seating(): void
    {
        [$business] = $this->tenant('reservationlink');
        $table = $this->table($business);
        $reservation = app(ReservationService::class)->save($business, $this->reservationData($table));
        $order = $this->order($business, ['dining_table_id' => $table->id, 'status' => 'draft']);

        $this->assertRejected(fn () => app(ReservationService::class)->save($business, ['order_id' => $order->id], $reservation->id), 'order_id');
        $this->assertNull($reservation->fresh()->order_id);
    }

    public function test_production_uses_the_common_stock_ledger_once_and_updates_weighted_food_cost(): void
    {
        [$business] = $this->tenant('production');
        $ingredient = $this->variant($business, 'Rice', true, 10, 20);
        $food = $this->variant($business, 'Prepared rice', false, 40, 2);
        $this->recipe($business, $food, $ingredient, 2);
        $warehouse = app(Inventory::class)->main($business);
        $data = ['food_variant_id' => $food->id, 'warehouse_id' => $warehouse->id,
            'quantity' => 3, 'request_key' => (string) Str::uuid()];

        $production = app(ProductionService::class)->post($business, $data);
        $this->assertSame($production->id, app(ProductionService::class)->post($business, $data)->id);
        $this->assertEquals(14, $ingredient->fresh()->stock);
        $this->assertEquals(5, $food->fresh()->stock);
        $this->assertEquals(28, $food->fresh()->cost);
        $this->assertEquals(60, $production->total_cost);
        $this->assertCount(1, $production->ingredients);
        $this->assertSame(2, StockMovement::where('business_id', $business->id)->where('source_type', 'restaurant_production')->count());
        $this->assertSame(1, Production::where('business_id', $business->id)->count());
        $this->assertSame(0, Journal::where('source_type', 'restaurant_production')->count());
    }

    public function test_production_stock_shortage_rolls_back_the_entire_batch(): void
    {
        [$business] = $this->tenant('shortage');
        $ingredient = $this->variant($business, 'Flour', true, 10, 1);
        $food = $this->variant($business, 'Bread', false, 40, 0);
        $this->recipe($business, $food, $ingredient, 2);
        $data = ['food_variant_id' => $food->id, 'warehouse_id' => app(Inventory::class)->main($business)->id,
            'quantity' => 1, 'request_key' => (string) Str::uuid()];

        $this->assertRejected(fn () => app(ProductionService::class)->post($business, $data), 'stock');
        $this->assertEquals(1, $ingredient->fresh()->stock);
        $this->assertEquals(0, $food->fresh()->stock);
        $this->assertEquals(40, $food->fresh()->cost);
        $this->assertDatabaseCount('restaurant_productions', 0);
        $this->assertSame(0, StockMovement::where('source_type', 'restaurant_production')->count());
    }

    public function test_production_rejects_a_foreign_warehouse_without_any_stock_change(): void
    {
        [$business] = $this->tenant('warehouseowner');
        [$other] = $this->tenant('warehouseother');
        $ingredient = $this->variant($business, 'Milk', true, 10, 5);
        $food = $this->variant($business, 'Dessert', false, 0, 0);
        $this->recipe($business, $food, $ingredient, 1);

        try {
            app(ProductionService::class)->post($business, ['food_variant_id' => $food->id,
                'warehouse_id' => app(Inventory::class)->main($other)->id, 'quantity' => 1, 'request_key' => (string) Str::uuid()]);
            $this->fail('A foreign warehouse must be rejected.');
        } catch (ModelNotFoundException) {
            $this->assertEquals(5, $ingredient->fresh()->stock);
            $this->assertDatabaseCount('restaurant_productions', 0);
        }
    }

    public function test_wastage_posts_a_single_balanced_expense_and_stock_deduction(): void
    {
        [$business] = $this->tenant('wastage');
        $ingredient = $this->variant($business, 'Vegetables', true, 10, 10);
        $data = ['variant_id' => $ingredient->id, 'warehouse_id' => app(Inventory::class)->main($business)->id,
            'quantity' => 2.5, 'reason' => 'Spoiled during storage', 'request_key' => (string) Str::uuid()];

        $record = app(WastageService::class)->post($business, $data);
        $this->assertSame($record->id, app(WastageService::class)->post($business, $data)->id);
        $this->assertEquals(7.5, $ingredient->fresh()->stock);
        $journal = Journal::where('business_id', $business->id)->where('source_type', 'restaurant_wastage')->sole();
        $this->assertEquals(25, JournalLine::where('journal_id', $journal->id)->sum('debit'));
        $this->assertEquals(25, JournalLine::where('journal_id', $journal->id)->sum('credit'));
        $this->assertSame(1, Wastage::count());
        $this->assertSame(1, StockMovement::where('source_type', 'restaurant_wastage')->count());

        $this->assertRejected(fn () => app(WastageService::class)->post($business, array_replace($data, [
            'quantity' => 20, 'request_key' => (string) Str::uuid(),
        ])), 'stock');
        $this->assertSame(1, Wastage::count());
        $this->assertSame(1, Journal::where('source_type', 'restaurant_wastage')->count());
        $this->assertEquals(7.5, $ingredient->fresh()->stock);
    }

    public function test_modifier_actions_require_granular_permission_and_scope_products_and_records(): void
    {
        [$business, $staff] = $this->tenant('modifierstaff', false);
        [$module, $menu] = $this->feature($business, 'configurations', 'modifiers');
        $access = BusinessUserAccess::create(['business_user_id' => $staff->id, 'module_id' => $module->id,
            'menu_id' => $menu->id, 'can_view' => true, 'can_create' => false, 'can_update' => true,
            'is_active' => true, 'approval_status' => 'Approved']);
        $url = $this->url($business, '/configurations/modifiers');
        $payload = ['name' => 'Extra cheese', 'name_bn' => 'অতিরিক্ত চিজ', 'price' => 20, 'is_active' => 1];
        $this->actingAs($staff, 'business_admin')->post($url, $payload)->assertForbidden();
        $access->update(['can_create' => true]);

        [$other] = $this->tenant('modifierother');
        $foreign = $this->variant($other, 'Foreign food', false, 10, 0);
        $this->post($url, $payload + ['product_id' => $foreign->product_id])->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('config_modifiers', 0);
        $this->post($url, $payload)->assertRedirect();
        $this->assertDatabaseHas('config_modifiers', ['business_id' => $business->id, 'name' => 'Extra cheese']);
        $foreignModifier = Modifier::create(['business_id' => $other->id, 'name' => 'Other modifier', 'price' => 5]);
        $this->put($url.'/'.$foreignModifier->id, $payload)->assertNotFound();
        $this->assertSame('Other modifier', $foreignModifier->fresh()->name);
    }

    public function test_food_cost_uses_bill_date_and_excludes_drafts_voids_and_returned_quantity(): void
    {
        [$business, $user] = $this->tenant('foodreport');
        $this->feature($business, 'restaurant', 'reports', 'food-cost-report');
        $food = $this->variant($business, 'Rice plate', false, 30, 2);
        $posted = $this->order($business, ['subtotal' => 200, 'discount' => 21, 'total' => 179,
            'created_at' => now()->subMonth(), 'posted_at' => now()]);
        $this->item($posted, $food, ['quantity' => 2, 'line_total' => 200]);
        DB::transaction(fn () => app(Inventory::class)->move($business, $food, -2, 'sale', 30, 'order', $posted->id));
        app(Returns::class)->create($business, ['order_id' => $posted->id,
            'items' => [['order_item_id' => $posted->items()->firstOrFail()->id, 'quantity' => 1]]]);
        $this->item($posted, $food, ['cost' => 999, 'kitchen_status' => 'void']);
        $this->item($this->order($business, ['status' => 'draft']), $food, ['price' => 999, 'line_total' => 999, 'cost' => 900]);
        $this->item($this->order($business, ['status' => 'cancelled']), $food);

        $response = $this->actingAs($user, 'business_admin')->get($this->url($business,
            '/restaurant/reports/food-cost-report?from='.today()->toDateString().'&to='.today()->toDateString()));
        $response->assertOk()->assertViewHas('totals', fn (array $totals): bool => $totals['quantity'] == 1
            && $totals['revenue'] == 89.5 && $totals['cost'] == 30 && $totals['margin'] == 59.5);
        $response->assertViewHas('rows', fn (array $rows): bool => count($rows) === 1 && $rows[0]['variant_id'] === $food->id);
    }

    public function test_schema_updates_can_run_again_without_erasing_existing_business_data(): void
    {
        [$business] = $this->tenant('migrationdata');
        $order = $this->order($business, ['status' => 'draft']);
        $reservation = app(ReservationService::class)->save($business, $this->reservationData($this->table($business)));
        foreach (glob(database_path('migrations/2026_11_25_00001*.php')) as $path) {
            $migration = require $path;
            $migration->up();
        }

        $this->assertSame('draft', $order->fresh()->status);
        $this->assertSame('confirmed', $reservation->fresh()->status);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('restaurant_reservations', 1);
    }

    public function test_food_cost_retains_fractional_recipe_unit_cost_when_summing_portions(): void
    {
        [$business, $user] = $this->tenant('fractionalcost');
        $this->feature($business, 'restaurant', 'reports', 'food-cost-report');
        $food = $this->variant($business, 'Small portion', false, 0, 0);
        $ingredient = $this->variant($business, 'Ingredient', true, 1.01, 0);
        $order = $this->order($business, ['subtotal' => 1000, 'total' => 1000, 'posted_at' => now()]);
        $this->item($order, $food, ['quantity' => 1000, 'price' => 1, 'line_total' => 1000, 'cost' => .33633,
            'recipe_snapshot' => [['ingredient_variant_id' => $ingredient->id, 'quantity' => .333, 'cost' => 1.01]]]);

        $this->actingAs($user, 'business_admin')->get($this->url($business, '/restaurant/reports/food-cost-report'))
            ->assertOk()->assertViewHas('totals', fn (array $totals): bool => $totals['cost'] == 336.33 && $totals['margin'] == 663.67);
        $this->assertEquals(.33633, $order->items()->firstOrFail()->cost);
    }

    public function test_food_cost_matches_posted_cogs_after_cooked_and_finished_stock_refunds(): void
    {
        [$business, $user] = $this->tenant('refundedcost');
        $this->feature($business, 'restaurant', 'reports', 'food-cost-report');
        $ingredient = $this->variant($business, 'Cooking ingredient', true, 10, 10);
        $cooked = $this->variant($business, 'Cooked food', false, 0, 0);
        $cooked->product->update(['track_stock' => false]);
        $this->recipe($business, $cooked, $ingredient, .5);
        $prepared = $this->variant($business, 'Prepared stock', false, 8, 5);
        $untracked = $this->variant($business, 'Untracked service', false, 50, 0);
        $untracked->product->update(['track_stock' => false]);

        $cookedOrder = $this->bill($business, $cooked, 2);
        $preparedOrder = $this->bill($business, $prepared, 2);
        $this->bill($business, $untracked, 1);
        app(Returns::class)->create($business, ['order_id' => $cookedOrder->id,
            'items' => [['order_item_id' => $cookedOrder->items()->firstOrFail()->id, 'quantity' => 2]]]);
        app(Returns::class)->create($business, ['order_id' => $preparedOrder->id,
            'items' => [['order_item_id' => $preparedOrder->items()->firstOrFail()->id, 'quantity' => 1]]]);

        $this->assertEquals(9, $ingredient->fresh()->stock);
        $this->assertEquals(4, $prepared->fresh()->stock);
        $cogs = DB::table('journal_lines')->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journal_lines.business_id', $business->id)->where('accounts.code', '5000')
            ->selectRaw('SUM(journal_lines.debit - journal_lines.credit) as amount')->first()->amount;
        $this->assertEquals(18, $cogs);
        $url = $this->url($business, '/restaurant/reports/food-cost-report');
        $this->actingAs($user, 'business_admin')->get($url)->assertOk()
            ->assertViewHas('totals', fn (array $totals): bool => $totals['cost'] == $cogs && $totals['quantity'] == 2
                && $totals['revenue'] == 200 && $totals['margin'] == 182)
            ->assertViewHas('rows', function (array $rows) use ($cooked, $prepared, $untracked): bool {
                $rows = collect($rows)->keyBy('variant_id');

                return $rows[$cooked->id]['quantity'] == 0 && $rows[$cooked->id]['cost'] == 10
                    && $rows[$cooked->id]['margin'] == -10 && $rows[$cooked->id]['cost_percent'] === null
                    && $rows[$prepared->id]['cost'] == 8 && $rows[$untracked->id]['cost'] == 0;
            });

        app(Returns::class)->create($business, ['order_id' => $preparedOrder->id,
            'items' => [['order_item_id' => $preparedOrder->items()->firstOrFail()->id, 'quantity' => 1]]]);
        $this->get($url)->assertOk()->assertViewHas('totals', fn (array $totals): bool => $totals['cost'] == 10
            && $totals['quantity'] == 1 && $totals['revenue'] == 100 && $totals['margin'] == 90);
        $this->assertEquals(5, $prepared->fresh()->stock);
    }

    private function tenant(string $domain, bool $owner = true): array
    {
        $global = User::create(['name' => 'Owner', 'email' => $domain.'@test.local', 'approval_status' => 'Approved']);
        $category = BusinessCategory::create(['slug' => $domain, 'name' => 'Restaurant', 'is_active' => true, 'approval_status' => 'Approved']);
        $business = Business::create(['owner_id' => $global->id, 'category_id' => $category->id,
            'name' => $domain, 'domain' => $domain, 'is_active' => true, 'approval_status' => 'Approved']);
        $role = BusinessRole::create(['business_id' => $business->id, 'name' => $owner ? 'Owner' : 'Staff',
            'is_owner' => $owner, 'is_active' => true, 'approval_status' => 'Approved']);
        $user = BusinessUser::create(['business_id' => $business->id, 'user_id' => $global->id, 'role_id' => $role->id,
            'name' => $domain, 'email' => $domain.'@test.local', 'password' => 'test-password-123',
            'is_active' => true, 'approval_status' => 'Approved']);

        return [$business, $user];
    }

    private function feature(Business $business, string $moduleSlug, string $menuSlug, ?string $childSlug = null): array
    {
        $module = Module::firstOrCreate(['slug' => $moduleSlug], ['name' => $moduleSlug, 'is_active' => true, 'approval_status' => 'Approved']);
        $menu = Menu::firstOrCreate(['module_id' => $module->id, 'slug' => $menuSlug], ['name' => $menuSlug, 'is_active' => true, 'approval_status' => 'Approved']);
        BusinessCategoryModule::firstOrCreate(['business_category_id' => $business->category_id, 'module_id' => $module->id], ['approval_status' => 'Approved']);
        BusinessCategoryMenu::firstOrCreate(['business_category_id' => $business->category_id, 'menu_id' => $menu->id], ['approval_status' => 'Approved']);
        if ($childSlug) {
            $child = ChildMenu::firstOrCreate(['menu_id' => $menu->id, 'slug' => $childSlug], ['name' => $childSlug, 'is_active' => true, 'approval_status' => 'Approved']);
            BusinessCategoryChildMenu::firstOrCreate(['business_category_id' => $business->category_id, 'child_menu_id' => $child->id], ['approval_status' => 'Approved']);
        }
        app(BusinessSubscriptionService::class)->activateBasic($business);

        return [$module, $menu];
    }

    private function table(Business $business): DiningTable
    {
        $area = DiningArea::create(['business_id' => $business->id, 'name' => 'Main dining']);

        return DiningTable::create(['business_id' => $business->id, 'dining_area_id' => $area->id, 'name' => 'Table 1', 'capacity' => 4]);
    }

    private function reservationData(DiningTable $table): array
    {
        return ['dining_table_id' => $table->id, 'customer_name' => 'Guest', 'guest_count' => 2,
            'starts_at' => today()->addDay()->setTime(18, 0), 'ends_at' => today()->addDay()->setTime(19, 0), 'request_key' => (string) Str::uuid()];
    }

    private function variant(Business $business, string $name, bool $ingredient, float $cost, float $stock): Variant
    {
        $sku = 'ITEM-'.Str::random(8);
        $product = Product::create(['business_id' => $business->id, 'name' => $name, 'name_bn' => 'খাবার', 'sku' => $sku,
            'price' => 100, 'cost' => $cost, 'track_stock' => true, 'is_ingredient' => $ingredient, 'is_active' => true]);
        $variant = Variant::create(['business_id' => $business->id, 'product_id' => $product->id, 'sku' => $sku.'-V',
            'price' => 100, 'cost' => $cost, 'stock' => 0, 'is_active' => true]);
        if ($stock > 0) {
            DB::transaction(fn () => app(Inventory::class)->move($business, $variant, $stock, 'opening', $cost, 'opening', $variant->id));
        }

        return $variant->refresh();
    }

    private function recipe(Business $business, Variant $food, Variant $ingredient, float $quantity): RecipeItem
    {
        return RecipeItem::create(['business_id' => $business->id, 'food_variant_id' => $food->id,
            'ingredient_variant_id' => $ingredient->id, 'quantity' => $quantity, 'is_active' => true, 'approval_status' => 'Approved']);
    }

    private function order(Business $business, array $attributes = []): Order
    {
        return Order::create(array_replace(['business_id' => $business->id, 'order_no' => 'REST-'.Str::random(10),
            'channel' => 'restaurant', 'status' => 'confirmed', 'subtotal' => 100, 'total' => 100], $attributes));
    }

    private function bill(Business $business, Variant $variant, float $quantity): Order
    {
        $service = app(RestaurantPosService::class);
        $order = $service->save($business, ['service_type' => 'takeaway', 'guest_count' => 1,
            'request_key' => (string) Str::uuid(), 'items' => [['variant_id' => $variant->id, 'quantity' => $quantity]]]);

        return $service->checkout($business, $order->id, ['version' => $order->restaurant_version,
            'expected_total' => $order->total, 'payment_mode' => 'full', 'payment_method' => 'cash',
            'tendered' => $order->total, 'request_key' => (string) Str::uuid()]);
    }

    private function item(Order $order, Variant $variant, array $attributes = []): void
    {
        $order->items()->create(array_replace(['business_id' => $order->business_id, 'variant_id' => $variant->id,
            'name' => $variant->product->name, 'quantity' => 1, 'returned_quantity' => 0, 'price' => 100,
            'cost' => 30, 'line_total' => 100, 'kitchen_status' => 'served'], $attributes));
    }

    private function url(Business $business, string $path): string
    {
        return 'http://admin.'.$business->domain.'.'.config('saas.domain').$path;
    }

    private function assertRejected(callable $action, string $field): void
    {
        try {
            $action();
            $this->fail('Expected a validation error for '.$field.'.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
