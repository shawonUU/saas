<?php
namespace App\Http\Controllers\Public\Business\Admin\Sales\Returns\ReturnList;
use App\Http\Controllers\Controller;
use App\Models\Sales\SaleReturn;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Finance\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller {
 public function refund(Request $r,Access $a,Payments $payments){$v=$a->check($r,'sales','returns','return-list','update');$b=$v['business'];$d=$r->validate(['return_id'=>'required|integer','amount'=>'required|numeric|gt:0','method'=>'required|in:cash,bank','reference_no'=>'nullable|string|max:255']);
  DB::transaction(fn()=> $payments->refund($b,SaleReturn::where('business_id',$b->id)->findOrFail($d['return_id']),(float)$d['amount'],$d['method'],$d['reference_no']??''));return back()->with('success',t('Refund posted.','রিফান্ড পোস্ট হয়েছে।'));}
}
