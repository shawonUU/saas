<?php
namespace App\Http\Controllers\Public\Business\Admin\Ecommerce;
use App\Http\Controllers\Controller;
use App\Services\Access;
use App\Services\Returns;
use App\Services\ReturnRequestService;
use App\Models\Ecommerce\ReturnRequest;
use Illuminate\Http\Request;
class ReturnRequestController extends Controller {
 public function index(Request $r,Access $a){ $v=$a->check($r,'ecommerce','online-orders','return-requests');$q=ReturnRequest::where('business_id',$v['business']->id);if(in_array($r->query('status'),['pending','posted','rejected'],true))$q->where('status',$r->query('status'));$v['requests']=$q->latest('id')->paginate(20)->withQueryString();$v['title']=t('Return requests','রিটার্ন অনুরোধ');return view('public.business.admin.ecommerce.online-orders.returns',$v); }
 public function review(Request $r,string $business,int $record,Access $a,ReturnRequestService $s,Returns $returns){ $v=$a->check($r,'ecommerce','online-orders','return-requests','update');$data=$r->validate(['decision'=>'required|in:posted,rejected','admin_note'=>'required_if:decision,rejected|nullable|string|max:255','received'=>'required_if:decision,posted|accepted_if:decision,posted']);if($data['decision']==='posted')$a->check($r,'sales','returns','new-return','create');$s->review($v['business'],$record,$v['user']->id,$data,$returns);return back()->with('success',t('Return request reviewed.','রিটার্ন অনুরোধ পর্যালোচিত হয়েছে।')); }
}
