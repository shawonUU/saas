@php
 $bnHeaders=['SKU'=>'এসকেইউ','Name'=>'নাম','Price'=>'মূল্য','Base Price'=>'মূল মূল্য','Cost'=>'ক্রয়মূল্য','Active'=>'সক্রিয়','Created'=>'তৈরির তারিখ','Quantity'=>'পরিমাণ','Stock'=>'স্টক','Code'=>'কোড','Date'=>'তারিখ','Source'=>'উৎস','Type'=>'ধরন','Status'=>'অবস্থা','Order'=>'অর্ডার','Customer'=>'কাস্টমার','Phone'=>'ফোন','Total'=>'মোট','Paid'=>'পরিশোধ','Discount'=>'ছাড়','Delivery'=>'ডেলিভারি','Tax'=>'কর','Subtotal'=>'সাবটোটাল','Refunded'=>'রিফান্ড','Reference'=>'রেফারেন্স','Method'=>'পদ্ধতি','Direction'=>'দিক','Amount'=>'পরিমাণ','Description'=>'বিবরণ','Unit Cost'=>'একক ক্রয়মূল্য','Tracking'=>'ট্র্যাকিং','System'=>'সিস্টেম','Channel'=>'চ্যানেল','Supplier ID'=>'সরবরাহকারী আইডি','Reason'=>'কারণ','Journal'=>'জার্নাল','Debit'=>'ডেবিট','Credit'=>'ক্রেডিট'];
 $perPage=(int)request('per_page',15);if(!in_array($perPage,[10,15,25,50,100],true))$perPage=15;
 $listuiFormId='ret-list-filters-'.md5(request()->path());
@endphp
@include('public.business.admin.shared.list-ui.styles')
<section class="cfg-panel ret-list-card" data-listui-root>
 <form method="GET" class="cfg-toolbar cfg-customers-toolbar listui-toolbar" data-listui-form id="{{ $listuiFormId }}">
  @foreach(request()->except(['per_page','page','export','f']) as $param=>$value)
   @if(is_scalar($value))<input type="hidden" name="{{ $param }}" value="{{ $value }}">
   @elseif(is_array($value))@foreach($value as $subkey=>$subvalue)@if(is_scalar($subvalue))<input type="hidden" name="{{ $param }}[{{ $subkey }}]" value="{{ $subvalue }}">@endif @endforeach @endif
  @endforeach
  <div class="cfg-toolbar-actions listui-controls">
   <select name="per_page" class="cfg-input cfg-small listui-perpage" aria-label="{{ t('Rows per page','প্রতি পৃষ্ঠায় সারি') }}" onchange="this.form.requestSubmit()">@foreach([10,15,25,50,100] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }} {{ t('per page','প্রতি পৃষ্ঠায়') }}</option>@endforeach</select>
   <button type="button" class="cfg-btn cfg-action-icon cfg-filter-toggle" data-listui-toggle data-show-label="{{ t('Show Column Filters','কলাম ফিল্টার দেখান') }}" data-hide-label="{{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}" aria-expanded="true"><span data-listui-toggle-label>{{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}</span></button>
   <a class="cfg-btn cfg-action-icon" href="{{ url()->current() }}" title="{{ t('Reset all filters','ফিল্টার রিসেট') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11a9 9 0 1 1 3.2 6.9"/><path d="M3 4v7h7"/></svg>{{ t('Reset','রিসেট') }}</a><span class="cfg-toolbar-separator" aria-hidden="true"></span>
   <a class="cfg-btn cfg-action-icon cfg-export-excel" data-listui-export="excel" href="{{ request()->fullUrlWithQuery(['export'=>'excel','page'=>null]) }}" title="{{ t('Download filtered Excel','ফিল্টার করা এক্সেল') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 5 6m0-6-5 6"/></svg>Excel</a>
   <a class="cfg-btn cfg-action-icon cfg-export-pdf" data-listui-export="pdf" href="{{ request()->fullUrlWithQuery(['export'=>'pdf','page'=>null]) }}" title="{{ t('Download filtered PDF','ফিল্টার করা পিডিএফ') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>PDF</a>
  </div>
 </form>
 @if(session('error'))<div class="ret-error" role="alert">{{ session('error') }}</div>@endif
 <div class="cfg-table-scroll listui-table-scroll"><table class="cfg-table ret-table"><thead><tr>
 @foreach($columns as $field=>$label)<th scope="col"><a href="{{ request()->fullUrlWithQuery(['sort'=>$field,'dir'=>request('sort')===$field && request('dir')==='asc'?'desc':'asc','page'=>1]) }}">{{ t($label,$bnHeaders[$label]??$label) }} @if(request('sort')===$field){{ request('dir')==='asc'?'↑':'↓' }}@endif</a></th>@endforeach
 <th>{{ t('Actions','অ্যাকশন') }}</th></tr><tr class="cfg-filter-row listui-filter-row" data-listui-filter-row>
 @foreach($columns as $field=>$label)<th><input class="cfg-input ret-input" form="{{ $listuiFormId }}" name="f[{{ $field }}]" value="{{ is_scalar(request('f.'.$field)) ? request('f.'.$field) : '' }}" aria-label="{{ t('Filter','ফিল্টার') }} {{ t($label,$bnHeaders[$label]??$label) }}" placeholder="{{ t('Filter...','ফিল্টার করুন...') }}"></th>@endforeach
 <th><button class="cfg-btn cfg-primary" form="{{ $listuiFormId }}" type="submit">{{ t('Go','যান') }}</button></th>
 </tr></thead><tbody>
 @forelse($records as $record)<tr>
 @foreach($columns as $field=>$label)<td>{{ in_array($field,['name','description','address'],true) ? t(data_get($record,$field),data_get($record,$field.'_bn')) : data_get($record,$field) }}@if($field==='sku' && $record->relationLoaded('product') && $record->product)<div class="ret-help">{{ $record->product->name }}</div>@endif @if($field==='account_id' && $record->relationLoaded('account') && $record->account)<div class="ret-help">{{ $record->account->code }} · {{ t($record->account->name,$record->account->name_bn) }}</div>@endif @if($field==='journal_id' && $record->relationLoaded('journal') && $record->journal)<div class="ret-help">{{ $record->journal->memo }}</div>@endif</td>@endforeach
 <td><div class="cfg-actions">@if(!empty($rowActions))@include($rowActions)@endif</div></td>
 </tr>@empty<tr><td colspan="{{ count($columns)+1 }}" class="cfg-empty">{{ t('No records found. Reset filters to try again.','কোনো রেকর্ড পাওয়া যায়নি। ফিল্টার রিসেট করুন।') }}</td></tr>@endforelse
 </tbody></table></div>
 @include('public.business.admin.shared.list-ui.pagination',['paginator'=>$records])
</section>
@include('public.business.admin.shared.list-ui.scripts')
