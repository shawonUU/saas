<?php

namespace App\Services\Public\Business\Admin\Restaurant\Dining\Reservations;

use App\Models\Business;
use App\Models\Configurations\DiningTable;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function save(Business $business, array $data, ?int $reservationId = null): Reservation
    {
        return DB::transaction(function () use ($business, $data, $reservationId): Reservation {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $reservation = $reservationId
                ? Reservation::where('business_id', $business->id)->lockForUpdate()->findOrFail($reservationId)
                : Reservation::where('business_id', $business->id)->where('request_key', $data['request_key'])->first();

            if ($reservation && ! $reservationId) {
                return $reservation;
            }

            $values = array_merge($reservation?->only([
                'dining_table_id', 'customer_name', 'customer_phone', 'guest_count', 'starts_at',
                'ends_at', 'status', 'notes', 'order_id', 'request_key',
            ]) ?? ['status' => 'confirmed', 'order_id' => null], $data);
            $status = $values['status'];
            $this->assertTransition($reservation, $status);

            $table = DiningTable::withTrashed()->where('business_id', $business->id)
                ->lockForUpdate()->findOrFail($values['dining_table_id']);
            $startsAt = Carbon::parse($values['starts_at']);
            $endsAt = Carbon::parse($values['ends_at']);

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages(['ends_at' => t('End time must be after the start time.', 'শেষ সময় শুরুর সময়ের পরে হতে হবে।')]);
            }

            if (in_array($status, ['confirmed', 'seated'], true)) {
                $area = $table->diningArea;
                if ($table->trashed() || ! $table->is_active || $table->approval_status !== 'Approved'
                    || ! $area || (int) $area->business_id !== (int) $business->id
                    || ! $area->is_active || $area->approval_status !== 'Approved') {
                    throw ValidationException::withMessages(['dining_table_id' => t('Choose an active, approved table and dining area.', 'সক্রিয় ও অনুমোদিত টেবিল এবং ডাইনিং এরিয়া নির্বাচন করুন।')]);
                }
                if ((int) $values['guest_count'] < 1 || (int) $values['guest_count'] > (int) $table->capacity) {
                    throw ValidationException::withMessages(['guest_count' => t('The guest count exceeds this table’s capacity.', 'অতিথির সংখ্যা এই টেবিলের ধারণক্ষমতা ছাড়িয়েছে।')]);
                }

                $overlap = Reservation::where('business_id', $business->id)->where('dining_table_id', $table->id)
                    ->whereIn('status', ['confirmed', 'seated'])->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)->when($reservation, fn ($query) => $query->whereKeyNot($reservation->id))->exists();
                if ($overlap) {
                    throw ValidationException::withMessages(['starts_at' => t('This table is already reserved during that time.', 'এই সময়ে টেবিলটি ইতোমধ্যে সংরক্ষিত আছে।')]);
                }
            }

            $order = null;
            if ($status === 'confirmed' && ! empty($values['order_id'])) {
                throw ValidationException::withMessages(['order_id' => t('Link an order only when seating this reservation in Restaurant POS.', 'রেস্তোরাঁ POS-এ অতিথি বসানোর সময়ই সংরক্ষণের সাথে অর্ডার যুক্ত করুন।')]);
            }
            if (! empty($values['order_id'])) {
                $order = Order::where('business_id', $business->id)->where('channel', 'restaurant')
                    ->where('dining_table_id', $table->id)->lockForUpdate()->findOrFail($values['order_id']);
                if ($status === 'seated' && (! in_array($order->status, ['draft', 'confirmed'], true) || $order->restaurant_closed_at)) {
                    throw ValidationException::withMessages(['order_id' => t('Select an open restaurant order for this table.', 'এই টেবিলের একটি খোলা রেস্তোরাঁ অর্ডার নির্বাচন করুন।')]);
                }
                $linked = Reservation::where('business_id', $business->id)->where('order_id', $order->id)
                    ->whereIn('status', ['confirmed', 'seated'])
                    ->when($reservation, fn ($query) => $query->whereKeyNot($reservation->id))->exists();
                if ($linked) {
                    throw ValidationException::withMessages(['order_id' => t('This order already belongs to another reservation.', 'অর্ডারটি ইতোমধ্যে অন্য একটি সংরক্ষণের সাথে যুক্ত।')]);
                }
            }

            if ($status === 'seated' && ! $order) {
                throw ValidationException::withMessages(['order_id' => t('Open this reservation in Restaurant POS to seat the guests.', 'অতিথিদের বসাতে রেস্তোরাঁ POS-এ এই সংরক্ষণটি খুলুন।')]);
            }
            if ($reservation?->order_id && (int) $reservation->order_id !== (int) ($values['order_id'] ?? 0)) {
                throw ValidationException::withMessages(['order_id' => t('A seated reservation cannot be moved to another order.', 'বসানো অতিথির সংরক্ষণ অন্য অর্ডারে সরানো যাবে না।')]);
            }
            if (in_array($status, ['completed', 'cancelled', 'no_show'], true) && $order && ! $order->restaurant_closed_at
                && in_array($order->status, ['draft', 'confirmed'], true)) {
                throw ValidationException::withMessages(['status' => t('Close or cancel the linked order in Restaurant POS first.', 'আগে রেস্তোরাঁ POS-এ যুক্ত অর্ডারটি বন্ধ বা বাতিল করুন।')]);
            }

            $values['starts_at'] = $startsAt;
            $values['ends_at'] = $endsAt;
            $values['customer_name'] = trim($values['customer_name']);
            $values['business_id'] = $business->id;
            $values['approval_status'] = 'Approved';

            if ($reservation) {
                $reservation->update($values);

                return $reservation->refresh();
            }

            return Reservation::create($values);
        });
    }

    private function assertTransition(?Reservation $reservation, string $status): void
    {
        $allowed = $reservation ? match ($reservation->status) {
            'confirmed' => ['confirmed', 'seated', 'cancelled', 'no_show'],
            'seated' => ['seated', 'completed', 'cancelled'],
            default => [],
        } : ['confirmed'];

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => t('This reservation status can no longer be changed.', 'এই সংরক্ষণের অবস্থা আর পরিবর্তন করা যাবে না।')]);
        }
    }
}
