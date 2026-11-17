@php
 $bnHeaders=['SKU'=>'এসকেইউ','Name'=>'নাম','Price'=>'মূল্য','Base Price'=>'মূল মূল্য','Cost'=>'ক্রয়মূল্য','Active'=>'সক্রিয়','Created'=>'তৈরির তারিখ','Quantity'=>'পরিমাণ','Stock'=>'স্টক','Code'=>'কোড','Date'=>'তারিখ','Source'=>'উৎস','Type'=>'ধরন','Status'=>'অবস্থা','Order'=>'অর্ডার','Customer'=>'কাস্টমার','Phone'=>'ফোন','Total'=>'মোট','Paid'=>'পরিশোধ','Discount'=>'ছাড়','Delivery'=>'ডেলিভারি','Tax'=>'কর','Subtotal'=>'সাবটোটাল','Refunded'=>'রিফান্ড','Reference'=>'রেফারেন্স','Method'=>'পদ্ধতি','Direction'=>'দিক','Amount'=>'পরিমাণ','Description'=>'বিবরণ','Unit Cost'=>'একক ক্রয়মূল্য','Tracking'=>'ট্র্যাকিং','System'=>'সিস্টেম','Channel'=>'চ্যানেল','Supplier ID'=>'সরবরাহকারী আইডি','Reason'=>'কারণ','Journal'=>'জার্নাল','Debit'=>'ডেবিট','Credit'=>'ক্রেডিট'];
@endphp
<div class="ret-card">
 <form method="GET" class="ret-toolbar"><input class="ret-input" name="q" value="{{ request('q') }}" placeholder="{{ t('Search records','তথ্য খুঁজুন') }}"><button class="ret-btn" type="submit">{{ t('Search / Filter','খুঁজুন / ফিল্টার') }}</button><a class="ret-btn alt" href="{{ url()->current() }}">{{ t('Reset','রিসেট') }}</a></form>
 <form method="GET" id="ret-filter-form"><input type="hidden" name="q" value="{{ request('q') }}"></form><div class="ret-table-wrap"><table class="ret-table"><thead><tr>
 @foreach($columns as $field=>$label)<th><a href="{{ request()->fullUrlWithQuery(['sort'=>$field,'dir'=>request('dir')==='asc'?'desc':'asc']) }}">{{ t($label,$bnHeaders[$label]??$label) }} ↕</a></th>@endforeach
 <th>{{ t('Action','অ্যাকশন') }}</th></tr><tr>
 @foreach($columns as $field=>$label)<th><input class="ret-input" form="ret-filter-form" name="f[{{ $field }}]" value="{{ is_scalar(request('f.'.$field)) ? request('f.'.$field) : '' }}" aria-label="{{ t($label,$bnHeaders[$label]??$label) }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th>@endforeach
 <th><button class="ret-btn" form="ret-filter-form" type="submit">{{ t('Apply','প্রয়োগ') }}</button></th>
 </tr></thead><tbody>
 @forelse($records as $record)<tr>
 @foreach($columns as $field=>$label)<td>{{ data_get($record,$field) }}@if($field==='sku' && $record->relationLoaded('product') && $record->product)<div class="ret-help">{{ $record->product->name }}</div>@endif @if($field==='account_id' && $record->relationLoaded('account') && $record->account)<div class="ret-help">{{ $record->account->code }} · {{ $record->account->name }}</div>@endif @if($field==='journal_id' && $record->relationLoaded('journal') && $record->journal)<div class="ret-help">{{ $record->journal->memo }}</div>@endif</td>@endforeach
 <td>@if(!empty($rowActions))@include($rowActions)@endif</td>
 </tr>@empty<tr><td colspan="{{ count($columns)+1 }}">{{ t('No records found','কোনো তথ্য পাওয়া যায়নি') }}</td></tr>@endforelse
 </tbody></table></div><div style="margin-top:14px">{{ $records->links() }}</div>
 </div>
