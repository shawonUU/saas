<?php

namespace App\Services\Public\Business\Admin\Sales\RestaurantPos;

use App\Models\Accounts\Payment;
use App\Models\Business;
use App\Models\BusinessCustomer;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Public\Business\Admin\Finance\Payments;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantPosService
{
    public function __construct(private RestaurantWorkflow $workflow) {}

    public function find(Business $business, int $id, bool $lock = false): Order
    {
        $query = Order::where('business_id', $business->id)->where('channel', 'restaurant');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }

    public function checkVersion(Order $order, int $version): void
    {
        abort_if((int) $order->restaurant_version !== $version, 409, t('This order changed in another screen. Reload the latest order before continuing.', 'অন্য স্ক্রিনে অর্ডারটি পরিবর্তন হয়েছে। চালিয়ে যাওয়ার আগে সর্বশেষ অর্ডার লোড করুন।'));
    }

    public function save(Business $business, array $data, ?int $id = null): Order
    {
        return DB::transaction(function () use ($business, $data, $id): Order {
            $business = $this->lockBusiness($business);
            $order = $id ? $this->find($business, $id, true) : null;
            if (! $order) {
                $existing = Order::where('business_id', $business->id)->where('request_key', $data['request_key'])->first();
                if ($existing) {
                    if ($existing->channel !== 'restaurant') {
                        $this->fail('request_key', t('This reference belongs to another sale.', 'এই রেফারেন্সটি অন্য বিক্রয়ের জন্য ব্যবহার করা হয়েছে।'));
                    }

                    return $existing;
                }
            } else {
                $this->checkVersion($order, (int) $data['version']);
                $this->requireDraft($order);
            }
            $header = $this->header($business, $data, $order);
            $lines = app(RestaurantOrderPricing::class)->lines($business, $data['items'], $order, true);
            $totals = app(Sales::class)->totals($business, $data, array_sum(array_column($lines, 'amount')), 'restaurant');
            $values = array_merge($header, [
                'subtotal' => $totals['subtotal'], 'discount' => $totals['discount'], 'shipping' => $totals['shipping'],
                'tax' => $totals['tax'], 'total' => $totals['total'], 'coupon_code' => $totals['code'] ?: null,
                'restaurant_tax_id' => $data['tax_id'] ?? null,
                'restaurant_version' => $order ? $order->restaurant_version + 1 : 1,
            ]);
            if ($order) {
                $order->update($values);
            } else {
                $order = Order::create(array_merge($values, [
                    'business_id' => $business->id, 'channel' => 'restaurant', 'status' => 'draft',
                    'request_key' => $data['request_key'],
                    'restaurant_workflow' => $this->workflow->snapshot($business),
                    'order_no' => getGenerateCode(Order::where('business_id', $business->id), 'order_no', 'SO'),
                    'paid' => 0, 'credited' => 0,
                ]));
            }
            $keep = [];
            foreach ($lines as $line) {
                $item = $line['item'];
                if (! $item || $item->kitchen_status === 'draft') {
                    $attributes = [
                        'variant_id' => $line['variant']->id, 'name' => $line['product']->name,
                        'quantity' => $line['qty'], 'base_price' => $line['base_price'], 'price' => $line['price'],
                        'cost' => $line['cost'], 'line_total' => $line['amount'], 'modifier_snapshot' => $line['modifiers'],
                        'kitchen_station_id' => $line['station_id'], 'kitchen_note' => $line['note'] ?: null,
                    ];
                    if ($item) {
                        $item->update($attributes);
                    } else {
                        $item = $order->items()->create(array_merge($attributes, [
                            'business_id' => $business->id, 'returned_quantity' => 0, 'kitchen_status' => 'draft',
                        ]));
                    }
                }
                $keep[] = $item->id;
            }
            $order->items()->where('business_id', $business->id)->where('kitchen_status', 'draft')->whereNotIn('id', $keep)->delete();
            if (! empty($data['reservation_id'])) {
                Reservation::where('business_id', $business->id)->whereKey($data['reservation_id'])
                    ->update(['status' => 'seated', 'order_id' => $order->id]);
            }
            if (! empty($data['send_to_kitchen'])) {
                $this->queueItems($business, $order);
            }

            return $order->fresh();
        }, 3);
    }

    public function send(Business $business, int $id, int $version): Order
    {
        return DB::transaction(function () use ($business, $id, $version): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            $this->checkVersion($order, $version);
            $this->requireActive($order);
            if ($this->queueItems($business, $order)) {
                $order->increment('restaurant_version');
            }

            return $order->fresh();
        }, 3);
    }

    public function checkout(Business $business, int $id, array $data): Order
    {
        return DB::transaction(function () use ($business, $id, $data): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            if ($order->status === 'confirmed') {
                return $order;
            }
            $this->checkVersion($order, (int) $data['version']);
            $this->requireDraft($order);
            if ($order->dining_table_id) {
                DiningTable::where('business_id', $business->id)->lockForUpdate()->findOrFail($order->dining_table_id);
            }
            $paymentKey = Payment::where('business_id', $business->id)->where('request_key', $data['request_key'])->exists();
            if ($paymentKey) {
                $this->fail('request_key', t('Use a new reference for this payment.', 'এই পেমেন্টের জন্য নতুন রেফারেন্স ব্যবহার করুন।'));
            }
            $saleData = $this->pricingData($order);
            $saleData['expected_total'] = $data['expected_total'];
            $order = app(Sales::class)->create($business, $saleData, 'restaurant', $order);
            $payment = $this->paymentValues($business, $order, $data);
            if ($payment['paid'] > 0) {
                app(Payments::class)->payOrder($business, $order, $payment['paid'], $data['payment_method'] ?? 'cash', (string) ($data['payment_reference'] ?? ''), $data['request_key'], $data['payment_method_id'] ?? null);
            }
            if ($this->workflow->forOrder($order)['auto_send_to_kitchen']) {
                $this->queueItems($business, $order);
            }
            $order->update([
                'restaurant_tendered' => $payment['tendered'], 'restaurant_change' => $payment['change'],
                'restaurant_version' => $order->restaurant_version + 1,
            ]);
            $this->closeIfServed($business, $order);

            return $order->fresh();
        }, 3);
    }

    public function pay(Business $business, int $id, array $data): Order
    {
        return DB::transaction(function () use ($business, $id, $data): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            $existing = Payment::where('business_id', $business->id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                $amount = $data['payment_mode'] === 'partial' ? (float) $data['tendered'] : (float) $existing->amount;
                app(Payments::class)->payOrder($business, $order, $amount, $data['payment_method'] ?? 'cash', (string) ($data['payment_reference'] ?? ''), $data['request_key'], $data['payment_method_id'] ?? null);

                return $order->fresh();
            }
            $this->checkVersion($order, (int) $data['version']);
            if ($order->status !== 'confirmed') {
                $this->fail('order', t('Bill the order before accepting a later payment.', 'পরবর্তী পেমেন্ট গ্রহণের আগে অর্ডারের বিল তৈরি করুন।'));
            }
            if (abs((float) $order->total - (float) $data['expected_total']) > .001) {
                $this->fail('expected_total', t('The bill changed. Reload it before payment.', 'বিল পরিবর্তন হয়েছে। পেমেন্টের আগে আবার লোড করুন।'));
            }
            if ($data['payment_mode'] === 'due') {
                $this->fail('payment_mode', t('Enter a payment amount.', 'পেমেন্টের টাকা লিখুন।'));
            }
            $payment = $this->paymentValues($business, $order, $data);
            if ($payment['paid'] <= 0) {
                $this->fail('tendered', t('This bill has no outstanding amount.', 'এই বিলে কোনো বকেয়া নেই।'));
            }
            app(Payments::class)->payOrder($business, $order, $payment['paid'], $data['payment_method'] ?? 'cash', (string) ($data['payment_reference'] ?? ''), $data['request_key'], $data['payment_method_id'] ?? null);
            $order->update([
                'restaurant_tendered' => round((float) $order->restaurant_tendered + $payment['tendered'], 2),
                'restaurant_change' => round((float) $order->restaurant_change + $payment['change'], 2),
                'restaurant_version' => $order->restaurant_version + 1,
            ]);

            return $order->fresh();
        }, 3);
    }

    public function close(Business $business, int $id, int $version): Order
    {
        return DB::transaction(function () use ($business, $id, $version): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            if ($order->restaurant_closed_at) {
                return $order;
            }
            $this->checkVersion($order, $version);
            if ($order->status !== 'confirmed') {
                $this->fail('order', t('Bill the order before closing the table.', 'টেবিল বন্ধ করার আগে অর্ডারের বিল তৈরি করুন।'));
            }
            if ($order->service_type === 'dine_in' && $this->workflow->forOrder($order)['kitchen_mode'] === 'required' && ! $this->allServed($order)) {
                $this->fail('order', t('Bill and serve all items before closing the table.', 'টেবিল বন্ধ করার আগে বিল তৈরি করুন ও সব খাবার পরিবেশন করুন।'));
            }
            $order->increment('restaurant_version');
            $this->finish($business, $order);

            return $order->fresh();
        }, 3);
    }

    public function cancel(Business $business, int $id, int $version, string $reason): Order
    {
        return DB::transaction(function () use ($business, $id, $version, $reason): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            if ($order->status === 'cancelled') {
                return $order;
            }
            $this->checkVersion($order, $version);
            $this->requireDraft($order);
            if ($order->items()->where('business_id', $business->id)->whereIn('kitchen_status', ['preparing', 'ready', 'served'])->exists()) {
                $this->fail('order', t('Prepared food must be billed; apply an authorized discount if needed. Do not record the same consumed ingredients again as wastage.', 'প্রস্তুত খাবারের বিল করতে হবে; প্রয়োজনে অনুমোদিত ছাড় দিন। একই ব্যবহৃত উপকরণ আবার অপচয় হিসেবে লিখবেন না।'));
            }
            $order->items()->where('business_id', $business->id)->whereIn('kitchen_status', ['draft', 'queued'])
                ->update(['kitchen_status' => 'void', 'void_reason' => $reason, 'line_total' => 0]);
            $order->update(['status' => 'cancelled', 'restaurant_closed_at' => now(), 'restaurant_version' => $order->restaurant_version + 1]);
            foreach (Reservation::where('business_id', $business->id)->where('order_id', $order->id)->where('status', 'seated')->get() as $reservation) {
                $reservation->update(['status' => $reservation->ends_at->isPast() ? 'cancelled' : 'confirmed', 'order_id' => null]);
            }

            return $order->fresh();
        }, 3);
    }

    public function voidItem(Business $business, int $id, int $itemId, int $version, string $reason): Order
    {
        return DB::transaction(function () use ($business, $id, $itemId, $version, $reason): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            $this->checkVersion($order, $version);
            $this->requireDraft($order);
            $item = $order->items()->where('business_id', $business->id)->lockForUpdate()->findOrFail($itemId);
            if (! in_array($item->kitchen_status, ['draft', 'queued'], true)) {
                $this->fail('item', t('Only unprepared food can be voided. Bill prepared food with an authorized discount if needed; do not record the same ingredients again as wastage.', 'শুধু প্রস্তুত না হওয়া খাবার বাতিল করা যাবে। প্রয়োজনে অনুমোদিত ছাড় দিয়ে প্রস্তুত খাবারের বিল করুন; একই উপকরণ আবার অপচয় হিসেবে লিখবেন না।'));
            }
            $item->update(['kitchen_status' => 'void', 'void_reason' => $reason, 'line_total' => 0]);
            $subtotal = (float) $order->items()->where('business_id', $business->id)->where('kitchen_status', '!=', 'void')->sum('line_total');
            $data = $subtotal > 0 ? $this->pricingData($order) : [];
            $totals = app(Sales::class)->totals($business, $data, $subtotal, 'restaurant');
            $order->update([
                'subtotal' => $totals['subtotal'], 'discount' => $totals['discount'], 'shipping' => $totals['shipping'],
                'tax' => $totals['tax'], 'total' => $totals['total'], 'restaurant_version' => $order->restaurant_version + 1,
            ]);

            return $order->fresh();
        }, 3);
    }

    public function serve(Business $business, int $id, int $version): Order
    {
        return DB::transaction(function () use ($business, $id, $version): Order {
            $this->lockBusiness($business);
            $order = $this->find($business, $id, true);
            $this->checkVersion($order, $version);
            $this->requireActive($order);
            $ready = $order->items()->where('business_id', $business->id)->where('kitchen_status', 'ready')->lockForUpdate()->get();
            if ($ready->isEmpty()) {
                $this->fail('items', t('No ready items are waiting to be served.', 'পরিবেশনের জন্য প্রস্তুত কোনো খাবার নেই।'));
            }
            foreach ($ready as $item) {
                $item->update(['kitchen_status' => 'served', 'served_at' => now()]);
            }
            $order->increment('restaurant_version');

            return $order->fresh();
        }, 3);
    }

    public function snapshot(Business $business, Order $order): array
    {
        return $this->snapshots($business, collect([$order]))[0];
    }

    /** One bounded set of reads for an entire POS list or kitchen poll. */
    public function snapshots(Business $business, Collection $orders): array
    {
        if ($orders->isEmpty()) {
            return [];
        }
        abort_unless($orders->every(fn (Order $order): bool => (int) $order->business_id === (int) $business->id && $order->channel === 'restaurant'), 404);
        $items = OrderItem::where('business_id', $business->id)->whereIn('order_id', $orders->pluck('id'))
            ->with(['variant' => fn ($query) => $query->where('business_id', $business->id)
                ->with(['product' => fn ($product) => $product->where('business_id', $business->id)])])
            ->orderBy('id')->get();
        $stations = KitchenStation::withTrashed()->where('business_id', $business->id)->whereIn('id', $items->pluck('kitchen_station_id')->filter())->get()->keyBy('id');
        $tables = DiningTable::withTrashed()->where('business_id', $business->id)->whereIn('id', $orders->pluck('dining_table_id')->filter())
            ->with(['diningArea' => fn ($query) => $query->where('business_id', $business->id)])->get()->keyBy('id');
        $reservations = Reservation::where('business_id', $business->id)->whereIn('order_id', $orders->pluck('id'))->whereNull('deleted_at')->pluck('id', 'order_id');
        $grouped = $items->groupBy('order_id');

        return $orders->map(fn (Order $order): array => $this->serializeOrder($business, $order, $grouped->get($order->id, collect()), $stations, $tables->get($order->dining_table_id), $reservations->get($order->id)))->values()->all();
    }

    private function serializeOrder(Business $business, Order $order, Collection $items, Collection $stations, ?DiningTable $table, ?int $reservationId): array
    {
        $due = $order->status === 'cancelled' ? 0 : max(0, round((float) $order->total - (float) $order->paid - (float) $order->credited, 2));
        $activeItems = $items->where('kitchen_status', '!=', 'void');
        $unsentItems = $activeItems->where('kitchen_status', 'draft');
        $workflow = $this->workflow->forOrder($order);
        $active = in_array($order->status, ['draft', 'confirmed'], true);

        return [
            'id' => (int) $order->id, 'order_no' => $order->order_no, 'version' => (int) $order->restaurant_version,
            'status' => $order->status, 'service_type' => $order->service_type, 'dining_table_id' => $order->dining_table_id,
            'table' => $table ? ['name' => $table->name, 'name_bn' => $table->name_bn, 'area' => $table->diningArea?->name] : null,
            'guest_count' => (int) $order->guest_count, 'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name, 'customer_phone' => $order->customer_phone, 'customer_address' => $order->customer_address,
            'restaurant_notes' => $order->restaurant_notes, 'coupon_code' => $order->coupon_code, 'tax_id' => $order->restaurant_tax_id,
            'subtotal' => (float) $order->subtotal, 'discount' => (float) $order->discount, 'shipping' => (float) $order->shipping,
            'tax' => (float) $order->tax, 'total' => (float) $order->total, 'paid' => (float) $order->paid,
            'credited' => (float) $order->credited, 'due' => $due,
            'tendered' => (float) $order->restaurant_tendered, 'change' => (float) $order->restaurant_change,
            'closed_at' => $order->restaurant_closed_at?->toIso8601String(), 'created_at' => $order->created_at?->toIso8601String(),
            'posted_at' => $order->posted_at?->toIso8601String(),
            'reservation_id' => $reservationId,
            'workflow' => $workflow,
            'kitchen_tracking' => $activeItems->contains(fn (OrderItem $item): bool => in_array($item->kitchen_status, ['queued', 'preparing', 'ready', 'served'], true)),
            'can_edit' => $order->status === 'draft',
            'can_send' => $active && $workflow['kitchen_mode'] !== 'off' && $unsentItems->isNotEmpty()
                && ! $unsentItems->contains(fn (OrderItem $item): bool => (float) $item->returned_quantity > 0),
            'can_serve' => $active && $activeItems->contains('kitchen_status', 'ready'),
            'can_close' => ! $order->restaurant_closed_at && $order->status === 'confirmed'
                && ($order->service_type !== 'dine_in' || $workflow['kitchen_mode'] !== 'required'
                    || ($activeItems->isNotEmpty() && $activeItems->every(fn (OrderItem $item): bool => $item->kitchen_status === 'served'))),
            'items' => $items->map(function (OrderItem $item) use ($business, $stations): array {
                $variant = $item->variant;
                $product = $variant && (int) $variant->business_id === (int) $business->id ? $variant->product : null;
                $station = $stations->get($item->kitchen_station_id);

                return [
                    'id' => (int) $item->id, 'variant_id' => (int) $item->variant_id, 'name' => $item->name,
                    'name_bn' => $product && (int) $product->business_id === (int) $business->id ? $product->name_bn : null,
                    'sku' => $variant && (int) $variant->business_id === (int) $business->id ? $variant->sku : null,
                    'quantity' => (float) $item->quantity, 'base_price' => (float) ($item->base_price ?? $item->price),
                    'returned_quantity' => (float) $item->returned_quantity,
                    'price' => (float) $item->price, 'cost' => (float) $item->cost, 'line_total' => (float) $item->line_total,
                    'kitchen_status' => $item->kitchen_status, 'kitchen_station_id' => $item->kitchen_station_id,
                    'kitchen_station_name' => $station?->name, 'kitchen_station_name_bn' => $station?->name_bn,
                    'kitchen_batch' => (int) $item->kitchen_batch, 'kitchen_note' => $item->kitchen_note,
                    'modifier_ids' => array_column($item->modifier_snapshot ?? [], 'id'), 'modifiers' => $item->modifier_snapshot ?? [],
                    'void_reason' => $item->void_reason, 'sent_at' => $item->sent_at?->toIso8601String(),
                ];
            })->values()->all(),
        ];
    }

    /** Advisory catalog availability; posting still locks and validates the real shared stock ledger. */
    public function availability(Business $business, Collection $variants): array
    {
        $stocks = DB::table('warehouse_stocks as stock')->join('warehouses as warehouse', 'warehouse.id', '=', 'stock.warehouse_id')
            ->where('stock.business_id', $business->id)->where('warehouse.business_id', $business->id)->where('warehouse.code', 'MAIN')
            ->pluck('stock.quantity', 'stock.variant_id');
        $recipes = RecipeItem::where('business_id', $business->id)->whereIn('food_variant_id', $variants->pluck('id'))
            ->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at')->get()->groupBy('food_variant_id');
        $ingredientIds = $recipes->flatten()->pluck('ingredient_variant_id')->unique();
        $ingredients = Variant::with('product')->where('business_id', $business->id)->whereIn('id', $ingredientIds)
            ->where('is_active', true)->whereNull('deleted_at')->get()->keyBy('id');
        $result = [];
        foreach ($variants as $variant) {
            if ($variant->product->track_stock) {
                $result[$variant->id] = max(0, min((float) $variant->stock, (float) ($stocks[$variant->id] ?? 0)));

                continue;
            }
            $available = null;
            foreach ($recipes->get($variant->id, collect()) as $recipe) {
                $ingredient = $ingredients->get($recipe->ingredient_variant_id);
                $valid = $ingredient && $ingredient->product && (int) $ingredient->product->business_id === (int) $business->id
                    && $ingredient->product->is_active
                    && $ingredient->product->track_stock && $ingredient->product->is_ingredient && (float) $recipe->quantity > 0;
                $portions = $valid ? floor(max(0, min((float) $ingredient->stock, (float) ($stocks[$ingredient->id] ?? 0))) / (float) $recipe->quantity * 1000) / 1000 : 0;
                $available = $available === null ? $portions : min($available, $portions);
            }
            $result[$variant->id] = $available;
        }

        return $result;
    }

    public function closeIfServed(Business $business, Order $order): void
    {
        $automatic = $order->service_type !== 'dine_in'
            || ($this->workflow->forOrder($order)['table_release'] === 'automatic' && $this->allServed($order));
        if ($order->status === 'confirmed' && ! $order->restaurant_closed_at && $automatic) {
            $this->finish($business, $order);
        }
    }

    private function header(Business $business, array $data, ?Order $order): array
    {
        $service = $data['service_type'];
        $tableId = $service === 'dine_in' ? ($data['dining_table_id'] ?? null) : null;
        $guests = (int) ($data['guest_count'] ?? 1);
        if ($order && $order->items()->where('business_id', $business->id)->whereNotIn('kitchen_status', ['draft', 'void'])->exists()
            && ($order->service_type !== $service || (int) $order->dining_table_id !== (int) $tableId)) {
            $this->fail('dining_table_id', t('The service type and table cannot change after sending food to the kitchen.', 'রান্নাঘরে খাবার পাঠানোর পরে সেবার ধরন ও টেবিল পরিবর্তন করা যাবে না।'));
        }
        $linkedReservation = $order ? Reservation::where('business_id', $business->id)->where('order_id', $order->id)
            ->whereIn('status', ['confirmed', 'seated'])->lockForUpdate()->first() : null;
        if ($linkedReservation && ! empty($data['reservation_id']) && (int) $data['reservation_id'] !== (int) $linkedReservation->id) {
            $this->fail('reservation_id', t('This check is already linked to another reservation.', 'এই অর্ডারটি ইতিমধ্যে অন্য রিজার্ভেশনের সঙ্গে যুক্ত।'));
        }
        $reservationId = $linkedReservation?->id ?? ($data['reservation_id'] ?? null);
        $reservation = null;
        if ($reservationId) {
            $reservation = Reservation::where('business_id', $business->id)->whereIn('status', ['confirmed', 'seated'])
                ->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at')->lockForUpdate()->find($reservationId);
            if (! $reservation || $service !== 'dine_in' || (int) $reservation->dining_table_id !== (int) $tableId
                || ($reservation->order_id && (int) $reservation->order_id !== (int) $order?->id)) {
                $this->fail('reservation_id', t('This reservation cannot be seated on this check.', 'এই রিজার্ভেশনটি এই অর্ডারের সঙ্গে বসানো যাবে না।'));
            }
        }
        if ($service === 'dine_in') {
            $table = DiningTable::with('diningArea')->where('business_id', $business->id)->where('is_active', true)
                ->where('approval_status', 'Approved')->lockForUpdate()->find($tableId);
            if (! $table || ! $table->diningArea || (int) $table->diningArea->business_id !== (int) $business->id
                || ! $table->diningArea->is_active || $table->diningArea->approval_status !== 'Approved' || $guests > (int) $table->capacity) {
                $this->fail('dining_table_id', t('Select an available table with enough seats.', 'প্রয়োজনীয় আসনসহ একটি চালু টেবিল নির্বাচন করুন।'));
            }
            $occupied = Order::where('business_id', $business->id)->where('channel', 'restaurant')->where('dining_table_id', $tableId)
                ->whereIn('status', ['draft', 'confirmed'])->whereNull('restaurant_closed_at')
                ->when($order, fn ($query) => $query->where('id', '!=', $order->id))->exists();
            $reserved = Reservation::where('business_id', $business->id)->where('dining_table_id', $tableId)
                ->whereIn('status', ['confirmed', 'seated'])->where('is_active', true)->whereNull('deleted_at')
                ->where('starts_at', '<=', now())->where('ends_at', '>', now())
                ->when($reservation, fn ($query) => $query->where('id', '!=', $reservation->id))
                ->when($order, fn ($query) => $query->where(fn ($q) => $q->whereNull('order_id')->orWhere('order_id', '!=', $order->id)))->exists();
            if ($occupied || $reserved) {
                $this->fail('dining_table_id', t('This table is already occupied or reserved. Reload the table map.', 'টেবিলটি বর্তমানে ব্যবহৃত বা সংরক্ষিত। টেবিল ম্যাপ আবার লোড করুন।'));
            }
        }
        $customer = null;
        if (! empty($data['customer_id'])) {
            $customer = BusinessCustomer::where('business_id', $business->id)->where('is_active', true)
                ->where('approval_status', 'Approved')->find($data['customer_id']);
            if (! $customer) {
                $this->fail('customer_id', t('Customer is unavailable.', 'কাস্টমার পাওয়া যাচ্ছে না।'));
            }
        }
        $phone = $customer?->phone ?: ($data['customer_phone'] ?? null);
        $address = ($data['customer_address'] ?? null) ?: $customer?->address;
        if ($service === 'delivery' && (! $phone || ! $address)) {
            $this->fail('customer_address', t('Delivery requires a phone number and address.', 'ডেলিভারির জন্য ফোন নম্বর ও ঠিকানা প্রয়োজন।'));
        }

        return [
            'service_type' => $service, 'dining_table_id' => $tableId, 'guest_count' => $guests,
            'customer_id' => $customer?->id, 'customer_name' => $customer?->name ?: ($data['customer_name'] ?? null),
            'customer_phone' => $phone, 'customer_address' => $address, 'restaurant_notes' => $data['restaurant_notes'] ?? null,
        ];
    }

    private function pricingData(Order $order): array
    {
        return ['discount' => (float) $order->discount, 'coupon_code' => $order->coupon_code, 'shipping' => (float) $order->shipping, 'tax_id' => $order->restaurant_tax_id];
    }

    private function paymentValues(Business $business, Order $order, array $data): array
    {
        $due = max(0, round((float) $order->total - (float) $order->paid - (float) $order->credited, 2));
        $tendered = round((float) ($data['tendered'] ?? 0), 2);
        $mode = $data['payment_mode'];
        $method = app(Payments::class)->method($business, $data['payment_method'] ?? 'cash', $data['payment_method_id'] ?? null)['method'];
        $paid = $mode === 'due' ? 0 : ($mode === 'full' ? $due : $tendered);
        if (! in_array($mode, ['due', 'full', 'partial'], true) || ! is_finite($tendered) || $tendered < 0
            || ($mode === 'due' && $tendered !== 0.0)
            || ($mode === 'full' && ($tendered < $due || ($method !== 'cash' && abs($tendered - $due) > .001)))
            || ($mode === 'partial' && ($paid <= 0 || $paid >= $due))) {
            $this->fail('tendered', t('Check the received amount and payment mode.', 'গৃহীত টাকা ও পেমেন্টের ধরন যাচাই করুন।'));
        }
        if ($paid < $due && (! $order->customer_id || ! BusinessCustomer::where('business_id', $business->id)->where('is_active', true)
            ->where('approval_status', 'Approved')->whereKey($order->customer_id)->exists())) {
            $this->fail('customer_id', t('Select a registered customer for credit sales.', 'বাকিতে বিক্রয়ের জন্য নিবন্ধিত কাস্টমার নির্বাচন করুন।'));
        }

        return ['paid' => $paid, 'tendered' => $tendered, 'change' => $method === 'cash' ? max(0, round($tendered - $paid, 2)) : 0];
    }

    private function queueItems(Business $business, Order $order): bool
    {
        if ($this->workflow->forOrder($order)['kitchen_mode'] === 'off') {
            $this->fail('kitchen', t('Kitchen tracking is off for this order. You can print a kitchen ticket without sending it to the kitchen display.', 'এই অর্ডারের কিচেন ট্র্যাকিং বন্ধ। কিচেন ডিসপ্লেতে না পাঠিয়েও কিচেন টিকিট প্রিন্ট করতে পারবেন।'));
        }
        $items = $order->items()->where('business_id', $business->id)->where('kitchen_status', 'draft')->lockForUpdate()->get();
        if ($items->isEmpty()) {
            return false;
        }
        if ($items->contains(fn (OrderItem $item): bool => (float) $item->returned_quantity > 0)) {
            $this->fail('kitchen', t('Unsent food on this order has been returned. Review the return and create a corrected order before sending new food to the kitchen.', 'এই অর্ডারের কিচেনে না পাঠানো খাবার রিটার্ন হয়েছে। রিটার্ন যাচাই করে সঠিক নতুন অর্ডার তৈরি করুন, তারপর কিচেনে পাঠান।'));
        }
        $batch = (int) $order->items()->where('business_id', $business->id)->max('kitchen_batch') + 1;
        foreach ($items as $item) {
            $attributes = ['kitchen_status' => 'queued', 'kitchen_batch' => $batch, 'sent_at' => now()];
            if ($order->status === 'draft') {
                $food = Variant::with('product')->where('business_id', $business->id)->findOrFail($item->variant_id);
                $attributes['recipe_snapshot'] = app(RecipeConsumption::class)->snapshot($business, $food, $item->recipe_snapshot);
            }
            $item->update($attributes);
        }

        return true;
    }

    private function allServed(Order $order): bool
    {
        $items = $order->items()->where('business_id', $order->business_id)->where('kitchen_status', '!=', 'void')->get();

        return $items->isNotEmpty() && $items->every(fn (OrderItem $item): bool => $item->kitchen_status === 'served');
    }

    private function finish(Business $business, Order $order): void
    {
        $order->update(['restaurant_closed_at' => now()]);
        Reservation::where('business_id', $business->id)->where('order_id', $order->id)->where('status', 'seated')->update(['status' => 'completed']);
    }

    private function lockBusiness(Business $business): Business
    {
        return Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
    }

    private function requireDraft(Order $order): void
    {
        if ($order->status !== 'draft' || $order->restaurant_closed_at) {
            $this->fail('order', t('Only an open unbilled order can be changed. Use returns for a billed sale.', 'শুধু বিল না করা খোলা অর্ডার পরিবর্তন করা যাবে। বিল করা বিক্রয়ের জন্য রিটার্ন ব্যবহার করুন।'));
        }
    }

    private function requireActive(Order $order): void
    {
        if (! in_array($order->status, ['draft', 'confirmed'], true)) {
            $this->fail('order', t('This order is no longer active.', 'অর্ডারটি আর চালু নেই।'));
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
