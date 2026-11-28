<form method="GET" action="{{ $indexUrl }}" id="cfg-filter-form" class="cfg-toolbar cfg-customers-toolbar" data-listui-form>
  <div class="cfg-toolbar-actions">
    <select name="per_page" class="cfg-input cfg-small" aria-label="{{ t('Rows per page','প্রতি পৃষ্ঠায় সারি') }}" onchange="this.form.requestSubmit()">
      @foreach([10,15,25,50,100] as $size)<option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} {{ t('per page','প্রতি পৃষ্ঠায়') }}</option>@endforeach
    </select>
    <button type="button" class="cfg-btn cfg-action-icon cfg-filter-toggle" id="cfg-toggle-filters" aria-expanded="true" title="{{ t('Show or hide column filters','কলাম ফিল্টার দেখান বা লুকান') }}">{{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}</button>
    <a class="cfg-btn cfg-action-icon" href="{{ $indexUrl }}" title="{{ t('Reset all filters','সব ফিল্টার রিসেট করুন') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11a9 9 0 1 1 3.2 6.9"/><path d="M3 4v7h7"/></svg>{{ t('Reset','রিসেট') }}</a>
    <span class="cfg-toolbar-separator" aria-hidden="true"></span>
    <a class="cfg-btn cfg-action-icon cfg-export-excel" data-listui-export="excel" href="{{ $indexUrl }}?{{ http_build_query(array_merge(request()->except(['page','export']), ['export'=>'excel'])) }}" title="{{ t('Download filtered Excel','ফিল্টার অনুযায়ী এক্সেল') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 5 6m0-6-5 6"/></svg>Excel</a>
    <a class="cfg-btn cfg-action-icon cfg-export-pdf" data-listui-export="pdf" href="{{ $indexUrl }}?{{ http_build_query(array_merge(request()->except(['page','export']), ['export'=>'pdf'])) }}" title="{{ t('Download filtered PDF','ফিল্টার অনুযায়ী পিডিএফ') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>PDF</a>
  </div>
</form>