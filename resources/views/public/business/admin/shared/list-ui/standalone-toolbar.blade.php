@php
 $listuiExcludeParams = array_merge(['per_page','page','export','f'], $excludeParams ?? []);
 $listuiPerPage=(int)request('per_page',15);
 if(!in_array($listuiPerPage,[10,15,25,50,100],true))$listuiPerPage=15;
@endphp
<div class="listui-standalone" data-listui-root>
 <form method="GET" id="listui-standard-form" class="cfg-toolbar cfg-customers-toolbar listui-toolbar" data-listui-form data-listui-filter-target="listui-extra-filter">
  @foreach(request()->except($listuiExcludeParams) as $param=>$value)
   @if(is_scalar($value))<input type="hidden" name="{{ $param }}" value="{{ $value }}">
   @elseif(is_array($value))@foreach($value as $subkey=>$subvalue)@if(is_scalar($subvalue))<input type="hidden" name="{{ $param }}[{{ $subkey }}]" value="{{ $subvalue }}">@endif @endforeach @endif
  @endforeach
  <div class="cfg-toolbar-actions listui-controls">
   <select name="per_page" class="cfg-input cfg-small listui-perpage" aria-label="{{ t('Rows per page','প্রতি পৃষ্ঠায় সারি') }}" onchange="this.form.requestSubmit()">@foreach([10,15,25,50,100] as $size)<option value="{{ $size }}" @selected($listuiPerPage===$size)>{{ $size }} {{ t('per page','প্রতি পৃষ্ঠায়') }}</option>@endforeach</select>
   <button type="button" class="cfg-btn cfg-action-icon cfg-filter-toggle" data-listui-toggle data-show-label="{{ t('Show Column Filters','কলাম ফিল্টার দেখান') }}" data-hide-label="{{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}" aria-expanded="true" title="{{ t('Show or hide filters','ফিল্টার দেখান বা লুকান') }}"><span data-listui-toggle-label>{{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}</span></button>
   <a class="cfg-btn cfg-action-icon" href="{{ url()->current() }}" title="{{ t('Reset all filters','সব ফিল্টার রিসেট') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11a9 9 0 1 1 3.2 6.9"/><path d="M3 4v7h7"/></svg>{{ t('Reset','রিসেট') }}</a>
   <span class="cfg-toolbar-separator" aria-hidden="true"></span>
   <a class="cfg-btn cfg-action-icon cfg-export-excel" data-listui-export="excel" href="{{ request()->fullUrlWithQuery(['export'=>'excel','page'=>null]) }}" title="{{ t('Download filtered Excel','ফিল্টার করা এক্সেল ডাউনলোড') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 5 6m0-6-5 6"/></svg>Excel</a>
   <a class="cfg-btn cfg-action-icon cfg-export-pdf" data-listui-export="pdf" href="{{ request()->fullUrlWithQuery(['export'=>'pdf','page'=>null]) }}" title="{{ t('Download filtered PDF','ফিল্টার করা পিডিএফ ডাউনলোড') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>PDF</a>
  </div>
 </form>
</div>
