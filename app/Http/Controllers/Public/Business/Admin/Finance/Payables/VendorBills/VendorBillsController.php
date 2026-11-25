<?php
namespace App\Http\Controllers\Public\Business\Admin\Finance\Payables\VendorBills;
use App\Http\Controllers\Controller;
use App\Models\Purchase\Purchase;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
class VendorBillsController extends Controller {
 public function bills(Request $r,Access $a){$v=$a->check($r,'finance','payables','vendor-bills');$v['title']='Vendor Bills';$v['records']=Lists::page(Purchase::where('business_id',$v['business']->id)->whereRaw('(total - paid) > COALESCE((SELECT SUM(ap_credit) FROM purchase_returns WHERE purchase_returns.purchase_id=purchases.id AND purchase_returns.business_id=purchases.business_id),0)'),$r,['id'=>'number','purchase_no'=>'text','supplier_id'=>'number','total'=>'number','paid'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.bills',$v);}
}
