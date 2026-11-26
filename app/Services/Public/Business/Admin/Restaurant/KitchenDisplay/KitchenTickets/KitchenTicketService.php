<?php

namespace App\Services\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets;

use App\Models\Business;
use App\Models\Configurations\KitchenStation;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KitchenTicketService
{
    public function orders(Business $business, ?int $stationId = null): array
    {
        if ($stationId) {
            KitchenStation::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->findOrFail($stationId);
        }
        $orders = Order::where('business_id', $business->id)->where('channel', 'restaurant')->whereIn('status', ['draft', 'confirmed'])
            ->whereHas('items', fn ($query) => $query->where('business_id', $business->id)->whereIn('kitchen_status', ['queued', 'preparing', 'ready'])
                ->when($stationId, fn ($q) => $q->where('kitchen_station_id', $stationId)))
            ->oldest('id')->limit(200)->get();
        $pos = app(RestaurantPosService::class);

        return array_map(function (array $data) use ($stationId): array {
            $data['items'] = array_values(array_filter($data['items'], fn (array $item): bool => in_array($item['kitchen_status'], ['queued', 'preparing', 'ready'], true)
                && (! $stationId || (int) $item['kitchen_station_id'] === $stationId)));

            return $data;
        }, $pos->snapshots($business, $orders));
    }

    public function update(Business $business, int $orderId, int $itemId, int $version, string $status): Order
    {
        return DB::transaction(function () use ($business, $orderId, $itemId, $version, $status): Order {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $pos = app(RestaurantPosService::class);
            $order = $pos->find($business, $orderId, true);
            $pos->checkVersion($order, $version);
            if (! in_array($order->status, ['draft', 'confirmed'], true)) {
                throw ValidationException::withMessages(['order' => t('This order is no longer active.', 'অর্ডারটি আর চালু নেই।')]);
            }
            $item = $order->items()->where('business_id', $business->id)->lockForUpdate()->findOrFail($itemId);
            $next = ['queued' => 'preparing', 'preparing' => 'ready', 'ready' => 'served'];
            if (($next[$item->kitchen_status] ?? null) !== $status) {
                throw ValidationException::withMessages(['status' => t('The kitchen status changed or this transition is not allowed. Reload the ticket.', 'কিচেনের অবস্থা পরিবর্তন হয়েছে অথবা এই পরিবর্তন অনুমোদিত নয়। টিকিট আবার লোড করুন।')]);
            }
            $item->update(['kitchen_status' => $status, $status.'_at' => now()]);
            $order->increment('restaurant_version');

            return $order->fresh();
        }, 3);
    }
}
