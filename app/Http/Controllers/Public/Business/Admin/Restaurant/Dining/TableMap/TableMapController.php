<?php

namespace App\Http\Controllers\Public\Business\Admin\Restaurant\Dining\TableMap;

use App\Http\Controllers\Controller;
use App\Models\Configurations\DiningTable;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TableMapController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'restaurant', 'dining', 'table-map');
        $tenant = $context['business'];
        $context['permissions'] = $access->permissions($context, 'restaurant', 'dining', 'table-map');
        $context['tables'] = DiningTable::where('business_id', $tenant->id)->where('is_active', true)
            ->where('approval_status', 'Approved')->whereHas('diningArea', fn ($query) => $query
            ->where('business_id', $tenant->id)->where('is_active', true)->where('approval_status', 'Approved'))->with('diningArea')
            ->orderBy('dining_area_id')->orderBy('name')->get();
        $context['openOrders'] = Order::where('business_id', $tenant->id)->where('channel', 'restaurant')
            ->whereIn('status', ['draft', 'confirmed'])->whereNull('restaurant_closed_at')
            ->with('items')->orderBy('id')->get();
        $context['reservations'] = Reservation::where('business_id', $tenant->id)->whereIn('status', ['confirmed', 'seated'])
            ->where('ends_at', '>', now())->where('starts_at', '<', now()->endOfDay())
            ->with('table')->orderBy('starts_at')->get();

        return view('public.business.admin.restaurant.dining.table-map.index', $context);
    }
}
