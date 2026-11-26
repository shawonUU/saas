<?php

namespace App\Http\Controllers\Public\Business\Admin\Restaurant\Reports\FoodCostReport;

use App\Http\Controllers\Controller;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FoodCostReportController extends Controller
{
    public function index(Request $request, string $business, Access $access): View
    {
        $context = $access->check($request, 'restaurant', 'reports', 'food-cost-report');
        $tenant = $context['business'];
        $filters = $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        if ($to < $from) {
            throw ValidationException::withMessages(['to' => t('The end date must be on or after the start date.', 'শেষ তারিখ শুরুর তারিখের সমান বা পরে হতে হবে।')]);
        }

        $netQuantity = '(order_items.quantity - order_items.returned_quantity)';
        $netRevenue = 'CASE WHEN orders.subtotal > 0 AND order_items.quantity > 0 '
            .'THEN (orders.total - orders.tax - orders.shipping) * 1.0 * order_items.line_total / orders.subtotal '
            .'* '.$netQuantity.' / order_items.quantity ELSE 0 END';
        $cookedItems = DB::table('order_items')->where('business_id', $tenant->id)
            ->whereJsonLength('recipe_snapshot', '>', 0)->select('id');
        $stockSales = DB::table('stock_movements')->where('business_id', $tenant->id)
            ->where('source_type', 'order')->where('kind', 'sale')->where('quantity', '<', 0)
            ->select('source_id', 'variant_id')->groupBy('source_id', 'variant_id');
        $cost = 'CASE WHEN cooked_items.id IS NOT NULL THEN order_items.cost * order_items.quantity '
            .'WHEN original_stock_sales.source_id IS NOT NULL THEN order_items.cost * '.$netQuantity.' ELSE 0 END';
        $rows = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoinSub($cookedItems, 'cooked_items', 'cooked_items.id', '=', 'order_items.id')
            ->leftJoinSub($stockSales, 'original_stock_sales', function ($join): void {
                $join->on('original_stock_sales.source_id', '=', 'order_items.order_id')
                    ->on('original_stock_sales.variant_id', '=', 'order_items.variant_id');
            })
            ->leftJoin('variants', function ($join) use ($tenant): void {
                $join->on('variants.id', '=', 'order_items.variant_id')->where('variants.business_id', $tenant->id);
            })->leftJoin('products', function ($join) use ($tenant): void {
                $join->on('products.id', '=', 'variants.product_id')->where('products.business_id', $tenant->id);
            })->where('orders.business_id', $tenant->id)->where('order_items.business_id', $tenant->id)
            ->where('orders.channel', 'restaurant')->whereIn('orders.status', ['confirmed', 'returned'])
            ->where(fn ($query) => $query->whereNull('order_items.kitchen_status')->orWhere('order_items.kitchen_status', '!=', 'void'))
            ->whereBetween(DB::raw('COALESCE(orders.posted_at, orders.created_at)'), [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->select('order_items.variant_id', 'products.name_bn')
            ->selectRaw('COALESCE(products.name, MAX(order_items.name)) as name')
            ->selectRaw('SUM('.$netQuantity.') as quantity')
            ->selectRaw('SUM('.$netRevenue.') as revenue')
            ->selectRaw('SUM('.$cost.') as cost')
            ->groupBy('order_items.variant_id', 'products.name', 'products.name_bn')
            ->havingRaw('SUM('.$netQuantity.') > 0 OR SUM('.$cost.') > 0')->orderBy('name')->get()
            ->map(function ($row): array {
                $revenue = round((float) $row->revenue, 2);
                $cost = round((float) $row->cost, 2);

                return ['variant_id' => $row->variant_id, 'name' => $row->name, 'name_bn' => $row->name_bn,
                    'quantity' => round((float) $row->quantity, 3), 'revenue' => $revenue, 'cost' => $cost,
                    'margin' => round($revenue - $cost, 2), 'cost_percent' => $revenue > 0 ? round($cost / $revenue * 100, 2) : null];
            });
        $context['rows'] = $rows->all();
        $context['totals'] = ['quantity' => round($rows->sum('quantity'), 3), 'revenue' => round($rows->sum('revenue'), 2),
            'cost' => round($rows->sum('cost'), 2), 'margin' => round($rows->sum('margin'), 2)];
        $context['from'] = $from;
        $context['to'] = $to;
        $context['permissions'] = $access->permissions($context, 'restaurant', 'reports', 'food-cost-report');

        return view('public.business.admin.restaurant.reports.food-cost-report.index', $context);
    }
}
