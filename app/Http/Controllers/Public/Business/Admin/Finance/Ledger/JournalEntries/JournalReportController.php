<?php
namespace App\Http\Controllers\Public\Business\Admin\Finance\Ledger\JournalEntries;
use App\Http\Controllers\Controller;
use App\Models\Purchase\Purchase;
use App\Models\Accounts\Journal;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Http\Request;
class JournalReportController extends Controller {
 public function journals(Request $r,Access $a){
  $v=$a->check($r,'finance','ledger','journal-entries');$b=$v['business'];
  $filters=$r->validate(['type'=>'nullable|in:SV,PUV,RV,PV,CV,JV,CN,DN,OV,AV,GS','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d','q'=>'nullable|string|max:100','origin'=>'nullable|in:manual,automatic','voucher'=>'nullable|integer|min:1']);
  $types=['SV'=>['Sales Voucher','বিক্রয় ভাউচার'],'PUV'=>['Purchase Voucher','ক্রয় ভাউচার'],'RV'=>['Receipt Voucher','প্রাপ্তি ভাউচার'],'PV'=>['Payment Voucher','প্রদান ভাউচার'],'CV'=>['Contra Voucher','স্থানান্তর ভাউচার'],'JV'=>['Journal Voucher','জার্নাল ভাউচার'],'CN'=>['Credit Note','ক্রেডিট নোট'],'DN'=>['Debit Note','ডেবিট নোট'],'OV'=>['Opening Voucher','প্রারম্ভিক ভাউচার'],'AV'=>['Adjustment Voucher','সমন্বয় ভাউচার'],'GS'=>['Gateway Settlement','গেটওয়ে সেটেলমেন্ট']];
  $case="CASE journals.source_type WHEN 'order' THEN 'SV' WHEN 'purchase' THEN 'PUV' WHEN 'payment' THEN CASE payments.direction WHEN 'in' THEN 'RV' WHEN 'out' THEN 'PV' ELSE 'JV' END WHEN 'expense' THEN 'PV' WHEN 'cash_transfer' THEN 'CV' WHEN 'return' THEN 'CN' WHEN 'order_cancel' THEN 'CN' WHEN 'purchase_return' THEN 'DN' WHEN 'opening' THEN 'OV' WHEN 'adjustment' THEN 'AV' WHEN 'gateway_settlement' THEN 'GS' WHEN 'manual' THEN COALESCE(manual_journals.voucher_type,'JV') ELSE 'JV' END";
  $query=Journal::query()->where('journals.business_id',$b->id)->leftJoin('payments',function($j){$j->on('payments.id','=','journals.source_id')->on('payments.business_id','=','journals.business_id')->where('journals.source_type','=','payment');})->leftJoin('manual_journals',function($j){$j->on('manual_journals.id','=','journals.source_id')->on('manual_journals.business_id','=','journals.business_id')->where('journals.source_type','=','manual');})->select('journals.*','manual_journals.reference_no as manual_reference')->selectRaw($case.' AS voucher_type')->with(['lines'=>fn($q)=>$q->where('business_id',$b->id)->with(['account'=>fn($q)=>$q->where('business_id',$b->id)])]);
  $v['types']=$types;$v['title']=t('All Vouchers','সকল ভাউচার');
  if(!empty($filters['voucher'])){$v['voucher']=(clone $query)->where('journals.id',$filters['voucher'])->firstOrFail();}
  if(!empty($filters['type']))$query->whereRaw("($case) = ?",[$filters['type']]);
  if(!empty($filters['from']))$query->whereDate('journals.posted_at','>=',$filters['from']);
  if(!empty($filters['to']))$query->whereDate('journals.posted_at','<=',$filters['to']);
  if(!empty($filters['origin']))$query->where('journals.source_type',$filters['origin']==='manual'?'=':'!=','manual');
  if(!empty($filters['q'])){$search=$filters['q'];$query->where(function($q)use($search,$case){$q->where('journals.memo','like','%'.$search.'%')->orWhere('manual_journals.reference_no','like','%'.$search.'%')->orWhereRaw("CONCAT(($case), '-', LPAD(journals.id, GREATEST(8, CHAR_LENGTH(journals.id)), '0')) LIKE ?",['%'.$search.'%']);if(ctype_digit($search))$q->orWhere('journals.source_id',$search);});}
  $v['records']=$query->orderByDesc('journals.id')->paginate(20)->withQueryString();
  return view('public.business.admin.retail.reports.journals',$v);
 }
}
