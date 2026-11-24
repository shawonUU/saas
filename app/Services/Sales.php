<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Sales\Discount;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;

class Sales
{
    public function create(Business $business, array $data, string $channel = 'admin'): Order
    {
        return DB::transaction(function () use ($business, $data, $channel) {
            $key = (string) ($data['request_key'] ?? '');

            if ($key !== '') {
                $existing = Order::where('business_id', $business->id)
                    ->where('request_key', $key)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $lines = [];
            $subtotal = 0;
            $costTotal = 0;

            foreach ($data['items'] as $row) {
                $variant = Variant::where('business_id', $business->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($row['variant_id']);

                $product = $variant->product;
                $qty = round((float) $row['quantity'], 3);

                if (!$product || !$product->is_active || $qty <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid or inactive product.'
                    ]);
                }

                if ($product->track_stock && (float) $variant->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$variant->sku}."
                    ]);
                }

                $price = (float) $variant->price;
                $cost = (float) $variant->cost;
                $amount = round($qty * $price, 2);

                $subtotal += $amount;
                $costTotal += $product->track_stock ? round($qty * $cost, 2) : 0;

                $lines[] = compact(
                    'variant',
                    'product',
                    'qty',
                    'price',
                    'cost',
                    'amount'
                );
            }

            $subtotal = round($subtotal, 2);

            if (!$lines || $subtotal <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Select at least one priced item.'
                ]);
            }

            $discount = 0;
            $code = strtoupper(trim((string) ($data['coupon_code'] ?? '')));

            if ($code !== '') {
                $coupon = Discount::where('business_id', $business->id)
                    ->where('code', $code)
                    ->where('is_active', true)
                    ->first();

                if (
                    !$coupon ||
                    ($coupon->starts_at && now()->lt($coupon->starts_at)) ||
                    ($coupon->ends_at && now()->gt($coupon->ends_at))
                ) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Coupon is invalid or expired.'
                    ]);
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

                if (!empty($data['shipping_method_id'])) {
                    $method = DB::table('config_shipping_methods')
                        ->where('business_id', $business->id)
                        ->where('is_active', true)
                        ->where('id', $data['shipping_method_id'])
                        ->first();

                    if (!$method) {
                        throw ValidationException::withMessages([
                            'shipping_method_id' => 'Invalid shipping method.'
                        ]);
                    }

                    $shipping = round((float) $method->charge, 2);
                }
            }

            if ($shipping < 0) {
                throw ValidationException::withMessages([
                    'shipping' => 'Invalid delivery charge.'
                ]);
            }

            $rate = 0;
            $inclusive = false;

            if ($channel !== 'online' && !empty($data['tax_id'])) {
                $tax = DB::table('config_taxes')
                    ->where('business_id', $business->id)
                    ->where('is_active', true)
                    ->where('id', $data['tax_id'])
                    ->first();

                if (!$tax) {
                    throw ValidationException::withMessages([
                        'tax_id' => 'Invalid tax.'
                    ]);
                }

                $rate = (float) $tax->rate;
                $inclusive = $tax->calculation_type === 'inclusive';
            }

            $base = round($subtotal - $discount, 2);

            $tax = $inclusive && $rate > 0
                ? round($base - $base / (1 + $rate / 100), 2)
                : round($base * $rate / 100, 2);

            $total = round(
                $base + $shipping + ($inclusive ? 0 : $tax),
                2
            );

            $order = Order::create([
                'business_id' => $business->id,
                'order_no' => getGenerateCode(Order::where('business_id', $business->id), 'order_no', 'SO'),
                'request_key' => $key ?: null,
                'customer_id' => $data['customer_id'] ?? null,
                'channel' => $channel,
                'status' => 'confirmed',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'coupon_code' => $code ?: null,
                'shipping' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'paid' => 0,
                'credited' => 0,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null
            ]);

            foreach ($lines as $line) {
                $item = OrderItem::create([
                    'business_id' => $business->id,
                    'order_id' => $order->id,
                    'variant_id' => $line['variant']->id,
                    'name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'returned_quantity' => 0,
                    'price' => $line['price'],
                    'cost' => $line['cost'],
                    'line_total' => $line['amount']
                ]);

                if ($line['product']->track_stock) {
                    app(Inventory::class)->move(
                        $business,
                        $line['variant'],
                        -$line['qty'],
                        'sale',
                        $line['cost'],
                        'order',
                        $order->id,
                        $order->order_no
                    );
                }
            }

            $entries = [
                '1200' => [$total, 0],
                '4000' => [0, $base - ($inclusive ? $tax : 0)]
            ];

            if ($shipping > 0) {
                $entries['4100'] = [0, $shipping];
            }

            if ($tax > 0) {
                $entries['2100'] = [0, $tax];
            }

            if ($costTotal > 0) {
                $entries['5000'] = [$costTotal, 0];
                $entries['1300'] = [0, $costTotal];
            }

            app(Accounting::class)->post(
                $business,
                'order',
                $order->id,
                $order->order_no,
                $entries
            );

            if (!empty($data['pay_amount'])) {
                app(Payments::class)->payOrder(
                    $business,
                    $order,
                    (float) $data['pay_amount'],
                    (string) ($data['payment_method'] ?? 'cash'),
                    (string) ($data['payment_reference'] ?? '')
                );
            }

            return $order->fresh();
        });
    }
}
