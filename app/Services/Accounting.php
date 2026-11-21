<?php
namespace App\Services;
use App\Models\Business;
use App\Models\Accounts\{Account,Journal,JournalLine};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class Accounting {
 public const DEFAULTS=[
  '1000'=>['Cash','asset'],'1100'=>['Bank','asset'],'1150'=>['Gateway Clearing','asset'],'1200'=>['Accounts Receivable','asset'],'1300'=>['Inventory','asset'],
  '2000'=>['Accounts Payable','liability'],'2100'=>['VAT Payable','liability'],'3000'=>['Opening Equity','equity'],
  '4000'=>['Sales Revenue','income'],'4100'=>['Delivery Revenue','income'],'4200'=>['Sales Returns','income'],
  '5000'=>['Cost of Goods Sold','expense'],'6000'=>['Operating Expenses','expense'],'6100'=>['Purchase Price Variance','expense'],'6200'=>['Gateway Fees','expense']];
 public function ensure(Business $business):void {
  foreach(self::DEFAULTS as $code=>[$name,$type])Account::firstOrCreate(['business_id'=>$business->id,'code'=>(string)$code],['name'=>$name,'type'=>$type,'is_system'=>true,'is_active'=>true]);
 }
 public function post(Business $business,string $source,int $sourceId,string $memo,array $entries):void {
  $this->ensure($business);$debit=0;$credit=0;$filtered=[];
  foreach($entries as $code=>$amounts){$d=round((float)$amounts[0],2);$c=round((float)$amounts[1],2);
   if($d<0 || $c<0)throw ValidationException::withMessages(['journal'=>'Negative debit/credit is not allowed.']);
   if($d==0 && $c==0)continue;if($d>0 && $c>0)throw ValidationException::withMessages(['journal'=>'One account cannot be both debit and credit on the same line.']);
   $debit+=(int)round($d*100);$credit+=(int)round($c*100);$filtered[]=[(string)$code,$d,$c];}
  if($debit!==$credit || $debit===0)throw ValidationException::withMessages(['journal'=>'Unbalanced transaction.']);
  if(Journal::where('business_id',$business->id)->where('source_type',$source)->where('source_id',$sourceId)->exists())throw ValidationException::withMessages(['journal'=>'Transaction already posted.']);
  $journal=Journal::create(['business_id'=>$business->id,'source_type'=>$source,'source_id'=>$sourceId,'memo'=>$memo,'posted_at'=>now()]);
  foreach($filtered as [$code,$d,$c]){$account=Account::where('business_id',$business->id)->where('code',$code)->where('is_active',true)->firstOrFail();
   JournalLine::create(['business_id'=>$business->id,'journal_id'=>$journal->id,'account_id'=>$account->id,'debit'=>$d,'credit'=>$c]);}
 }
 public function lines(Business $business,$query){return $query->where('retail_journal_lines.business_id',$business->id);}
}
