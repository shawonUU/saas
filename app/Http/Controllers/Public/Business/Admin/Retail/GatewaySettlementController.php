<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\GatewayPayment;
use App\Services\Retail\{Access,Lists,Accounting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class GatewaySettlementController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','banking','bank-reconciliation');$v['records']=Lists::page(GatewayPayment::where('business_id',$v['business']->id)->where('status','paid'),$r,['id'=>'number','order_id'=>'number','tran_id'=>'text','amount'=>'number','fee'=>'number','settled_at'=>'date']);return view('public.business.admin.retail.gateway_settlements.index',$v);}
 public function settle(Request $r,int $record,Access $a){$v=$a->check($r,'finance','banking','bank-reconciliation','update');$b=$v['business'];$d=$r->validate(['fee'=>'required|numeric|min:0|max:99999999','reference'=>'required|string|max:100']);DB::transaction(function()use($b,$record,$d){$gp=GatewayPayment::where('business_id',$b->id)->where('status','paid')->lockForUpdate()->findOrFail($record);if($gp->settled_at)throw ValidationException::withMessages(['settlement'=>'Already settled.']);$gross=round((float)$gp->amount,2);$fee=round((float)$d['fee'],2);if($fee>$gross)throw ValidationException::withMessages(['fee'=>'Fee cannot exceed amount.']);$net=round($gross-$fee,2);$entries=['1100'=>[$net,0],'1150'=>[0,$gross]];if($fee>0)$entries['6200']=[$fee,0];app(Accounting::class)->post($b,'gateway_settlement',$gp->id,'SSLCommerz settlement '.$d['reference'],$entries);$gp->update(['fee'=>$fee,'settled_at'=>now()]);});return back()->with('success',t('Gateway settlement reconciled.','গেটওয়ে সেটেলমেন্ট হিসাবভুক্ত।'));}
}
