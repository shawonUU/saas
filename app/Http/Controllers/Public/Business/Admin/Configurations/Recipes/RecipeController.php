<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\Recipes;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    private function variants($b, bool $ingredient)
    {
        return Variant::where('business_id', $b->id)->where('is_active', true)->whereHas('product', fn ($q) => $q->where('business_id', $b->id)->where('is_active', true)->where('is_ingredient', $ingredient)->when($ingredient, fn ($x) => $x->where('track_stock', true)));
    }

    public function index(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'recipes');
        $v['permissions'] = $a->permissions($v, 'configurations', 'recipes');
        $b = $v['business'];
        $v['foods'] = $this->variants($b, false)->with('product')->orderBy('sku')->get();
        $v['ingredients'] = $this->variants($b, true)->with('product')->orderBy('sku')->get();
        $v['selected'] = (int) $r->query('food', 0);
        $allRecipeRows = RecipeItem::where('business_id', $b->id)->where('food_variant_id', $v['selected'])->with(['ingredient.product', 'food.product'])->orderBy('id');
        $v['cost'] = (clone $allRecipeRows)->get()->sum(fn ($row) => (float) $row->quantity * (float) ($row->ingredient?->cost ?? 0));
        $v['records'] = \App\Services\Public\Business\Admin\Shared\ListPage::page($allRecipeRows, $r, ['id'=>'number','ingredient_variant_id'=>'number','quantity'=>'number','food_variant_id'=>'number'], 'Recipe Ingredients');
        $v['units'] = DB::table('config_units')->where('business_id', $b->id)->pluck('name', 'id');

        return view('public.business.admin.configurations.recipes.index', $v);
    }

    private function save(Request $r, $b, ?int $id = null)
    {
        $data = $r->validate(['food_variant_id' => 'required|integer', 'ingredient_variant_id' => 'required|integer', 'quantity' => 'required|numeric|min:0.001|max:99999999|decimal:0,3']);
        DB::transaction(function () use ($b, $data, $id) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $food = $this->variants($b, false)->findOrFail($data['food_variant_id']);
            $ingredient = $this->variants($b, true)->findOrFail($data['ingredient_variant_id']);
            $products = Product::where('business_id', $b->id)->whereIn('id', [$food->product_id, $ingredient->product_id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($products->count() !== 2 || $products[$food->product_id]->is_ingredient || ! $products[$ingredient->product_id]->is_ingredient || ! $products[$ingredient->product_id]->track_stock) {
                throw ValidationException::withMessages(['ingredient_variant_id' => t('Invalid ingredient assignment.', 'উপকরণ বরাদ্দ সঠিক নয়।')]);
            }$query = RecipeItem::where('business_id', $b->id);
            $row = $id ? (clone $query)->findOrFail($id) : null;
            if ((clone $query)->where('food_variant_id', $food->id)->where('ingredient_variant_id', $ingredient->id)->when($id, fn ($q) => $q->where('id', '!=', $id))->exists()) {
                throw ValidationException::withMessages(['ingredient_variant_id' => t('Ingredient already exists in this recipe.', 'উপকরণটি এই রেসিপিতে রয়েছে।')]);
            }if ($row) {
                $row->update($data);
            } else {
                RecipeItem::create($data + ['business_id' => $b->id, 'approval_status' => 'Approved', 'is_active' => true]);
            }
        });

        return redirect()->route('business.admin.configurations.recipes.index', ['business' => $r->route('business'), 'food' => $data['food_variant_id']])->with('success', t('Recipe saved.', 'রেসিপি সংরক্ষিত হয়েছে।'));
    }

    public function store(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'recipes', null, 'create');

        return $this->save($r, $v['business']);
    }

    public function update(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'recipes', null, 'update');

        return $this->save($r, $v['business'], $record);
    }

    public function destroy(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'recipes', null, 'delete');
        DB::transaction(function () use ($v, $record) {
            Business::whereKey($v['business']->id)->lockForUpdate()->firstOrFail();
            RecipeItem::where('business_id', $v['business']->id)->lockForUpdate()->findOrFail($record)->delete();
        });

        return back()->with('success', t('Recipe item removed.', 'রেসিপির উপকরণ সরানো হয়েছে।'));
    }
}
