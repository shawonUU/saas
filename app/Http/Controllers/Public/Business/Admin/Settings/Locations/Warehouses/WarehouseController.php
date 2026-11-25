<?php
namespace App\Http\Controllers\Public\Business\Admin\Settings\Locations\Warehouses;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Warehouse;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class WarehouseController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'settings','locations','warehouses');$b=$v['business'];app(Inventory::class)->main($b);$v['title']='Warehouses';$v['records']=Lists::page(Warehouse::where('business_id',$b->id),$r,['id'=>'number','code'=>'text','name'=>'text','is_default'=>'number','is_active'=>'number']);return view('public.business.admin.retail.warehouses.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'settings','locations','warehouses','create');$b=$v['business'];$d=$r->validate(['code'=>['required','alpha_dash','max:30',Rule::unique('warehouses')->where('business_id',$b->id)],'name'=>'required|string|max:255']);Warehouse::create(['business_id'=>$b->id,'code'=>strtoupper($d['code']),'name'=>$d['name'],'is_default'=>false,'is_active'=>true]);return back()->with('success',t('Warehouse created.','গুদাম তৈরি হয়েছে।'));}
}
