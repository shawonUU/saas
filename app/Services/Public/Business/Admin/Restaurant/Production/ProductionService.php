<?php

namespace App\Services\Public\Business\Admin\Restaurant\Production;

use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use App\Models\Public\Business\Admin\Restaurant\Production\Production;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function __construct(private Inventory $inventory) {}

    public function post(Business $business, array $data): Production
    {
        return DB::transaction(function () use ($business, $data): Production {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $existing = Production::where('business_id', $business->id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                return $existing;
            }

            $quantity = round((float) $data['quantity'], 3);
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => t('Enter a production quantity greater than zero.', 'শূন্যের বেশি উৎপাদন পরিমাণ লিখুন।')]);
            }
            $warehouse = Warehouse::where('business_id', $business->id)->where('is_active', true)->findOrFail($data['warehouse_id']);
            $recipe = RecipeItem::where('business_id', $business->id)->where('food_variant_id', $data['food_variant_id'])
                ->where('is_active', true)->where('approval_status', 'Approved')->orderBy('ingredient_variant_id')->get();
            if ($recipe->isEmpty()) {
                throw ValidationException::withMessages(['food_variant_id' => t('Add a recipe before recording production.', 'উৎপাদন রেকর্ড করার আগে একটি রেসিপি যোগ করুন।')]);
            }

            $variantIds = $recipe->pluck('ingredient_variant_id')->push((int) $data['food_variant_id'])->unique()->sort()->values();
            $variants = Variant::where('business_id', $business->id)->whereIn('id', $variantIds)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $products = Product::where('business_id', $business->id)->whereIn('id', $variants->pluck('product_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $food = $variants->get((int) $data['food_variant_id']);
            $foodProduct = $food ? $products->get($food->product_id) : null;
            if (! $food || ! $food->is_active || ! $foodProduct || ! $foodProduct->is_active
                || $foodProduct->is_ingredient || ! $foodProduct->track_stock) {
                throw ValidationException::withMessages(['food_variant_id' => t('Select an active food that tracks finished stock.', 'প্রস্তুত মজুত ট্র্যাক করে এমন সক্রিয় খাবার নির্বাচন করুন।')]);
            }

            $ingredients = [];
            $totalCost = 0.0;
            foreach ($recipe as $line) {
                $ingredient = $variants->get($line->ingredient_variant_id);
                $product = $ingredient ? $products->get($ingredient->product_id) : null;
                if (! $ingredient || ! $ingredient->is_active || ! $product || ! $product->is_active
                    || ! $product->is_ingredient || ! $product->track_stock || (int) $ingredient->id === (int) $food->id) {
                    throw ValidationException::withMessages(['food_variant_id' => t('The recipe contains an unavailable ingredient. Update the recipe first.', 'রেসিপির একটি উপকরণ ব্যবহারযোগ্য নয়। আগে রেসিপি হালনাগাদ করুন।')]);
                }
                $required = round((float) $line->quantity * $quantity, 3);
                if ($required <= 0 || (float) $ingredient->cost < 0) {
                    throw ValidationException::withMessages(['quantity' => t('The recipe quantity is too small or its cost is invalid.', 'রেসিপির পরিমাণ খুব কম অথবা খরচ সঠিক নয়।')]);
                }
                $cost = round((float) $ingredient->cost, 2);
                $totalCost += $required * $cost;
                $ingredients[] = ['ingredient_variant_id' => $ingredient->id, 'name' => $product->name,
                    'name_bn' => $product->name_bn, 'quantity' => $required, 'unit_cost' => $cost];
            }
            $totalCost = round($totalCost, 2);
            $production = Production::create([
                'business_id' => $business->id, 'food_variant_id' => $food->id, 'warehouse_id' => $warehouse->id,
                'quantity' => $quantity, 'total_cost' => $totalCost, 'ingredients' => $ingredients,
                'request_key' => $data['request_key'], 'notes' => $data['notes'] ?? null,
                'posted_at' => now(), 'approval_status' => 'Approved',
            ]);

            foreach ($ingredients as $ingredient) {
                $this->inventory->move($business, $variants[$ingredient['ingredient_variant_id']], -$ingredient['quantity'],
                    'ingredient-consumption', $ingredient['unit_cost'], 'restaurant_production', $production->id,
                    $production->code, $warehouse->id);
            }
            $oldStock = max(0, (float) $food->stock);
            $weightedCost = round(($oldStock * (float) $food->cost + $totalCost) / ($oldStock + $quantity), 2);
            $this->inventory->move($business, $food, $quantity, 'production', round($totalCost / $quantity, 2),
                'restaurant_production', $production->id, $production->code, $warehouse->id);
            $food->update(['cost' => $weightedCost]);

            return $production;
        });
    }
}
