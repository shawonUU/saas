<?php

namespace App\Http\Controllers\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets;

use App\Http\Controllers\Controller;
use App\Models\Configurations\KitchenStation;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets\KitchenTicketService;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenTicketController extends Controller
{
    public function __construct(private Access $access, private KitchenTicketService $kitchen) {}

    public function index(Request $request): View
    {
        $context = $this->access->check($request, 'restaurant', 'kitchen-display', 'kitchen-tickets');
        $context['title'] = t('Kitchen display', 'কিচেন ডিসপ্লে');
        $context['permissions'] = $this->access->permissions($context, 'restaurant', 'kitchen-display', 'kitchen-tickets');
        $context['stationId'] = $this->station($request);
        $context['stations'] = KitchenStation::where('business_id', $context['business']->id)->where('is_active', true)
            ->where('approval_status', 'Approved')->orderBy('name')->get();
        $context['orders'] = $this->kitchen->orders($context['business'], $context['stationId']);

        return view('public.business.admin.restaurant.kitchen-display.kitchen-tickets.index', $context);
    }

    public function data(Request $request): JsonResponse
    {
        $context = $this->access->check($request, 'restaurant', 'kitchen-display', 'kitchen-tickets');

        return response()->json(['success' => true, 'orders' => $this->kitchen->orders($context['business'], $this->station($request))]);
    }

    public function update(Request $request, string $business, int $order, int $item, RestaurantPosService $pos): JsonResponse
    {
        $context = $this->access->check($request, 'restaurant', 'kitchen-display', 'kitchen-tickets', 'update');
        $data = $request->validate(['version' => 'required|integer|min:1', 'status' => 'required|in:preparing,ready,served']);
        $saved = $this->kitchen->update($context['business'], $order, $item, (int) $data['version'], $data['status']);

        return response()->json(['success' => true, 'message' => t('Kitchen ticket updated.', 'কিচেন টিকিট হালনাগাদ হয়েছে।'), 'order' => $pos->snapshot($context['business'], $saved)]);
    }

    private function station(Request $request): ?int
    {
        $data = $request->validate(['station' => 'nullable|integer|min:1', 'station_id' => 'nullable|integer|min:1']);
        $id = $data['station'] ?? $data['station_id'] ?? null;

        return $id ? (int) $id : null;
    }
}
