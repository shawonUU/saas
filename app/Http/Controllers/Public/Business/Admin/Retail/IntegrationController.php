<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\Integration;
use App\Services\Access;
use Illuminate\Http\Request;

class IntegrationController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'settings','integrations','api-keys');$v['integrations']=Integration::where('business_id',$v['business']->id)->get()->keyBy('provider');return view('public.business.admin.retail.integrations.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'settings','integrations','api-keys','update');$b=$v['business'];$d=$r->validate(['provider'=>'required|in:sslcommerz,pathao','is_active'=>'required|boolean','sandbox'=>'nullable|boolean','store_id'=>'nullable|string|max:120','store_password'=>'nullable|string|max:200','client_id'=>'nullable|string|max:200','client_secret'=>'nullable|string|max:200','username'=>'nullable|string|max:200','password'=>'nullable|string|max:200','pathao_store_id'=>'nullable|integer|min:1','sender_name'=>'nullable|string|max:255','sender_phone'=>'nullable|string|max:40']);$integration=Integration::firstOrNew(['business_id'=>$b->id,'provider'=>$d['provider']]);$settings=$integration->exists?($integration->settings??[]):[];foreach(['store_id','store_password','client_id','client_secret','username','password','pathao_store_id','sender_name','sender_phone'] as $k)if(isset($d[$k]) && $d[$k]!=='')$settings[$k]=$d[$k];$settings['sandbox']=(bool)($d['sandbox']??false);if($d['is_active']){$mandatory=$d['provider']==='sslcommerz'?['store_id','store_password']:['client_id','client_secret','username','password','pathao_store_id','sender_name','sender_phone'];foreach($mandatory as $k)if(empty($settings[$k]))return back()->withErrors([$k=>'Required when enabling this integration.']);}$integration->fill(['settings'=>$settings,'is_active'=>(bool)$d['is_active']])->save();return back()->with('success',t('Integration settings saved securely.','ইন্টিগ্রেশন সেটিংস নিরাপদে সংরক্ষিত।'));}
}
