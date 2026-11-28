<?php

namespace App\Http\Controllers\Public\Business\Admin\Inventory\Movements\IngredientConsumption;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockMovement;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class IngredientConsumptionController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'inventory', 'movements', 'ingredient-consumption');
        $tenant = $context['business'];
        $filters = $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        if ($to < $from) {
            throw ValidationException::withMessages(['to' => t('The end date must be on or after the start date.', 'শেষ তারিখ শুরুর তারিখের সমান বা পরে হতে হবে।')]);
        }

        $query = StockMovement::where('business_id', $tenant->id)->where('kind', 'ingredient-consumption')
            ->where('quantity', '<', 0)->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->where(function ($query) use ($tenant): void {
                $query->where('source_type', 'restaurant_production')->orWhere(function ($query) use ($tenant): void {
                    $query->where('source_type', 'order')->whereExists(function ($orders) use ($tenant): void {
                        $orders->selectRaw('1')->from('orders')->whereColumn('orders.id', 'stock_movements.source_id')
                            ->where('orders.business_id', $tenant->id)->whereIn('orders.status', ['confirmed', 'returned']);
                    });
                });
            });
        $totals = (clone $query)->selectRaw('COALESCE(SUM(-quantity), 0) as quantity, COALESCE(SUM(-quantity * unit_cost), 0) as cost')->first();
        $context['records'] = \App\Services\Public\Business\Admin\Shared\ListPage::page($query->with(['variant.product', 'variant.size', 'warehouse'])->orderByDesc('id'), $request, ['id'=>'number','variant_id'=>'number','warehouse_id'=>'number','quantity'=>'number','unit_cost'=>'number','created_at'=>'date','source_type'=>'text'], 'Ingredient Consumption');
        $context['totals'] = ['quantity' => round((float) $totals->quantity, 3), 'cost' => round((float) $totals->cost, 2)];
        $context['from'] = $from;
        $context['to'] = $to;
        $context['permissions'] = $access->permissions($context, 'inventory', 'movements', 'ingredient-consumption');

        return view('public.business.admin.inventory.movements.ingredient-consumption.index', $context);
    }
}
