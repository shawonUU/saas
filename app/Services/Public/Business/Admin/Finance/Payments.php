<?php

namespace App\Services\Public\Business\Admin\Finance;

use App\Models\Accounts\Payment;
use App\Models\Business;
use App\Models\Configurations\PaymentMethod;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Sales\Order;
use App\Models\Sales\SaleReturn;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Payments
{
    private function validateAmount(float $amount, string $method): void
    {
        if (! in_array($method, ['cash', 'bank'], true) || ! is_finite($amount) || round($amount, 2) <= 0) {
            throw ValidationException::withMessages(['amount' => t('Select a method and enter a positive amount.', 'পদ্ধতি নির্বাচন করে শূন্যের বেশি টাকা লিখুন।')]);
        }
    }

    private function record(Business $business, string $type, int $id, string $method, string $direction, float $amount, string $ref, ?string $key = null, ?int $configurationMethodId = null): Payment
    {
        return Payment::create([
            'business_id' => $business->id, 'reference_type' => $type, 'reference_id' => $id,
            'method' => $method, 'direction' => $direction, 'amount' => $amount, 'reference_no' => $ref ?: null,
            'request_key' => $key ?: null, 'configuration_method_id' => $configurationMethodId,
        ]);
    }

    /** Configuration changes select an existing ledger account; they never create another payment ledger. */
    public function method(Business $business, string $fallback, ?int $configurationMethodId = null): array
    {
        $method = $fallback;
        if ($configurationMethodId) {
            $configuration = PaymentMethod::where('business_id', $business->id)->where('is_active', true)
                ->where('approval_status', 'Approved')->find($configurationMethodId);
            if (! $configuration) {
                throw ValidationException::withMessages(['payment_method_id' => t('The payment method is unavailable.', 'পেমেন্ট পদ্ধতিটি পাওয়া যাচ্ছে না।')]);
            }
            $method = $configuration->method_type;
        } elseif (! in_array($method, ['cash', 'bank'], true)) {
            throw ValidationException::withMessages(['payment_method' => t('Select an available payment method.', 'একটি চালু পেমেন্ট পদ্ধতি নির্বাচন করুন।')]);
        }
        $account = match ($method) {
            'cash' => '1000', 'bank', 'other' => '1100', 'mobile' => '1110', 'card' => '1150',
            default => null,
        };
        if (! $account) {
            throw ValidationException::withMessages(['payment_method' => t('Unsupported payment method.', 'পেমেন্ট পদ্ধতিটি সমর্থিত নয়।')]);
        }

        return ['method' => $method, 'account' => $account, 'configuration_method_id' => $configurationMethodId];
    }

    public function payOrder(Business $business, Order $order, float $amount, string $method, string $ref = '', ?string $requestKey = null, ?int $configurationMethodId = null): void
    {
        DB::transaction(function () use ($business, $order, $amount, $method, $ref, $requestKey, $configurationMethodId): void {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $order = Order::where('business_id', $business->id)->lockForUpdate()->findOrFail($order->id);
            $amount = round($amount, 2);
            if (! is_finite($amount) || $amount <= 0) {
                throw ValidationException::withMessages(['amount' => t('Enter a positive payment amount.', 'শূন্যের বেশি পেমেন্টের টাকা লিখুন।')]);
            }
            if ($requestKey) {
                $existing = Payment::where('business_id', $business->id)->where('request_key', $requestKey)->first();
                if ($existing) {
                    if ($existing->reference_type !== 'order' || (int) $existing->reference_id !== (int) $order->id
                        || $existing->direction !== 'in' || abs((float) $existing->amount - $amount) > .001
                        || (! $configurationMethodId && $existing->method !== $method) || (int) $existing->configuration_method_id !== (int) $configurationMethodId
                        || (string) $existing->reference_no !== $ref) {
                        throw ValidationException::withMessages(['request_key' => t('This payment reference was already used for another payment.', 'এই পেমেন্ট রেফারেন্সটি অন্য পেমেন্টে ব্যবহার করা হয়েছে।')]);
                    }

                    return;
                }
            }
            $resolved = $this->method($business, $method, $configurationMethodId);
            if (! in_array($order->status, ['confirmed', 'returned'], true)) {
                throw ValidationException::withMessages(['order' => t('Only a posted sale can receive payment.', 'শুধু পোস্ট করা বিক্রয়ের পেমেন্ট গ্রহণ করা যাবে।')]);
            }
            if ($amount > round((float) $order->total - (float) $order->paid - (float) $order->credited, 2) + .00001) {
                throw ValidationException::withMessages(['amount' => t('Amount exceeds order due.', 'পেমেন্ট অর্ডারের বকেয়ার চেয়ে বেশি।')]);
            }
            $payment = $this->record($business, 'order', $order->id, $resolved['method'], 'in', $amount, $ref, $requestKey, $configurationMethodId);
            $order->increment('paid', $amount);
            app(Accounting::class)->post($business, 'payment', $payment->id, 'Customer payment '.$order->order_no, [$resolved['account'] => [$amount, 0], '1200' => [0, $amount]]);
        });
    }

    public function gatewayOrder(Business $business, Order $order, float $amount, string $providerReference): void
    {
        $order = Order::where('business_id', $business->id)->lockForUpdate()->findOrFail($order->id);
        $amount = round($amount, 2);
        if ($order->status !== 'confirmed' || $amount <= 0 || $amount > round((float) $order->total - (float) $order->paid - (float) $order->credited, 2) + .00001) {
            throw ValidationException::withMessages(['payment' => t('Gateway amount exceeds order due or the sale is not confirmed.', 'গেটওয়ের টাকা অর্ডারের বকেয়ার চেয়ে বেশি অথবা বিক্রয় নিশ্চিত করা হয়নি।')]);
        }
        $payment = $this->record($business, 'order', $order->id, 'bank', 'in', $amount, 'SSL:'.$providerReference);
        $order->increment('paid', $amount);
        app(Accounting::class)->post($business, 'payment', $payment->id, 'Gateway validated (awaiting settlement) '.$order->order_no, ['1150' => [$amount, 0], '1200' => [0, $amount]]);
    }

    public function payPurchase(Business $business, Purchase $purchase, float $amount, string $method, string $ref = ''): void
    {
        $this->validateAmount($amount, $method);
        $purchase = Purchase::where('business_id', $business->id)->lockForUpdate()->findOrFail($purchase->id);
        $amount = round($amount, 2);
        if ($amount > round((float) $purchase->total - (float) $purchase->paid - (float) PurchaseReturn::where('business_id', $business->id)->where('purchase_id', $purchase->id)->sum('ap_credit'), 2) + .00001) {
            throw ValidationException::withMessages(['amount' => 'Amount exceeds supplier due.']);
        }
        $payment = $this->record($business, 'purchase', $purchase->id, $method, 'out', $amount, $ref);
        $purchase->increment('paid', $amount);
        app(Accounting::class)->post($business, 'payment', $payment->id, 'Supplier payment '.$purchase->purchase_no, ['2000' => [$amount, 0], ($method === 'bank' ? '1100' : '1000') => [0, $amount]]);
    }

    public function refund(Business $business, SaleReturn $return, float $amount, string $method, string $ref = ''): void
    {
        $this->validateAmount($amount, $method);
        $return = SaleReturn::where('business_id', $business->id)->lockForUpdate()->findOrFail($return->id);
        $amount = round($amount, 2);
        $order = Order::where('business_id', $business->id)->lockForUpdate()->findOrFail($return->order_id);
        $totalRefunded = SaleReturn::where('business_id', $business->id)->where('order_id', $order->id)->sum('refunded');
        $refundable = max(0, round((float) $order->paid - (float) $totalRefunded - max(0, (float) $order->total - (float) $order->credited), 2));
        if ($amount > round((float) $return->amount - (float) $return->refunded, 2) + .00001 || $amount > $refundable + .00001) {
            throw ValidationException::withMessages(['amount' => 'Refund exceeds return credit or the paid amount after remaining customer dues.']);
        }
        $payment = $this->record($business, 'return', $return->id, $method, 'out', $amount, $ref);
        $return->increment('refunded', $amount);
        app(Accounting::class)->post($business, 'payment', $payment->id, 'Customer refund', ['1200' => [$amount, 0], ($method === 'bank' ? '1100' : '1000') => [0, $amount]]);
    }
}
