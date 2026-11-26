<?php

namespace App\Services\Public\Business\Admin\Sales\Orders\NewOrder;

use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Sales\Discount;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Finance\Payments;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RecipeConsumption;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantOrderPricing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Sales
{
    /** The optional draft is loaded by the restaurant workflow, never from a client price payload. */
    public function create(Business $business, array $data, string $channel = 'admin', ?Order $draft = null): Order
    {
        return DB::transaction(function () use ($business, $data, $channel, $draft): Order {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $key = (string) ($data['request_key'] ?? '');

            if ($draft) {
                $draft = Order::where('business_id', $business->id)->where('channel', 'restaurant')
                    ->lockForUpdate()->findOrFail($draft->id);
                if ($draft->status === 'confirmed') {
                    return $draft;
                }
                if ($draft->status !== 'draft' || $channel !== 'restaurant') {
                    $this->fail('order', t('This check cannot be posted.', 'এই বিলটি পোস্ট করা যাবে না।'));
                }
                $lines = app(RestaurantOrderPricing::class)->storedLines($business, $draft);
            } else {
                if ($channel === 'restaurant') {
                    $this->fail('order', t('Save the restaurant check before billing.', 'বিল করার আগে রেস্তোরাঁর অর্ডার সংরক্ষণ করুন।'));
                }
                if ($key !== '') {
                    $existing = Order::where('business_id', $business->id)->where('request_key', $key)->first();
                    if ($existing) {
                        if ($existing->channel !== $channel || $existing->status === 'draft') {
                            $this->fail('request_key', t('The sale reference belongs to another operation.', 'বিক্রয় রেফারেন্সটি অন্য কাজের জন্য ব্যবহার করা হয়েছে।'));
                        }

                        return $existing;
                    }
                }
                $lines = $this->saleLines($business, $data['items'], $channel);
            }

            $totals = $this->totals($business, $data, array_sum(array_column($lines, 'amount')), $channel);
            if (array_key_exists('expected_total', $data) && abs($totals['total'] - (float) $data['expected_total']) > .001) {
                $this->fail('expected_total', t('Prices or charges changed. Review and save the check again.', 'মূল্য বা চার্জ পরিবর্তন হয়েছে। বিল যাচাই করে আবার সংরক্ষণ করুন।'));
            }

            $recipes = app(RecipeConsumption::class);
            $costTotal = 0;
            foreach ($lines as &$line) {
                $frozen = isset($line['item']) ? $line['item']->recipe_snapshot : null;
                $line['recipe'] = $recipes->snapshot($business, $line['variant'], $frozen);
                if ($line['recipe']) {
                    $lineCost = $recipes->lineCost($line['recipe'], $line['qty']);
                    $line['cost'] = round($lineCost / $line['qty'], 5);
                    $costTotal += $lineCost;
                } elseif ($line['product']->track_stock) {
                    $costTotal += round($line['qty'] * $line['cost'], 2);
                }
            }
            unset($line);
            $costTotal = round($costTotal, 2);

            $values = [
                'status' => 'confirmed', 'posted_at' => now(), 'subtotal' => $totals['subtotal'], 'discount' => $totals['discount'],
                'coupon_code' => $totals['code'] ?: null, 'shipping' => $totals['shipping'],
                'tax' => $totals['tax'], 'total' => $totals['total'],
            ];
            if ($draft) {
                $draft->update($values);
                $order = $draft;
            } else {
                $order = Order::create(array_merge($values, [
                    'business_id' => $business->id,
                    'order_no' => getGenerateCode(Order::where('business_id', $business->id), 'order_no', 'SO'),
                    'request_key' => $key ?: null, 'customer_id' => $data['customer_id'] ?? null,
                    'channel' => $channel, 'paid' => 0, 'credited' => 0,
                    'customer_name' => $data['customer_name'] ?? null, 'customer_email' => $data['customer_email'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null, 'customer_address' => $data['customer_address'] ?? null,
                ]));
            }

            foreach ($lines as $line) {
                $itemValues = [
                    'cost' => $line['cost'], 'recipe_snapshot' => $line['recipe'],
                    'price' => $line['price'], 'line_total' => $line['amount'],
                ];
                if ($draft) {
                    if ($line['item']->kitchen_status === 'draft') {
                        $itemValues['base_price'] = $line['base_price'];
                        $itemValues['modifier_snapshot'] = $line['modifiers'];
                    }
                    $line['item']->update($itemValues);
                } else {
                    OrderItem::create(array_merge($itemValues, [
                        'business_id' => $business->id, 'order_id' => $order->id, 'variant_id' => $line['variant']->id,
                        'name' => $line['product']->name, 'quantity' => $line['qty'], 'returned_quantity' => 0,
                    ]));
                }

                if ($line['product']->track_stock) {
                    app(Inventory::class)->move($business, $line['variant'], -$line['qty'], 'sale', $line['cost'], 'order', $order->id, $order->order_no);
                } elseif ($line['recipe']) {
                    $recipes->consume($business, $order, $line['recipe'], $line['qty']);
                }
            }

            $entries = [
                '1200' => [$totals['total'], 0],
                '4000' => [0, round($totals['base'] - ($totals['inclusive'] ? $totals['tax'] : 0), 2)],
                '4100' => [0, $totals['shipping']], '2100' => [0, $totals['tax']],
                '5000' => [$costTotal, 0], '1300' => [0, $costTotal],
            ];
            $entries = array_filter($entries, fn (array $entry): bool => $entry[0] > 0 || $entry[1] > 0);
            if ($entries) {
                app(Accounting::class)->post($business, 'order', $order->id, $order->order_no, $entries);
            }
            if (! empty($data['pay_amount'])) {
                app(Payments::class)->payOrder($business, $order, (float) $data['pay_amount'], (string) ($data['payment_method'] ?? 'cash'), (string) ($data['payment_reference'] ?? ''));
            }

            return $order->fresh();
        });
    }

    /** Common authoritative discount, tax and delivery calculation for every sales channel. */
    public function totals(Business $business, array $data, float $subtotal, string $channel = 'admin'): array
    {
        $subtotal = round($subtotal, 2);
        if (! is_finite($subtotal) || $subtotal < 0 || $subtotal > 999999999999) {
            $this->fail('items', t('The order amount is invalid.', 'অর্ডারের মূল্য সঠিক নয়।'));
        }
        $discount = 0;
        $code = strtoupper(trim((string) ($data['coupon_code'] ?? '')));
        if ($code !== '') {
            $coupon = Discount::where('business_id', $business->id)->where('code', $code)
                ->where('is_active', true)->whereNull('deleted_at')->first();
            if (! $coupon || ($coupon->starts_at && now()->lt($coupon->starts_at)) || ($coupon->ends_at && now()->gt($coupon->ends_at))) {
                $this->fail('coupon_code', t('Coupon is invalid or expired.', 'কুপনটি ভুল অথবা মেয়াদ শেষ।'));
            }
            $discount = $coupon->type === 'percent'
                ? round($subtotal * min((float) $coupon->value, 100) / 100, 2)
                : (float) $coupon->value;
        } elseif ($channel !== 'online') {
            $discount = round((float) ($data['discount'] ?? 0), 2);
        }
        $discount = max(0, min($discount, $subtotal));
        $shipping = round((float) ($data['shipping'] ?? 0), 2);
        if ($channel === 'online') {
            $shipping = 0;
            if (! empty($data['shipping_method_id'])) {
                $method = DB::table('config_shipping_methods')->where('business_id', $business->id)
                    ->where('is_active', true)->whereNull('deleted_at')->where('id', $data['shipping_method_id'])->first();
                if (! $method) {
                    $this->fail('shipping_method_id', t('Invalid shipping method.', 'ডেলিভারি পদ্ধতি সঠিক নয়।'));
                }
                $shipping = round((float) $method->charge, 2);
            }
        }
        if (! is_finite($shipping) || $shipping < 0 || $shipping > 999999999999) {
            $this->fail('shipping', t('Invalid delivery charge.', 'ডেলিভারি চার্জ সঠিক নয়।'));
        }
        $rate = 0;
        $inclusive = false;
        if ($channel !== 'online' && ! empty($data['tax_id'])) {
            $tax = DB::table('config_taxes')->where('business_id', $business->id)->where('is_active', true)
                ->whereNull('deleted_at')->where('id', $data['tax_id'])->first();
            if (! $tax) {
                $this->fail('tax_id', t('Invalid tax.', 'কর সঠিক নয়।'));
            }
            $rate = (float) $tax->rate;
            $inclusive = $tax->calculation_type === 'inclusive';
        }
        $base = round($subtotal - $discount, 2);
        $tax = $inclusive && $rate > 0 ? round($base - $base / (1 + $rate / 100), 2) : round($base * $rate / 100, 2);
        $total = round($base + $shipping + ($inclusive ? 0 : $tax), 2);

        return compact('subtotal', 'discount', 'shipping', 'tax', 'total', 'inclusive', 'base', 'code');
    }

    private function saleLines(Business $business, array $rows, string $channel): array
    {
        $lines = [];
        foreach ($rows as $row) {
            $variant = Variant::with('product')->where('business_id', $business->id)->where('is_active', true)
                ->whereNull('deleted_at')->lockForUpdate()->findOrFail($row['variant_id']);
            $product = $variant->product;
            $qty = round((float) $row['quantity'], 3);
            if (! $product || ! $product->is_active
                || (int) $product->business_id !== (int) $business->id || ! is_finite($qty) || $qty <= 0
                || ($channel === 'online' && $product->is_ingredient)) {
                $this->fail('items', t('Invalid or inactive product.', 'পণ্যটি সঠিক নয় অথবা নিষ্ক্রিয়।'));
            }
            if ($product->track_stock && (float) $variant->stock < $qty) {
                $this->fail('items', t('Insufficient stock: ', 'পর্যাপ্ত স্টক নেই: ').$variant->sku);
            }
            $price = (float) $variant->price;
            $cost = (float) $variant->cost;
            $amount = round($qty * $price, 2);
            if ($price < 0 || ! is_finite($amount)) {
                $this->fail('items', t('Invalid product price.', 'পণ্যের মূল্য সঠিক নয়।'));
            }
            $lines[] = compact('variant', 'product', 'qty', 'price', 'cost', 'amount');
        }
        if (! $lines || array_sum(array_column($lines, 'amount')) <= 0) {
            $this->fail('items', t('Select at least one priced item.', 'মূল্যযুক্ত অন্তত একটি পণ্য নির্বাচন করুন।'));
        }

        return $lines;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
