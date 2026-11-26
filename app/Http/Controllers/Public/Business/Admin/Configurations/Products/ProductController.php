<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations\Products;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Configurations\KitchenStation;
use App\Models\Configurations\Product;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Configurations\Products\ProductImageService;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private function reference($business)
    {
        return ['categories' => DB::table('config_categories')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'subcategories' => DB::table('config_sub_categories')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'brands' => DB::table('config_brands')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'sizes' => DB::table('config_sizes')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'colors' => DB::table('config_colors')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'units' => DB::table('config_units')->where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get(),
            'isRestaurant' => $business->category?->slug === 'restaurant',
            'kitchenStations' => KitchenStation::where('business_id', $business->id)->where('is_active', true)->where('approval_status', 'Approved')->orderBy('name')->get()];
    }

    public function index(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products');
        $b = $v['business'];
        $v['records'] = Lists::page(Product::where('business_id', $b->id)->withCount('variants'), $r, ['id' => 'number', 'sku' => 'text', 'barcode' => 'text', 'name' => 'text', 'price' => 'number', 'cost' => 'number', 'is_active' => 'number', 'created_at' => 'date']);
        $v['title'] = 'Products';

        return view('public.business.admin.configurations.products.manage.index', $v);
    }

    public function create(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products');

        return $this->form($v);
    }

    private function form($v, ?Product $record = null)
    {
        $v = array_merge($v, $this->reference($v['business']));
        $v['record'] = $record;
        $v['title'] = $record ? 'Edit Product' : 'New Product';

        return view('public.business.admin.configurations.products.manage.form', $v);
    }

    public function edit(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products');
        $product = Product::where('business_id', $v['business']->id)->with('variants')->findOrFail($record);

        return $this->form($v, $product);
    }

    private function rules(Request $r, $business, ?Product $product = null): array
    {
        $b = $business->id;
        if ($product?->is_ingredient && ! $r->boolean('track_stock')) {
            throw ValidationException::withMessages(['track_stock' => t('Ingredients must track stock.', 'উপকরণের স্টক ট্র্যাক করতে হবে।')]);
        }
        if ($product && (bool) $product->track_stock !== $r->boolean('track_stock') && DB::table('order_items as items')->join('orders', 'orders.id', '=', 'items.order_id')->where('items.business_id', $b)->where('orders.business_id', $b)->where('orders.channel', 'restaurant')->where('orders.status', 'draft')->whereIn('items.variant_id', $product->variants()->pluck('id'))->whereNotIn('items.kitchen_status', ['draft', 'void'])->exists()) {
            throw ValidationException::withMessages(['track_stock' => t('Bill the food already sent to the kitchen before changing its stock tracking.', 'স্টক ট্র্যাকিং পরিবর্তনের আগে রান্নাঘরে পাঠানো খাবারের বিল সম্পন্ন করুন।')]);
        }
        if ($product && (bool) $product->track_stock !== $r->boolean('track_stock') && $product->variants()->where('stock', '!=', 0)->exists()) {
            throw ValidationException::withMessages(['track_stock' => t('Clear the existing stock through inventory movements before changing stock tracking.', 'স্টক ট্র্যাকিং পরিবর্তনের আগে ইনভেন্টরি মুভমেন্ট দিয়ে বিদ্যমান স্টক নিষ্পত্তি করুন।')]);
        }

        return $r->validate(['name' => 'required|string|max:255', 'name_bn' => 'nullable|string|max:255', 'kitchen_station_id' => ['nullable', 'integer', Rule::exists('config_kitchen_stations', 'id')->where('business_id', $b)->where('is_active', true)->where('approval_status', 'Approved')->whereNull('deleted_at')], 'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->where('business_id', $b)->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->where('business_id', $b)->ignore($product?->id)], 'description' => 'nullable|string|max:2000', 'specification_lines' => 'nullable|string|max:12000', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096', 'price' => 'required|numeric|min:0', 'compare_at_price' => 'nullable|numeric|min:0', 'cost' => 'required|numeric|min:0',
            'category_id' => ['nullable', Rule::exists('config_categories', 'id')->where('business_id', $b)],
            'sub_category_id' => ['nullable', Rule::exists('config_sub_categories', 'id')->where('business_id', $b)->where('category_id', $r->input('category_id'))],
            'brand_id' => ['nullable', Rule::exists('config_brands', 'id')->where('business_id', $b)],
            'unit_id' => ['nullable', Rule::exists('config_units', 'id')->where('business_id', $b)],
            'track_stock' => 'required|boolean', 'is_active' => 'required|boolean',
            'variants' => 'required|array|min:1|max:80', 'variants.*.id' => 'nullable|integer', 'variants.*.sku' => 'nullable|string|max:100', 'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.size_id' => ['nullable', Rule::exists('config_sizes', 'id')->where('business_id', $b)],
            'variants.*.color_id' => ['nullable', Rule::exists('config_colors', 'id')->where('business_id', $b)],
            'variants.*.price' => 'required|numeric|min:0', 'variants.*.cost' => 'required|numeric|min:0',
            'variants.*.opening_stock' => 'nullable|numeric|min:0|max:99999999', 'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096']);
    }

    public function store(Request $r, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products', null, 'create');
        $b = $v['business'];
        $data = $this->rules($r, $b);
        DB::transaction(function () use ($r, $b, $data) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $data = $this->rules($r, $b);
            $fields = collect($data)->except(['variants', 'image', 'specification_lines'])->all();
            $fields['specifications'] = $this->specificationJson($data['specification_lines'] ?? '');
            if ($r->hasFile('image')) {
                $fields['image_url'] = app(ProductImageService::class)->upload($r->file('image'), $b);
            }$product = Product::create(array_merge($fields, ['business_id' => $b->id]));
            $this->saveVariants($b, $product, $data['variants']);
        });

        return redirect()->route('business.admin.configurations.products.index')->with('success', t('Product saved.', 'পণ্য সংরক্ষিত হয়েছে।'));
    }

    public function update(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products', null, 'update');
        $b = $v['business'];
        $product = Product::where('business_id', $b->id)->findOrFail($record);
        $data = $this->rules($r, $b, $product);
        DB::transaction(function () use ($r, $b, $product, $data) {
            Business::whereKey($b->id)->lockForUpdate()->firstOrFail();
            $product = Product::where('business_id', $b->id)->lockForUpdate()->findOrFail($product->id);
            $data = $this->rules($r, $b, $product);
            $fields = collect($data)->except(['variants', 'image', 'specification_lines'])->all();
            $fields['specifications'] = $this->specificationJson($data['specification_lines'] ?? '');
            if ($r->hasFile('image')) {
                $fields['image_url'] = app(ProductImageService::class)->upload($r->file('image'), $b);
            }$product->update($fields);
            $this->saveVariants($b, $product, $data['variants']);
        });

        return redirect()->route('business.admin.configurations.products.index')->with('success', t('Product updated.', 'পণ্য আপডেট হয়েছে।'));
    }

    private function specificationJson(string $lines): string
    {
        $items = [];
        foreach (preg_split('/\R/u', $lines) as $line) {
            if (count($items) >= 80) {
                break;
            }
            $pair = explode('|', $line, 2);
            if (count($pair) < 2) {
                continue;
            }
            $label = trim(mb_substr($pair[0], 0, 120));
            $value = trim(mb_substr($pair[1], 0, 350));
            if ($label !== '' && $value !== '') {
                $items[] = ['name' => $label, 'value' => $value];
            }
        }

        return json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function saveVariants($b, Product $product, array $rows): void
    {
        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            $fields = ['size_id' => $row['size_id'] ?? null, 'color_id' => $row['color_id'] ?? null, 'price' => $row['price'], 'cost' => $row['cost']];
            if (array_key_exists('barcode', $row)) {
                $fields['barcode'] = $row['barcode'];
                if ($row['barcode'] !== null && Variant::where('business_id', $b->id)->where('barcode', $row['barcode'])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists()) {
                    throw ValidationException::withMessages(['variants' => t('Duplicate variant barcode.', 'ভ্যারিয়েন্ট বারকোড আগে থেকেই আছে।')]);
                }
            }if (! empty($row['image'])) {
                $fields['image_path'] = app(ProductImageService::class)->upload($row['image'], $b);
            }
            if ($id) {
                $v = Variant::where('business_id', $b->id)->where('product_id', $product->id)->findOrFail($id);
                if ((float) $v->stock > 0 && round((float) $v->cost, 2) !== round((float) $fields['cost'], 2)) {
                    throw ValidationException::withMessages(['variants' => 'Cost of existing stock is valued through receiving; post a purchase instead of directly editing its cost.']);
                }$v->update($fields);

                continue;
            }
            $prefix = mb_substr($product->sku, 0, 80).'-V';
            $number = 1;
            do {
                $fields['sku'] = $prefix.str_pad((string) $number++, 3, '0', STR_PAD_LEFT);
            } while (Variant::where('business_id', $b->id)->where('sku', $fields['sku'])->exists());
            $v = Variant::create(array_merge($fields, ['business_id' => $b->id, 'product_id' => $product->id, 'stock' => 0]));
            $opening = (float) ($row['opening_stock'] ?? 0);
            if ($opening > 0 && $product->track_stock) {
                app(Inventory::class)->move($b, $v, $opening, 'opening', (float) $row['cost'], 'opening', $v->id, 'Opening stock');
                $amount = round($opening * (float) $row['cost'], 2);
                if ($amount > 0) {
                    app(Accounting::class)->post($b, 'opening', $v->id, 'Opening inventory '.$v->sku, ['1300' => [$amount, 0], '3000' => [0, $amount]]);
                }
            }
        }
    }

    public function destroy(Request $r, string $business, int $record, Access $a)
    {
        $v = $a->check($r, 'configurations', 'products', null, 'delete');
        $b = $v['business'];
        $product = Product::where('business_id', $b->id)->findOrFail($record);
        $product->update(['is_active' => false]);

        return back()->with('success', t('Product deactivated.', 'পণ্য নিষ্ক্রিয় হয়েছে।'));
    }
}
