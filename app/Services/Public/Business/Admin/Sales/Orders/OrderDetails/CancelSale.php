<?php

namespace App\Services\Public\Business\Admin\Sales\Orders\OrderDetails;

use App\Models\Accounts\Account;
use App\Models\Accounts\GatewayPayment;
use App\Models\Accounts\Journal;
use App\Models\Accounts\JournalLine;
use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelSale
{
    public function cancel(Business $b, Order $order): void
    {
        DB::transaction(function () use ($b, $order) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $order = Order::where('business_id', $b->id)->lockForUpdate()->findOrFail($order->id);
            if ($order->channel === 'restaurant') {
                throw ValidationException::withMessages(['order' => t('Cancel open bills from Restaurant POS. Use a sales return for a posted restaurant bill.', 'খোলা বিল Restaurant POS থেকে বাতিল করুন। পোস্ট করা রেস্টুরেন্ট বিলের জন্য সেলস রিটার্ন ব্যবহার করুন।')]);
            }
            if ($order->status !== 'confirmed' || $order->paid > 0 || $order->credited > 0 || in_array($order->delivery_status, ['shipped', 'delivered'], true)) {
                throw ValidationException::withMessages(['order' => 'Only unpaid, unshipped and unreturned orders can be cancelled.']);
            }
            if (GatewayPayment::where('business_id', $b->id)->where('order_id', $order->id)->whereIn('status', ['paid', 'initiated'])->exists()) {
                throw ValidationException::withMessages(['order' => 'Gateway payment already recorded.']);
            }
            // Reverse the posted movement snapshot, including recipe ingredients and its original warehouse.
            // Current product settings may have changed since the order was posted.
            $movements = StockMovement::where('business_id', $b->id)->where('source_type', 'order')->where('source_id', $order->id)->where('quantity', '<', 0)->orderBy('id')->get();
            foreach ($movements as $movement) {
                $variant = Variant::where('business_id', $b->id)->findOrFail($movement->variant_id);
                app(Inventory::class)->move($b, $variant, -(float) $movement->quantity, 'sale_cancel', (float) $movement->unit_cost, 'order_cancel', $order->id, 'Cancel unpaid order', $movement->warehouse_id);
            }
            $journal = Journal::where('business_id', $b->id)->where('source_type', 'order')->where('source_id', $order->id)->firstOrFail();
            $entries = [];
            foreach (JournalLine::where('business_id', $b->id)->where('journal_id', $journal->id)->get() as $line) {
                $code = Account::where('business_id', $b->id)->findOrFail($line->account_id)->code;
                $entries[$code] = [(float) $line->credit, (float) $line->debit];
            }
            app(Accounting::class)->post($b, 'order_cancel', $order->id, 'Cancel '.$order->order_no, $entries);
            $order->update(['status' => 'cancelled']);
        });
    }
}
