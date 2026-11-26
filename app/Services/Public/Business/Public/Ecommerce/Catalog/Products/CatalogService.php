<?php

namespace App\Services\Public\Business\Public\Ecommerce\Catalog\Products;

use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Retail\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogService
{
    public function query(Business $business)
    {
        return Product::query()->where('business_id', $business->id)->where('is_active', true)->where('is_ingredient', false)
            ->whereHas('variants', fn ($q) => $q->where('is_active', true))
            ->with(['variants' => fn ($q) => $q->where('is_active', true)->with('size', 'color')->orderBy('price')]);
    }

    public function categories(Business $b)
    {
        $categories = DB::table('config_categories')->where('business_id', $b->id)->where('is_active', true)->orderBy('name')->get();
        $ids = $categories->pluck('id')->all();
        $subcategories = $ids ? DB::table('config_sub_categories')->where('business_id', $b->id)->where('is_active', true)->whereIn('category_id', $ids)->orderBy('name')->get()->groupBy('category_id') : collect();

        return $categories->map(function ($category) use ($subcategories) {
            $category->children = $subcategories->get($category->id, collect());

            return $category;
        });
    }

    public function search(Business $b, Request $r)
    {
        $query = $this->query($b);
        if ($q = trim((string) $r->query('q', ''))) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', '%'.mb_substr($q, 0, 90).'%')->orWhere('sku', 'like', '%'.mb_substr($q, 0, 90).'%');
            });
        }
        if ($category = $r->integer('category')) {
            $query->where('category_id', $category);
        }
        if ($sub = $r->integer('sub_category')) {
            $query->where('sub_category_id', function ($q) use ($b, $sub, $category) {
                $q->select('id')->from('config_sub_categories')->where('business_id', $b->id)->where('id', $sub)->when($category, fn ($x) => $x->where('category_id', $category))->limit(1);
            });
        }
        switch ($r->query('sort')) {
            case 'price_low': $query->orderBy('price');
                break;
            case 'price_high':$query->orderByDesc('price');
                break;
            case 'name':$query->orderBy('name');
                break;
            default:$query->latest('id');
        }

        return $query->paginate(28)->withQueryString();
    }

    public function soldCounts(Business $b, $products): array
    {
        $ids = $products->pluck('id')->map(fn ($id) => (int) $id)->filter()->all();
        if (! $ids) {
            return [];
        }

        return DB::table('order_items as oi')->join('variants as v', 'v.id', '=', 'oi.variant_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.business_id', $b->id)->where('v.business_id', $b->id)->where('o.business_id', $b->id)
            ->whereIn('o.status', ['confirmed', 'completed'])->whereIn('v.product_id', $ids)
            ->where(fn ($q) => $q->whereNull('oi.kitchen_status')->orWhere('oi.kitchen_status', '!=', 'void'))
            ->select('v.product_id')->selectRaw('SUM(CASE WHEN oi.quantity > oi.returned_quantity THEN oi.quantity - oi.returned_quantity ELSE 0 END) AS sold')
            ->groupBy('v.product_id')->pluck('sold', 'v.product_id')->map(fn ($n) => (int) $n)->all();
    }

    public function shippings(Business $b)
    {
        return DB::table('config_shipping_methods')->where('business_id', $b->id)->where('is_active', true)->orderBy('charge')->get();
    }

    public function onlinePayment(Business $b): bool
    {
        return Integration::where('business_id', $b->id)->where('provider', 'sslcommerz')->where('is_active', true)->exists();
    }
}
