<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\Ingredients;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IngredientController extends Controller
{
    public function index(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'ingredients');
        $v['permissions'] = $a->permissions($v, 'configurations', 'ingredients');
        $b = $v['business'];
        $v['records'] = Product::where('business_id', $b->id)->where('is_ingredient', true)->with('variants')->when($r->filled('search'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$r->search.'%')->orWhere('sku', 'like', '%'.$r->search.'%')))->orderBy('name')->paginate(20)->withQueryString();
        $v['available'] = Product::where('business_id', $b->id)->where('is_active', true)->where('track_stock', true)->where('is_ingredient', false)->whereHas('variants')->orderBy('name')->get();

        return view('public.business.admin.configurations.ingredients.index', $v);
    }

    public function store(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'ingredients', null, 'create');
        $data = $r->validate(['product_id' => 'required|integer']);
        $b = $v['business'];
        DB::transaction(function () use ($b, $data) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $p = Product::where('business_id', $b->id)->where('is_active', true)->where('track_stock', true)->whereHas('variants')->lockForUpdate()->findOrFail($data['product_id']);
            $ids = $p->variants()->pluck('id');
            if (RecipeItem::where('business_id', $b->id)->whereIn('food_variant_id', $ids)->exists()) {
                throw ValidationException::withMessages(['product_id' => t('Product already has a recipe.', 'পণ্যটির রেসিপি রয়েছে।')]);
            }$p->update(['is_ingredient' => true]);
        });

        return back()->with('success', t('Ingredient assigned.', 'উপকরণ নির্ধারণ হয়েছে।'));
    }

    public function destroy(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'ingredients', null, 'delete');
        $b = $v['business'];
        DB::transaction(function () use ($b, $record) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $p = Product::where('business_id', $b->id)->where('is_ingredient', true)->lockForUpdate()->findOrFail($record);
            if (RecipeItem::where('business_id', $b->id)->whereIn('ingredient_variant_id', $p->variants()->pluck('id'))->exists()) {
                throw ValidationException::withMessages(['product_id' => t('Ingredient is used in a recipe.', 'উপকরণটি রেসিপিতে ব্যবহৃত হচ্ছে।')]);
            }$p->update(['is_ingredient' => false]);
        });

        return back()->with('success', t('Ingredient unassigned.', 'উপকরণ বরাদ্দ বাতিল হয়েছে।'));
    }
}
