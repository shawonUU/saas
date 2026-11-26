<?php

namespace App\Http\Controllers\Public\Business\Admin\Restaurant\Production;

use App\Http\Controllers\Controller;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use App\Models\Public\Business\Admin\Restaurant\Production\Production;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Restaurant\Production\ProductionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'restaurant', 'production');
        $tenant = $context['business'];
        $filters = $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $context['from'] = $filters['from'] ?? null;
        $context['to'] = $filters['to'] ?? null;
        if ($context['from'] && $context['to'] && $context['to'] < $context['from']) {
            throw ValidationException::withMessages(['to' => t('The end date must be on or after the start date.', 'শেষ তারিখ শুরুর তারিখের সমান বা পরে হতে হবে।')]);
        }
        $context['permissions'] = $access->permissions($context, 'restaurant', 'production');
        $context['records'] = Production::where('business_id', $tenant->id)->with(['food.product', 'food.size', 'warehouse'])
            ->when($context['from'], fn ($query, $from) => $query->where('posted_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($context['to'], fn ($query, $to) => $query->where('posted_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('posted_at')->paginate(20)->withQueryString();
        $context['foodVariants'] = Variant::where('business_id', $tenant->id)->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('business_id', $tenant->id)->where('is_active', true)
                ->where('is_ingredient', false)->where('track_stock', true))
            ->whereIn('id', RecipeItem::where('business_id', $tenant->id)->where('is_active', true)->where('approval_status', 'Approved')->select('food_variant_id'))
            ->with(['product', 'size'])->orderBy('sku')->get();
        $context['warehouses'] = Warehouse::where('business_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();

        return view('public.business.admin.restaurant.production.index', $context);
    }

    public function store(Request $request, string $business, Access $access, ProductionService $service): RedirectResponse
    {
        $context = $access->check($request, 'restaurant', 'production', null, 'create');
        $data = $request->validate([
            'food_variant_id' => 'required|integer|min:1', 'warehouse_id' => 'required|integer|min:1',
            'quantity' => 'required|numeric|decimal:0,3|min:0.001|max:999999',
            'request_key' => 'required|uuid', 'notes' => 'nullable|string|max:2000',
        ]);
        $service->post($context['business'], $data);

        return redirect()->route('business.admin.restaurant.production.index', ['business' => $business])
            ->with('success', t('Production posted and stock updated.', 'উৎপাদন পোস্ট হয়েছে এবং মজুত হালনাগাদ হয়েছে।'));
    }
}
