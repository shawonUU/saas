<?php

namespace App\Services\Public\Business\Admin\Inventory\Movements\Wastage;

use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use App\Models\Public\Business\Admin\Inventory\Movements\Wastage\Wastage;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WastageService
{
    public function __construct(private Inventory $inventory, private Accounting $accounting) {}

    public function post(Business $business, array $data): Wastage
    {
        return DB::transaction(function () use ($business, $data): Wastage {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $existing = Wastage::where('business_id', $business->id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                return $existing;
            }

            $quantity = round((float) $data['quantity'], 3);
            $reason = trim($data['reason']);
            if ($quantity <= 0 || $reason === '') {
                throw ValidationException::withMessages(['quantity' => t('Enter a positive wastage quantity and its reason.', 'অপচয়ের পরিমাণ শূন্যের বেশি লিখুন এবং কারণ উল্লেখ করুন।')]);
            }
            $warehouse = Warehouse::where('business_id', $business->id)->where('is_active', true)->findOrFail($data['warehouse_id']);
            $variant = Variant::where('business_id', $business->id)->lockForUpdate()->findOrFail($data['variant_id']);
            $product = $variant->product()->where('business_id', $business->id)->lockForUpdate()->first();
            if (! $variant->is_active || ! $product || ! $product->is_active || ! $product->track_stock || (float) $variant->cost < 0) {
                throw ValidationException::withMessages(['variant_id' => t('Select an active item with tracked stock.', 'মজুত ট্র্যাক করা সক্রিয় পণ্য নির্বাচন করুন।')]);
            }

            $unitCost = round((float) $variant->cost, 2);
            $record = Wastage::create([
                'business_id' => $business->id, 'variant_id' => $variant->id, 'warehouse_id' => $warehouse->id,
                'quantity' => $quantity, 'unit_cost' => $unitCost, 'reason' => $reason,
                'request_key' => $data['request_key'], 'posted_at' => now(), 'approval_status' => 'Approved',
            ]);
            $this->inventory->move($business, $variant, -$quantity, 'wastage', $unitCost,
                'restaurant_wastage', $record->id, mb_substr($reason, 0, 250), $warehouse->id);
            $amount = round($quantity * $unitCost, 2);
            if ($amount > 0) {
                $this->accounting->post($business, 'restaurant_wastage', $record->id,
                    t('Stock wastage', 'মজুত অপচয়').' '.$record->code, ['5120' => [$amount, 0], '1300' => [0, $amount]]);
            }

            return $record;
        });
    }
}
