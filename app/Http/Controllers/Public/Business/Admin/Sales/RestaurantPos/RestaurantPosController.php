<?php

namespace App\Http\Controllers\Public\Business\Admin\Sales\RestaurantPos;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCustomer;
use App\Models\Configurations\Category;
use App\Models\Configurations\DiningArea;
use App\Models\Configurations\DiningTable;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\PaymentMethod;
use App\Models\Configurations\Tax;
use App\Models\Configurations\Variant;
use App\Models\Public\Business\Admin\Configurations\Modifiers\Modifier;
use App\Models\Public\Business\Admin\Restaurant\Dining\Reservations\Reservation;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantOrderPricing;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosService;
use App\Services\Public\Business\Admin\Sales\RestaurantPos\RestaurantWorkflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantPosController extends Controller
{
    public function __construct(private Access $access, private RestaurantPosService $pos) {}

    public function index(Request $request, RestaurantWorkflow $workflow): View
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos');
        $business = $context['business'];
        $context['title'] = t('Restaurant POS', 'রেস্টুরেন্ট পস');
        $context['permissions'] = $this->access->permissions($context, 'sales', 'restaurant-pos');
        $context['variants'] = $this->variants($business);
        $context['posSettings'] = $workflow->settings($business);
        $context['popularVariantIds'] = $this->popularVariantIds($business, $context['variants']);
        $context['stocks'] = $this->pos->availability($business, $context['variants']);
        $context['categories'] = Category::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get()
            ->mapWithKeys(fn (Category $category): array => [$category->id => t($category->name, $category->name_bn)]);
        $context['areas'] = DiningArea::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get();
        $context['tables'] = $this->diningTables($business);
        $context['tableState'] = $this->tableState($business, $context['tables']);
        $context['stations'] = KitchenStation::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get();
        $context['customers'] = BusinessCustomer::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get(['id', 'name', 'name_bn', 'phone', 'address']);
        $context['taxes'] = Tax::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $context['modifiers'] = Modifier::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get();
        $context['paymentMethods'] = PaymentMethod::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get();
        $openOrders = Order::where('business_id', $business->id)->where('channel', 'restaurant')->whereIn('status', ['draft', 'confirmed'])
            ->where(fn ($query) => $query->whereNull('restaurant_closed_at')->orWhereRaw('total > paid + credited'))
            ->latest('id')->limit(100)->get();
        $context['openOrders'] = $this->pos->snapshots($business, $openOrders);
        $parameters = $request->validate(['order' => 'nullable|integer|min:1', 'reservation' => 'nullable|integer|min:1', 'table' => 'nullable|integer|min:1']);
        $context['reservation'] = ! empty($parameters['reservation'])
            ? Reservation::where('business_id', $business->id)->whereIn('status', ['confirmed', 'seated'])->where('is_active', true)->whereNull('deleted_at')->findOrFail($parameters['reservation']) : null;
        $selectedId = $parameters['order'] ?? $context['reservation']?->order_id;
        $context['selectedOrder'] = $selectedId ? $this->pos->snapshot($business, $this->pos->find($business, (int) $selectedId)) : null;

        return view('public.business.admin.sales.restaurant-pos.index', $context);
    }

    public function tables(Request $request): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos');

        return response()->json($this->tableState($context['business']), 200, ['Cache-Control' => 'no-store, private']);
    }

    public function show(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos');

        return $this->result($request, $context['business'], $this->pos->find($context['business'], $order));
    }

    public function quote(Request $request, RestaurantOrderPricing $pricing): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, $request->filled('order_id') ? 'update' : 'create');
        $data = $this->draftData($request, 'quote');
        $orderId = $request->validate(['order_id' => 'nullable|integer|min:1'])['order_id'] ?? null;
        $order = $orderId ? $this->pos->find($context['business'], (int) $orderId) : null;

        return response()->json(array_merge(['success' => true], $pricing->quote($context['business'], $data, $order)));
    }

    public function store(Request $request): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'create');
        $order = $this->pos->save($context['business'], $this->draftData($request, 'store'));

        return $this->result($request, $context['business'], $order, t('Order saved.', 'অর্ডার সংরক্ষিত হয়েছে।'));
    }

    public function update(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'update');
        $saved = $this->pos->save($context['business'], $this->draftData($request, 'update'), $order);

        return $this->result($request, $context['business'], $saved, t('Order updated.', 'অর্ডার হালনাগাদ হয়েছে।'));
    }

    public function send(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'update');
        $saved = $this->pos->send($context['business'], $order, $this->version($request));

        return $this->result($request, $context['business'], $saved, t('Items sent to the kitchen.', 'খাবার রান্নাঘরে পাঠানো হয়েছে।'));
    }

    public function checkout(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'create');
        $saved = $this->pos->checkout($context['business'], $order, $this->paymentData($request));

        return $this->result($request, $context['business'], $saved, t('Bill confirmed.', 'বিল নিশ্চিত হয়েছে।'), true);
    }

    public function pay(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'create');
        $saved = $this->pos->pay($context['business'], $order, $this->paymentData($request));

        return $this->result($request, $context['business'], $saved, t('Payment received.', 'পেমেন্ট গ্রহণ করা হয়েছে।'));
    }

    public function close(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'update');
        $saved = $this->pos->close($context['business'], $order, $this->version($request));

        return $this->result($request, $context['business'], $saved, t('Table closed.', 'টেবিল বন্ধ হয়েছে।'));
    }

    public function cancel(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'delete');
        $reason = $request->validate(['reason' => 'required|string|max:500']);
        $saved = $this->pos->cancel($context['business'], $order, $this->version($request), $reason['reason']);

        return $this->result($request, $context['business'], $saved, t('Order cancelled.', 'অর্ডার বাতিল হয়েছে।'));
    }

    public function voidItem(Request $request, string $business, int $order, int $item): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'delete');
        $reason = $request->validate(['reason' => 'required|string|max:500']);
        $saved = $this->pos->voidItem($context['business'], $order, $item, $this->version($request), $reason['reason']);

        return $this->result($request, $context['business'], $saved, t('Item voided.', 'পণ্য বাতিল হয়েছে।'));
    }

    public function serve(Request $request, string $business, int $order): JsonResponse
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos', null, 'update');
        $saved = $this->pos->serve($context['business'], $order, $this->version($request));

        return $this->result($request, $context['business'], $saved, t('Ready items marked served.', 'প্রস্তুত খাবার পরিবেশিত হিসেবে চিহ্নিত হয়েছে।'));
    }

    public function receipt(Request $request, string $business, int $order): View
    {
        return $this->printView($request, $order, 'receipt');
    }

    public function kot(Request $request, string $business, int $order): View
    {
        $request->validate(['batch' => 'nullable|integer|min:1']);

        return $this->printView($request, $order, 'kot');
    }

    private function printView(Request $request, int $id, string $name): View
    {
        $context = $this->access->check($request, 'sales', 'restaurant-pos');
        $context['title'] = $name === 'receipt' ? t('Restaurant bill', 'রেস্তোরাঁর বিল') : t('Kitchen order ticket', 'কিচেন অর্ডার টিকিট');
        $context['order'] = $this->pos->find($context['business'], $id);
        $context['orderData'] = $this->pos->snapshot($context['business'], $context['order']);

        return view('public.business.admin.sales.restaurant-pos.'.$name, $context);
    }

    private function result(Request $request, Business $business, Order $order, string $message = '', bool $withStocks = false): JsonResponse
    {
        $parameters = ['business' => $request->route('business'), 'order' => $order->id];
        $data = [
            'success' => true, 'message' => $message, 'order' => $this->pos->snapshot($business, $order),
            'receipt_url' => route('business.admin.sales.restaurant-pos.receipt', $parameters),
            'kot_url' => route('business.admin.sales.restaurant-pos.kot', $parameters),
        ];
        if ($withStocks) {
            $data['stocks'] = (object) $this->pos->availability($business, $this->variants($business));
        }

        return response()->json($data);
    }

    private function variants(Business $business): Collection
    {
        return Variant::with(['product', 'size' => fn ($query) => $query->where('business_id', $business->id), 'color' => fn ($query) => $query->where('business_id', $business->id)])
            ->where('business_id', $business->id)->where('is_active', true)->whereNull('deleted_at')
            ->whereHas('product', fn ($query) => $query->where('business_id', $business->id)->where('is_active', true)->where('is_ingredient', false))
            ->orderBy('sku')->get();
    }

    private function popularVariantIds(Business $business, Collection $variants): array
    {
        if ($variants->isEmpty()) {
            return [];
        }

        return OrderItem::where('business_id', $business->id)->whereIn('variant_id', $variants->modelKeys())
            ->where(fn ($query) => $query->whereNull('kitchen_status')->orWhere('kitchen_status', '!=', 'void'))
            ->whereIn('order_id', Order::select('id')->where('business_id', $business->id)->where('channel', 'restaurant')
                ->where('status', 'confirmed')->where('posted_at', '>=', now()->subDays(30)))
            ->whereColumn('quantity', '>', 'returned_quantity')
            ->select('variant_id')->selectRaw('SUM(quantity - returned_quantity) AS sold_quantity')
            ->groupBy('variant_id')->orderByDesc('sold_quantity')->orderBy('variant_id')->limit(12)
            ->pluck('variant_id')->map(fn ($id): int => (int) $id)->all();
    }

    private function diningTables(Business $business): Collection
    {
        return DiningTable::with('diningArea')->where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')
            ->whereHas('diningArea', fn ($query) => $query->where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved'))
            ->orderBy('dining_area_id')->orderBy('name')->get();
    }

    private function tableState(Business $business, ?Collection $tables = null): array
    {
        $tables ??= $this->diningTables($business);
        $now = now();
        $occupied = Order::where('business_id', $business->id)->where('channel', 'restaurant')->whereIn('status', ['draft', 'confirmed'])
            ->whereNull('restaurant_closed_at')->whereIn('dining_table_id', $tables->modelKeys())->orderBy('id')->get();
        $orders = collect($this->pos->snapshots($business, $occupied))->keyBy('dining_table_id');
        $reservations = Reservation::where('business_id', $business->id)->whereIn('dining_table_id', $tables->modelKeys())
            ->whereIn('status', ['confirmed', 'seated'])->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at')
            ->where('starts_at', '<=', $now)->where('ends_at', '>', $now)->orderBy('starts_at')->orderBy('id')->get()->groupBy('dining_table_id');

        return [
            'success' => true,
            'tables' => $tables->map(function (DiningTable $table) use ($orders, $reservations): array {
                $order = $orders->get($table->id);
                $reservation = $reservations->get($table->id)?->first();
                $table->setAttribute('occupied_order_id', $order['id'] ?? null);
                $table->setAttribute('occupied_order_no', $order['order_no'] ?? null);

                return [
                    'id' => (int) $table->id, 'name' => $table->name, 'name_bn' => $table->name_bn,
                    'area_id' => (int) $table->dining_area_id, 'area_name' => $table->diningArea?->name, 'area_name_bn' => $table->diningArea?->name_bn,
                    'capacity' => (int) $table->capacity, 'occupied_order_id' => $order['id'] ?? null, 'occupied_order_no' => $order['order_no'] ?? null,
                    'order' => $order,
                    'reservation' => $reservation ? [
                        'id' => (int) $reservation->id, 'order_id' => $reservation->order_id ? (int) $reservation->order_id : null,
                        'guest_count' => (int) $reservation->guest_count, 'customer_name' => $reservation->customer_name,
                        'customer_phone' => $reservation->customer_phone,
                        'starts_at' => $reservation->starts_at->toIso8601String(), 'ends_at' => $reservation->ends_at->toIso8601String(),
                    ] : null,
                ];
            })->values()->all(),
            'updated_at' => $now->toIso8601String(),
        ];
    }

    private function version(Request $request): int
    {
        return (int) $request->validate(['version' => 'required|integer|min:1'])['version'];
    }

    private function draftData(Request $request, string $operation): array
    {
        return $request->validate([
            'request_key' => ($operation === 'store' ? 'required' : 'nullable').'|uuid',
            'version' => ($operation === 'update' ? 'required' : 'nullable').'|integer|min:1',
            'reservation_id' => 'nullable|integer|min:1', 'service_type' => 'required|in:dine_in,takeaway,delivery',
            'dining_table_id' => 'nullable|integer|min:1', 'guest_count' => 'required|integer|min:1|max:1000',
            'customer_id' => 'nullable|integer|min:1', 'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:40', 'customer_address' => 'nullable|string|max:2000',
            'restaurant_notes' => 'nullable|string|max:2000', 'discount' => 'nullable|numeric|min:0|max:999999999999|decimal:0,2',
            'coupon_code' => 'nullable|string|max:50', 'tax_id' => 'nullable|integer|min:1',
            'shipping' => 'nullable|numeric|min:0|max:999999999999|decimal:0,2', 'send_to_kitchen' => 'sometimes|boolean',
            'items' => 'required|array|min:1|max:100', 'items.*.id' => 'nullable|integer|min:1',
            'items.*.variant_id' => 'required|integer|min:1', 'items.*.quantity' => 'required|numeric|min:0.001|max:1000000|decimal:0,3',
            'items.*.kitchen_note' => 'nullable|string|max:500', 'items.*.modifier_ids' => 'sometimes|array|max:30',
            'items.*.modifier_ids.*' => 'required|integer|min:1',
        ]);
    }

    private function paymentData(Request $request): array
    {
        return $request->validate([
            'version' => 'required|integer|min:1', 'expected_total' => 'required|numeric|min:0|max:999999999999|decimal:0,2',
            'request_key' => 'required|uuid', 'payment_mode' => 'required|in:full,partial,due',
            'payment_method' => 'required|in:cash,bank,card,mobile,other', 'payment_method_id' => 'nullable|integer|min:1',
            'tendered' => 'required|numeric|min:0|max:999999999999|decimal:0,2', 'payment_reference' => 'nullable|string|max:255',
        ]);
    }
}
