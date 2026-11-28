<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\Modifiers;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Public\Business\Admin\Configurations\Modifiers\Modifier;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModifierController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'configurations', 'modifiers');
        $tenant = $context['business'];
        $filters = $request->validate(['search' => 'nullable|string|max:100']);
        $context['permissions'] = $access->permissions($context, 'configurations', 'modifiers');
        $context['search'] = trim($filters['search'] ?? '');
        $columnFilters = $request->query('f', []);
        $columnFilters = is_array($columnFilters) ? $columnFilters : [];
        $label = trim(mb_substr(is_scalar($columnFilters['label'] ?? null) ? (string)$columnFilters['label'] : '', 0, 100));
        $context['records'] = \App\Services\Public\Business\Admin\Shared\ListPage::page(Modifier::where('business_id', $tenant->id)->with('product')
            ->when($context['search'], fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')->orWhere('name_bn', 'like', '%'.$search.'%')))
            ->when($label !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$label.'%')
                ->orWhere('name_bn', 'like', '%'.$label.'%')))
            ->orderBy('name'), $request, ['id'=>'number','name'=>'text','product_id'=>'number','price'=>'number','is_active'=>'number'], 'Food Modifiers');
        $context['products'] = Product::where('business_id', $tenant->id)->where('is_ingredient', false)->orderBy('name')->get();

        return view('public.business.admin.configurations.modifiers.index', $context);
    }

    public function store(Request $request, string $business, Access $access): RedirectResponse
    {
        $context = $access->check($request, 'configurations', 'modifiers', null, 'create');
        $tenant = $context['business'];
        DB::transaction(function () use ($request, $tenant): void {
            Business::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            Modifier::create($this->validated($request, $tenant) + ['business_id' => $tenant->id,
                'approval_status' => 'Approved', 'approved_at' => now()]);
        });

        return redirect()->route('business.admin.configurations.modifiers.index', ['business' => $business])
            ->with('success', t('Modifier created.', 'অতিরিক্ত আইটেম তৈরি হয়েছে।'));
    }

    public function update(Request $request, string $business, int $record, Access $access): RedirectResponse
    {
        $context = $access->check($request, 'configurations', 'modifiers', null, 'update');
        $tenant = $context['business'];
        DB::transaction(function () use ($request, $tenant, $record): void {
            Business::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            $modifier = Modifier::where('business_id', $tenant->id)->lockForUpdate()->findOrFail($record);
            $modifier->update($this->validated($request, $tenant));
        });

        return back()->with('success', t('Modifier updated.', 'অতিরিক্ত আইটেম হালনাগাদ হয়েছে।'));
    }

    public function destroy(Request $request, string $business, int $record, Access $access): RedirectResponse
    {
        $context = $access->check($request, 'configurations', 'modifiers', null, 'delete');
        $tenant = $context['business'];
        DB::transaction(function () use ($tenant, $record): void {
            Business::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            Modifier::where('business_id', $tenant->id)->lockForUpdate()->findOrFail($record)->delete();
        });

        return back()->with('success', t('Modifier removed.', 'অতিরিক্ত আইটেম সরানো হয়েছে।'));
    }

    private function validated(Request $request, Business $business): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'name_bn' => 'nullable|string|max:255',
            'price' => 'required|numeric|decimal:0,2|min:0|max:999999999', 'is_active' => 'required|boolean',
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query
                ->where('business_id', $business->id)->where('is_ingredient', false)->whereNull('deleted_at'))],
        ]);
        $data['name'] = trim($data['name']);
        $data['is_active'] = (bool) $data['is_active'];
        $data['product_id'] = $data['product_id'] ?? null;

        return $data;
    }
}
