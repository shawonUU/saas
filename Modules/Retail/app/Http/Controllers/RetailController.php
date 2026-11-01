<?php
namespace Modules\Retail\App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auth\App\Models\ShopAccount;
use Modules\Business\App\Models\Business;
class RetailController extends Controller {
 private function tenant(Request $request): int {
  $user=$request->user();abort_unless($user instanceof ShopAccount,403);
  $id=(int)$user->business_id;
  abort_unless($user->currentAccessToken()?->can('admin:'.$id),403);
  abort_unless(Business::whereKey($id)->where('status','Active')->exists(),403);
  return $id;
 }
 private function writeAccess(Request $request): int {
  $id=$this->tenant($request);
  abort_unless(in_array(strtolower((string)$request->user()->role),['owner','admin'],true),403,'Retail editing requires an owner or admin account.');
  return $id;
 }
 public function catalog(Request $request) {
  $id=$this->tenant($request);
  return response()->json(['categories'=>DB::table('retail_categories')->where('business_id',$id)->orderBy('sort_order')->get(), 'products'=>DB::table('retail_products')->where('business_id',$id)->orderByDesc('id')->get()->map(function($p)use($id){$p->variants=DB::table('retail_product_variants')->where('business_id',$id)->where('product_id',$p->id)->get();return $p;})]);
 }
 public function storefront(string $slug) {
  $business=Business::where('slug',$slug)->where('status','Active')->firstOrFail();$id=$business->id;
  $categories=DB::table('retail_categories')->where('business_id',$id)->where('is_active',true)->orderBy('sort_order')->get();
  $products=DB::table('retail_products')->where('business_id',$id)->where('is_active',true)->orderByDesc('id')->get()->map(function($p)use($id){$p->variants=DB::table('retail_product_variants')->where('business_id',$id)->where('product_id',$p->id)->get();return $p;});
  return response()->json(['business'=>$business->only(['id','name','slug','phone','email']),'categories'=>$categories,'products'=>$products]);
 }
 public function category(Request $request) {
  $id=$this->writeAccess($request);$data=$request->validate(['name'=>'required|string|max:150','name_bn'=>'nullable|string|max:150','slug'=>'nullable|alpha_dash|max:150','is_active'=>'boolean']);
  $slug=Str::slug($data['slug']??$data['name']);abort_if(!$slug,422,'Invalid category slug.');
  abort_if(DB::table('retail_categories')->where('business_id',$id)->where('slug',$slug)->exists(),422,'Category already exists.');
  $new=DB::table('retail_categories')->insertGetId(['business_id'=>$id,'slug'=>$slug,'name'=>$data['name'],'name_bn'=>$data['name_bn']??null,'is_active'=>$data['is_active']??true,'created_at'=>now(),'updated_at'=>now()]);return response()->json(['id'=>$new],201);
 }
 public function product(Request $request) {
  $id=$this->writeAccess($request);$d=$request->validate([
   'name'=>'required|string|max:200','name_bn'=>'nullable|string|max:200','slug'=>'nullable|string|max:200','description'=>'nullable|string|max:10000','image_url'=>'nullable|url|max:2048','category_id'=>'nullable|integer','sku'=>'nullable|string|max:100','price'=>'required|numeric|min:0','stock'=>'required|integer|min:0','is_active'=>'boolean',
   'variants'=>'sometimes|array|max:100','variants.*.sku'=>'required_with:variants|string|max:100|distinct','variants.*.size'=>'nullable|string|max:80','variants.*.color'=>'nullable|string|max:80','variants.*.color_hex'=>'nullable|regex:/^#[0-9A-Fa-f]{6}$/','variants.*.price'=>'required_with:variants|numeric|min:0','variants.*.stock'=>'required_with:variants|integer|min:0'
  ]);
  if(!empty($d['category_id']))abort_unless(DB::table('retail_categories')->where('business_id',$id)->where('id',$d['category_id'])->exists(),422,'Invalid category.');
  $slug=Str::slug($d['slug']??$d['name']);abort_if(!$slug,422,'Invalid product slug.');
  abort_if(DB::table('retail_products')->where('business_id',$id)->where('slug',$slug)->exists(),422,'Product slug already exists.');
  foreach($d['variants']??[] as $v){abort_if(DB::table('retail_product_variants')->where('business_id',$id)->where('sku',$v['sku'])->exists(),422,'Variant SKU already exists.');}
  return DB::transaction(function()use($id,$d,$slug){$variants=$d['variants']??[];$pid=DB::table('retail_products')->insertGetId(['business_id'=>$id,'category_id'=>$d['category_id']??null,'name'=>$d['name'],'name_bn'=>$d['name_bn']??null,'slug'=>$slug,'description'=>$d['description']??null,'image_url'=>$d['image_url']??null,'sku'=>$d['sku']??null,'price'=>$d['price'],'stock'=>count($variants)?0:$d['stock'],'has_variants'=>count($variants)>0,'is_active'=>$d['is_active']??true,'created_at'=>now(),'updated_at'=>now()]);foreach($variants as $v)DB::table('retail_product_variants')->insert(['business_id'=>$id,'product_id'=>$pid,'sku'=>$v['sku'],'size'=>$v['size']??null,'color'=>$v['color']??null,'color_hex'=>$v['color_hex']??null,'price'=>$v['price'],'stock'=>$v['stock'],'created_at'=>now(),'updated_at'=>now()]);return response()->json(['id'=>$pid,'message'=>'Product created'],201);});
 }
 public function deleteProduct(Request $request,int $product) {
  $id=$this->writeAccess($request);return DB::transaction(function()use($id,$product){$removed=DB::table('retail_products')->where('business_id',$id)->where('id',$product)->delete();abort_unless($removed,404);DB::table('retail_product_variants')->where('business_id',$id)->where('product_id',$product)->delete();return response()->json(['message'=>'Product deleted']);});
 }
}
