<?php

namespace App\Services\Public\Business\Admin\Sales\Returns\NewReturn;

use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\ReturnItem;
use App\Models\Sales\SaleReturn;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Returns
{
    public function create(Business $business, array $data): SaleReturn
    {
        return DB::transaction(function () use ($business, $data) {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $order = Order::where('business_id', $business->id)->lockForUpdate()->findOrFail($data['order_id']);
            if (! in_array($order->status, ['confirmed', 'returned'], true)) {
                throw ValidationException::withMessages(['order_id' => t('Only a posted sale can be returned.', 'শুধু পোস্ট করা বিক্রয় রিটার্ন করা যাবে।')]);
            }
            $ids = array_column($data['items'], 'order_item_id');
            if (count($ids) !== count(array_unique($ids))) {
                throw ValidationException::withMessages(['items' => t('Select each return item once.', 'প্রতিটি রিটার্ন পণ্য একবার নির্বাচন করুন।')]);
            }
            $returnLines = [];
            $sales = 0;
            $cost = 0;
            $subtotal = (float) $order->subtotal;
            // Invoice net revenue excludes shipping and VAT in both inclusive and exclusive tax modes.
            $invoiceNet = round((float) $order->total - (float) $order->shipping - (float) $order->tax, 2);
            // Restore only finished goods actually withdrawn by this sale, using its original warehouse.
            // Later product settings cannot turn a cooked-food refund into raw-stock restoration.
            $stockSales = StockMovement::where('business_id', $business->id)->where('source_type', 'order')
                ->where('source_id', $order->id)->where('kind', 'sale')->where('quantity', '<', 0)
                ->orderBy('id')->get()->keyBy('variant_id');
            foreach ($data['items'] as $row) {
                $item = OrderItem::where('business_id', $business->id)->where('order_id', $order->id)->where(fn ($q) => $q->whereNull('kitchen_status')->orWhere('kitchen_status', '!=', 'void'))->lockForUpdate()->findOrFail($row['order_item_id']);
                $qty = round((float) $row['quantity'], 3);
                if ($qty === 0.0) {
                    continue;
                }if ($qty < 0 || $qty > round((float) $item->quantity - (float) $item->returned_quantity, 3)) {
                    throw ValidationException::withMessages(['items' => 'Return quantity exceeds the quantity sold.']);
                }
                // Round each line; final return carries any remaining discount rounding difference.
                $net = round($qty * (float) $item->price * ($subtotal > 0 ? $invoiceNet / $subtotal : 1), 2);
                $sales += $net;
                $stockSale = $stockSales->get($item->variant_id);
                $lineCost = $stockSale ? round($qty * (float) $item->cost, 2) : 0;
                $cost += $lineCost;
                $returnLines[] = compact('item', 'qty', 'net', 'lineCost', 'stockSale');
            }
            if (! $returnLines) {
                throw ValidationException::withMessages(['items' => 'Select return items.']);
            }
            $sales = round($sales, 2);
            $cost = round($cost, 2);
            $previous = SaleReturn::where('business_id', $business->id)->where('order_id', $order->id)->sum('amount');
            $allReturning = true;
            foreach ($order->items()->where('business_id', $business->id)->where(fn ($q) => $q->whereNull('kitchen_status')->orWhere('kitchen_status', '!=', 'void'))->get() as $item) {
                $requested = collect($returnLines)->first(fn ($x) => $x['item']->id === $item->id)['qty'] ?? 0;
                if (round((float) $item->returned_quantity + $requested, 3) < round((float) $item->quantity, 3)) {
                    $allReturning = false;
                    break;
                }
            }
            // Treat delivery charge as non-refundable; tax is allocated based on net item value.
            $tax = round((float) $order->tax * ($subtotal > 0 ? array_sum(array_map(fn ($x) => $x['qty'] * (float) $x['item']->price, $returnLines)) / $subtotal : 0), 2);
            $previousTax = DB::table('journals')->where('journals.business_id', $business->id)->where('source_type', 'return')->whereIn('source_id', SaleReturn::where('business_id', $business->id)->where('order_id', $order->id)->pluck('id'))
                ->join('journal_lines', 'journal_lines.journal_id', '=', 'journals.id')->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')->where('accounts.code', '2100')->sum('journal_lines.debit');
            if ($allReturning) {
                $tax = round((float) $order->tax - $previousTax, 2);
            }
            if ($allReturning) {
                $sales = round($invoiceNet - ($previous - $previousTax), 2);
            }
            $amount = round($sales + $tax, 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages(['items' => 'Invalid return amount.']);
            }
            $return = SaleReturn::create(['business_id' => $business->id, 'order_id' => $order->id, 'amount' => $amount, 'reason' => $data['reason'] ?? null]);
            foreach ($returnLines as $line) {
                $item = $line['item'];
                ReturnItem::create(['business_id' => $business->id, 'return_id' => $return->id, 'order_item_id' => $item->id, 'quantity' => $line['qty'], 'amount' => $line['net']]);
                $item->increment('returned_quantity', $line['qty']);
                $variant = Variant::where('business_id', $business->id)->findOrFail($item->variant_id);
                if ($line['stockSale']) {
                    app(Inventory::class)->move($business, $variant, $line['qty'], 'return', (float) $item->cost, 'return', $return->id, 'Customer return', $line['stockSale']->warehouse_id);
                }
            }
            $entries = ['4200' => [$sales, 0], '1200' => [0, $amount]];
            if ($tax > 0) {
                $entries['2100'] = [$tax, 0];
            }if ($cost > 0) {
                $entries['1300'] = [$cost, 0];
                $entries['5000'] = [0, $cost];
            }
            app(Accounting::class)->post($business, 'return', $return->id, 'Sales return '.$order->order_no, $entries);
            $order->increment('credited', $amount);
            if ($allReturning) {
                $order->update(['status' => 'returned']);
                if ($order->channel === 'restaurant') {
                    $order->update(['restaurant_closed_at' => $order->restaurant_closed_at ?? now(), 'restaurant_version' => $order->restaurant_version + 1]);
                    Reservation::where('business_id', $business->id)->where('order_id', $order->id)->where('status', 'seated')->update(['status' => 'completed']);
                }
            }

            return $return;
        });
    }
}
