<?php

namespace App\Services\Public\Business\Admin\Sales\RestaurantPos;

use App\Models\Business;
use App\Models\Configurations\RecipeItem;
use App\Models\Configurations\Variant;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Validation\ValidationException;

class RecipeConsumption
{
    /** Snapshot one food unit; tracked prepared food was already consumed by production. */
    public function snapshot(Business $business, Variant $food, ?array $frozen = null): array
    {
        if ($food->product?->track_stock) {
            return [];
        }

        $rows = $frozen === null
            ? RecipeItem::where('business_id', $business->id)->where('food_variant_id', $food->id)
                ->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at')
                ->orderBy('ingredient_variant_id')->get()->map(fn (RecipeItem $item): array => [
                    'ingredient_variant_id' => $item->ingredient_variant_id, 'quantity' => $item->quantity,
                ])->all()
            : $frozen;

        $snapshot = [];
        foreach ($rows as $row) {
            $ingredient = Variant::with('product')->where('business_id', $business->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')->lockForUpdate()->find($row['ingredient_variant_id']);
            $product = $ingredient?->product;
            if (! $ingredient || ! $product || (int) $product->business_id !== (int) $business->id
                || ! $product->is_active
                || ! $product->is_ingredient || ! $product->track_stock || (float) $row['quantity'] <= 0) {
                throw ValidationException::withMessages([
                    'items' => t('A recipe ingredient is unavailable. Review the recipe before billing.', 'রেসিপির একটি উপকরণ পাওয়া যাচ্ছে না। বিল করার আগে রেসিপি যাচাই করুন।'),
                ]);
            }
            $snapshot[] = [
                'ingredient_variant_id' => (int) $ingredient->id,
                'quantity' => round((float) $row['quantity'], 3),
                'cost' => round((float) $ingredient->cost, 2),
            ];
        }

        return $snapshot;
    }

    public function lineCost(array $snapshot, float $quantity): float
    {
        return round(array_sum(array_map(fn (array $row): float => round($row['quantity'] * $quantity, 3) * $row['cost'], $snapshot)), 2);
    }

    /** Called inside the shared sale transaction, after the order has an ID. */
    public function consume(Business $business, Order $order, array $snapshot, float $quantity): void
    {
        foreach ($snapshot as $row) {
            $amount = round($row['quantity'] * $quantity, 3);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'items' => t('Recipe quantity is below the supported stock precision.', 'রেসিপির পরিমাণ স্টকের সমর্থিত নির্ভুলতার চেয়ে কম।'),
                ]);
            }
            $ingredient = Variant::where('business_id', $business->id)->findOrFail($row['ingredient_variant_id']);
            app(Inventory::class)->move($business, $ingredient, -$amount, 'ingredient-consumption', $row['cost'], 'order', $order->id, $order->order_no);
        }
    }
}
