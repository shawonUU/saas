<?php

namespace App\Services\Public\Business\Admin\Sales\RestaurantPos;

use App\Models\Business;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\Variant;
use App\Models\Public\Business\Admin\Configurations\Modifiers\Modifier;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use Illuminate\Validation\ValidationException;

class RestaurantOrderPricing
{
    public function quote(Business $business, array $data, ?Order $order = null): array
    {
        $lines = $this->lines($business, $data['items'], $order);
        $totals = app(Sales::class)->totals($business, $data, array_sum(array_column($lines, 'amount')), 'restaurant');

        return array_merge($totals, ['items' => array_map(fn (array $line): array => [
            'id' => $line['item']?->id,
            'variant_id' => $line['variant']->id,
            'quantity' => $line['qty'],
            'base_price' => $line['base_price'],
            'price' => $line['price'],
            'line_total' => $line['amount'],
            'modifiers' => $line['modifiers'],
        ], $lines)]);
    }

    public function storedLines(Business $business, Order $order): array
    {
        $rows = $order->items()->where('business_id', $business->id)->orderBy('id')->get()
            ->map(fn (OrderItem $item): array => [
                'id' => $item->id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'kitchen_note' => $item->kitchen_note,
                'modifier_ids' => array_column($item->modifier_snapshot ?? [], 'id'),
            ])->all();

        return $this->lines($business, $rows, $order, true);
    }

    /** Sent lines use immutable server snapshots; only unsent rows use today's catalog prices. */
    public function lines(Business $business, array $rows, ?Order $order = null, bool $lock = false): array
    {
        $existing = $order ? $order->items()->where('business_id', $business->id)->get()->keyBy('id') : collect();
        $variantIds = array_unique(array_column($rows, 'variant_id'));
        $query = Variant::with('product')->where('business_id', $business->id)->whereIn('id', $variantIds)
            ->whereNull('deleted_at')->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $variants = $query->get()->keyBy('id');
        $modifierIds = array_unique(array_merge([], ...array_map(fn (array $row): array => $row['modifier_ids'] ?? [], $rows)));
        $modifiers = Modifier::where('business_id', $business->id)->whereIn('id', $modifierIds)
            ->where('is_active', true)->where('approval_status', 'Approved')->get()->keyBy('id');
        $stations = KitchenStation::where('business_id', $business->id)->where('is_active', true)
            ->where('approval_status', 'Approved')->pluck('id')->all();
        $lines = [];
        $seen = [];

        foreach ($rows as $row) {
            $item = ! empty($row['id']) ? $existing->get((int) $row['id']) : null;
            if (! empty($row['id']) && (! $item || in_array((int) $row['id'], $seen, true))) {
                $this->fail(t('This order line is invalid. Reload the order.', 'অর্ডারের লাইনটি সঠিক নয়। অর্ডারটি আবার লোড করুন।'));
            }
            if ($item) {
                $seen[] = (int) $item->id;
            }
            $qty = round((float) ($row['quantity'] ?? 0), 3);
            $note = trim((string) ($row['kitchen_note'] ?? ''));
            $ids = array_map('intval', $row['modifier_ids'] ?? []);
            sort($ids);
            if (count($ids) !== count(array_unique($ids)) || ! is_finite($qty) || $qty <= 0 || $qty > 1000000) {
                $this->fail(t('Check the item quantity and modifiers.', 'পণ্যের পরিমাণ ও অতিরিক্ত উপকরণ যাচাই করুন।'));
            }

            $sent = $item && $item->kitchen_status !== 'draft';
            if ($sent) {
                $savedIds = array_map('intval', array_column($item->modifier_snapshot ?? [], 'id'));
                sort($savedIds);
                if ((int) $row['variant_id'] !== (int) $item->variant_id || abs($qty - (float) $item->quantity) > .00001
                    || $ids !== $savedIds || $note !== trim((string) $item->kitchen_note)) {
                    $this->fail(t('Sent items cannot be edited. Add another line or void an unprepared item with a reason.', 'রান্নাঘরে পাঠানো পণ্য পরিবর্তন করা যাবে না। নতুন লাইন যোগ করুন অথবা কারণ দিয়ে প্রস্তুত না হওয়া পণ্য বাতিল করুন।'));
                }
                if ($item->kitchen_status === 'void') {
                    continue;
                }
            }

            $variant = $variants->get((int) $row['variant_id']);
            $product = $variant?->product;
            if (! $variant || ! $product || (int) $product->business_id !== (int) $business->id
                || (! $sent && (! $variant->is_active || ! $product->is_active)) || $product->is_ingredient) {
                $this->fail(t('A selected food is unavailable.', 'নির্বাচিত একটি খাবার পাওয়া যাচ্ছে না।'));
            }
            $snapshot = [];
            if ($sent) {
                $snapshot = $item->modifier_snapshot ?? [];
                $basePrice = (float) ($item->base_price ?? $item->price);
                $price = (float) $item->price;
                $stationId = $item->kitchen_station_id;
            } else {
                foreach ($ids as $id) {
                    $modifier = $modifiers->get($id);
                    if (! $modifier || ($modifier->product_id && (int) $modifier->product_id !== (int) $product->id) || (float) $modifier->price < 0) {
                        $this->fail(t('A selected modifier is unavailable for this food.', 'নির্বাচিত অতিরিক্ত উপকরণটি এই খাবারের জন্য পাওয়া যাচ্ছে না।'));
                    }
                    $snapshot[] = ['id' => (int) $modifier->id, 'name' => $modifier->name, 'name_bn' => $modifier->name_bn, 'price' => (float) $modifier->price];
                }
                $basePrice = round((float) $variant->price, 2);
                $price = round($basePrice + array_sum(array_column($snapshot, 'price')), 2);
                $stationId = $product->kitchen_station_id;
                if ($stationId && ! in_array((int) $stationId, array_map('intval', $stations), true)) {
                    $this->fail(t('The food kitchen station is unavailable.', 'খাবারটির কিচেন স্টেশন পাওয়া যাচ্ছে না।'));
                }
            }
            $amount = round($qty * $price, 2);
            if (! is_finite($price) || $price < 0 || $amount > 999999999999) {
                $this->fail(t('The food price is invalid.', 'খাবারের মূল্য সঠিক নয়।'));
            }
            $lines[] = [
                'item' => $item, 'variant' => $variant, 'product' => $product, 'qty' => $qty,
                'base_price' => $basePrice, 'price' => $price, 'cost' => (float) $variant->cost,
                'amount' => $amount, 'modifiers' => $snapshot, 'station_id' => $stationId, 'note' => $note,
            ];
        }

        foreach ($existing as $item) {
            if (! in_array($item->kitchen_status, ['draft', 'void'], true) && ! in_array((int) $item->id, $seen, true)) {
                $this->fail(t('Sent items must remain on the order. Use the void action for unprepared items.', 'রান্নাঘরে পাঠানো পণ্য অর্ডারে থাকতে হবে। প্রস্তুত না হওয়া পণ্যের জন্য বাতিল বোতাম ব্যবহার করুন।'));
            }
        }
        if (! $lines || array_sum(array_column($lines, 'amount')) <= 0) {
            $this->fail(t('Select at least one priced food.', 'মূল্যযুক্ত অন্তত একটি খাবার নির্বাচন করুন।'));
        }

        return $lines;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['items' => $message]);
    }
}
