<?php

namespace App\Http\Controllers\Public\Business\Admin\Inventory\Movements\Wastage;

use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use App\Models\Public\Business\Admin\Inventory\Movements\Wastage\Wastage;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Inventory\Movements\Wastage\WastageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class WastageController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'inventory', 'movements', 'wastage');
        $tenant = $context['business'];
        $filters = $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $context['from'] = $filters['from'] ?? null;
        $context['to'] = $filters['to'] ?? null;
        if ($context['from'] && $context['to'] && $context['to'] < $context['from']) {
            throw ValidationException::withMessages(['to' => t('The end date must be on or after the start date.', 'শেষ তারিখ শুরুর তারিখের সমান বা পরে হতে হবে।')]);
        }
        $context['permissions'] = $access->permissions($context, 'inventory', 'movements', 'wastage');
        $context['records'] = \App\Services\Public\Business\Admin\Shared\ListPage::page(Wastage::where('business_id', $tenant->id)->with(['variant.product', 'variant.size', 'warehouse'])
            ->when($context['from'], fn ($query, $from) => $query->where('posted_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($context['to'], fn ($query, $to) => $query->where('posted_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('posted_at'), $request, ['id'=>'number','variant_id'=>'number','warehouse_id'=>'number','quantity'=>'number','reason'=>'text','posted_at'=>'date','unit_cost'=>'number'], 'Wastage History');
        $context['variants'] = Variant::where('business_id', $tenant->id)->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('business_id', $tenant->id)->where('is_active', true)->where('track_stock', true))
            ->with(['product', 'size'])->orderBy('sku')->get();
        $context['warehouses'] = Warehouse::where('business_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();

        return view('public.business.admin.inventory.movements.wastage.index', $context);
    }

    public function store(Request $request, string $business, Access $access, WastageService $service): RedirectResponse
    {
        $context = $access->check($request, 'inventory', 'movements', 'wastage', 'create');
        $data = $request->validate([
            'variant_id' => 'required|integer|min:1', 'warehouse_id' => 'required|integer|min:1',
            'quantity' => 'required|numeric|decimal:0,3|min:0.001|max:999999',
            'reason' => 'required|string|max:2000', 'request_key' => 'required|uuid',
        ]);
        $service->post($context['business'], $data);

        return redirect()->route('business.admin.inventory.movements.wastage.index', ['business' => $business])
            ->with('success', t('Wastage posted and stock updated.', 'অপচয় পোস্ট হয়েছে এবং মজুত হালনাগাদ হয়েছে।'));
    }
}
