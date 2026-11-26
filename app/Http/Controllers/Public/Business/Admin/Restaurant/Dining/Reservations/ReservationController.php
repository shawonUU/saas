<?php

namespace App\Http\Controllers\Public\Business\Admin\Restaurant\Dining\Reservations;

use App\Http\Controllers\Controller;
use App\Models\Configurations\DiningTable;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Restaurant\Dining\Reservations\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'restaurant', 'dining', 'reservations');
        $tenant = $context['business'];
        $filters = $request->validate(['date' => 'nullable|date_format:Y-m-d', 'search' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(['confirmed', 'seated', 'completed', 'cancelled', 'no_show'])]]);
        $context['permissions'] = $access->permissions($context, 'restaurant', 'dining', 'reservations');
        $context['filters'] = $filters;
        $context['records'] = Reservation::where('business_id', $tenant->id)->with(['table.diningArea', 'order'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('customer_name', 'like', '%'.trim($search).'%')->orWhere('customer_phone', 'like', '%'.trim($search).'%')))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('starts_at', $date))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('starts_at')->paginate(20)->withQueryString();
        $context['tables'] = DiningTable::where('business_id', $tenant->id)->where('is_active', true)
            ->where('approval_status', 'Approved')->whereHas('diningArea', fn ($query) => $query
            ->where('business_id', $tenant->id)->where('is_active', true)->where('approval_status', 'Approved'))
            ->with('diningArea')->orderBy('name')->get();
        $context['openOrders'] = Order::where('business_id', $tenant->id)->where('channel', 'restaurant')
            ->whereIn('status', ['draft', 'confirmed'])->whereNull('restaurant_closed_at')
            ->whereNotNull('dining_table_id')->orderByDesc('id')->get();

        return view('public.business.admin.restaurant.dining.reservations.index', $context);
    }

    public function store(Request $request, string $business, Access $access, ReservationService $service): RedirectResponse
    {
        $context = $access->check($request, 'restaurant', 'dining', 'reservations', 'create');
        $service->save($context['business'], $this->validated($request));

        return redirect()->route('business.admin.restaurant.dining.reservations.index', ['business' => $business])
            ->with('success', t('Reservation saved.', 'টেবিল সংরক্ষণ করা হয়েছে।'));
    }

    public function update(Request $request, string $business, int $record, Access $access, ReservationService $service): RedirectResponse
    {
        $context = $access->check($request, 'restaurant', 'dining', 'reservations', 'update');
        $service->save($context['business'], $this->validated($request, true), $record);

        return back()->with('success', t('Reservation updated.', 'সংরক্ষণ হালনাগাদ হয়েছে।'));
    }

    public function cancel(Request $request, string $business, int $record, Access $access, ReservationService $service): RedirectResponse
    {
        $context = $access->check($request, 'restaurant', 'dining', 'reservations', 'delete');
        $service->save($context['business'], ['status' => 'cancelled'], $record);

        return back()->with('success', t('Reservation cancelled.', 'সংরক্ষণ বাতিল হয়েছে।'));
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';
        $rules = [
            'dining_table_id' => $required.'|integer|min:1',
            'customer_name' => $required.'|string|max:255',
            'customer_phone' => 'nullable|string|max:40',
            'guest_count' => $required.'|integer|min:1|max:1000',
            'starts_at' => $required.'|date',
            'ends_at' => $required.'|date',
            'notes' => 'nullable|string|max:2000',
        ];
        if ($updating) {
            $rules['status'] = ['sometimes', 'required', Rule::in(['confirmed', 'seated', 'completed', 'cancelled', 'no_show'])];
            $rules['order_id'] = 'nullable|integer|min:1';
        } else {
            $rules['request_key'] = 'required|uuid';
        }

        return $request->validate($rules);
    }
}
