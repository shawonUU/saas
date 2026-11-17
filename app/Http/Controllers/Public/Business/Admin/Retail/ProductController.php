<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Services\Retail\{Access,Lists,Inventory,Accounting};
use App\Models\Retail\{Product,Variant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ProductController extends Controller {
 private function reference($business){return ['categories'=>DB::table('config_categories')->where('business_id',$business->id)->where('is_active',true)->orderBy('name')->get(),
 'brands'=>DB::table('config_brands')->where('business_id',$business->id)->where('is_active',true)->orderBy('name')->get(),
 'sizes'=>DB::table('config_sizes')->where('business_id',$business->id)->where('is_active',true)->orderBy('name')->get(),
 'colors'=>DB::table('config_colors')->where('business_id',$business->id)->where('is_active',true)->orderBy('name')->get(),
 'units'=>DB::table('config_units')->where('business_id',$business->id)->where('is_active',true)->orderBy('name')->get()];}
 public function index(Request $r,Access $a){$v=$a->check($r,'configurations','products');$b=$v['business'];$v['records']=Lists::page(Product::where('business_id',$b->id)->withCount('variants'),$r,['id'=>'number','sku'=>'text','name'=>'text','price'=>'number','cost'=>'number','is_active'=>'number','created_at'=>'date']);$v['title']='Products';return view('public.business.admin.retail.products.index',$v);}
 public function create(Request $r,Access $a){$v=$a->check($r,'configurations','products');return $this->form($v);}
 private function form($v,?Product $record=null){$v=array_merge($v,$this->reference($v['business']));$v['record']=$record;$v['title']=$record?'Edit Product':'New Product';return view('public.business.admin.retail.products.form',$v);}
 public function edit(Request $r,int $record,Access $a){$v=$a->check($r,'configurations','products');$product=Product::where('business_id',$v['business']->id)->with('variants')->findOrFail($record);return $this->form($v,$product);}
 private function rules(Request $r,$business,?Product $product=null):array {
  $b=$business->id;
  return $r->validate(['name'=>'required|string|max:255','sku'=>['required','string','max:100',Rule::unique('retail_products','sku')->where('business_id',$b)->ignore($product?->id)],
   'description'=>'nullable|string|max:2000','image_url'=>'nullable|url:http,https|max:1000','price'=>'required|numeric|min:0','cost'=>'required|numeric|min:0',
   'category_id'=>['nullable',Rule::exists('config_categories','id')->where('business_id',$b)],
   'brand_id'=>['nullable',Rule::exists('config_brands','id')->where('business_id',$b)],
   'unit_id'=>['nullable',Rule::exists('config_units','id')->where('business_id',$b)],
   'track_stock'=>'required|boolean','is_active'=>'required|boolean',
   'variants'=>'required|array|min:1|max:80','variants.*.id'=>'nullable|integer','variants.*.sku'=>'required|string|max:100',
   'variants.*.size_id'=>['nullable',Rule::exists('config_sizes','id')->where('business_id',$b)],
   'variants.*.color_id'=>['nullable',Rule::exists('config_colors','id')->where('business_id',$b)],
   'variants.*.price'=>'required|numeric|min:0','variants.*.cost'=>'required|numeric|min:0',
   'variants.*.opening_stock'=>'nullable|numeric|min:0|max:99999999']);
 }
 public function store(Request $r,Access $a){$v=$a->check($r,'configurations','products',null,'create');$b=$v['business'];$data=$this->rules($r,$b);
  DB::transaction(function()use($b,$data){$product=Product::create(array_merge(collect($data)->except('variants')->all(),['business_id'=>$b->id]));$this->saveVariants($b,$product,$data['variants']);});
  return redirect()->route('store.admin.retail.products.index')->with('success',t('Product saved.','পণ্য সংরক্ষিত হয়েছে।'));}
 public function update(Request $r,int $record,Access $a){$v=$a->check($r,'configurations','products',null,'update');$b=$v['business'];$product=Product::where('business_id',$b->id)->findOrFail($record);$data=$this->rules($r,$b,$product);
  DB::transaction(function()use($b,$product,$data){$product->update(collect($data)->except('variants')->all());$this->saveVariants($b,$product,$data['variants']);});
  return redirect()->route('store.admin.retail.products.index')->with('success',t('Product updated.','পণ্য আপডেট হয়েছে।'));}
 private function saveVariants($b,Product $product,array $rows):void {
  foreach($rows as $row){$id=$row['id']??null;$fields=['sku'=>$row['sku'],'size_id'=>$row['size_id']??null,'color_id'=>$row['color_id']??null,'price'=>$row['price'],'cost'=>$row['cost']];
   if($id){$v=Variant::where('business_id',$b->id)->where('product_id',$product->id)->findOrFail($id);if((float)$v->stock>0 && round((float)$v->cost,2)!==round((float)$fields['cost'],2))throw \Illuminate\Validation\ValidationException::withMessages(['variants'=>'Cost of existing stock is valued through receiving; post a purchase instead of directly editing its cost.']);$v->update($fields);continue;}
   $duplicate=Variant::where('business_id',$b->id)->where('sku',$row['sku'])->exists();if($duplicate)throw \Illuminate\Validation\ValidationException::withMessages(['variants'=>'Duplicate variant SKU: '.$row['sku']]);
   $v=Variant::create(array_merge($fields,['business_id'=>$b->id,'product_id'=>$product->id,'stock'=>0]));$opening=(float)($row['opening_stock']??0);
   if($opening>0 && $product->track_stock){app(Inventory::class)->move($b,$v,$opening,'opening',(float)$row['cost'],'opening',$v->id,'Opening stock');$amount=round($opening*(float)$row['cost'],2);
    if($amount>0)app(Accounting::class)->post($b,'opening',$v->id,'Opening inventory '.$v->sku,['1300'=>[$amount,0],'3000'=>[0,$amount]]);}
  }
 }
 public function destroy(Request $r,int $record,Access $a){$v=$a->check($r,'configurations','products',null,'delete');$b=$v['business'];$product=Product::where('business_id',$b->id)->findOrFail($record);$product->update(['is_active'=>false]);return back()->with('success',t('Product deactivated.','পণ্য নিষ্ক্রিয় হয়েছে।'));}
}
