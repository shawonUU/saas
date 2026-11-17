
@extends('public.business.admin.layout')

@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.configurations._ui_styles')
@endpush

@section('content')
@php
    $businessKey = request()->route('business');
    $businessKey = is_object($businessKey) ? ($businessKey->domain ?? $businessKey->slug ?? '') : $businessKey;
    $indexUrl = route($routePrefix.'.index', ['business'=>$businessKey]);
    $storeUrl = route($routePrefix.'.store', ['business'=>$businessKey]);
    $updateTemplate = route($routePrefix.'.update', ['business'=>$businessKey,'record'=>'__ID__']);
    $recordPayload = $records->getCollection()->mapWithKeys(fn($r) => [
        $r->id => $r->only(array_merge(['id','is_active'],array_keys($fields)))
    ])->all();
    $categoryNames = $categories->pluck('name', 'id');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
    <symbol id="ws-i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></symbol>
    <symbol id="ws-i-folder" viewBox="0 0 24 24"><path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/></symbol>
    <symbol id="ws-i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="ws-i-search" viewBox="0 0 24 24"><circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/></symbol>
    <symbol id="ws-i-x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>
    <symbol id="ws-i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="ws-i-logout" viewBox="0 0 24 24"><path d="M9 4H5a2 2 0 0 1-2 2v12a2 2 0 0 0 2 2h4m5-4 4-4-4-4m4 4H9"/></symbol>
    <symbol id="ws-i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10H3z"/></symbol>
    <symbol id="ws-i-store" viewBox="0 0 24 24"><path d="M3 10h18l-2-6H5l-2 6ZM5 10v11h14V10"/></symbol>
</svg>

<div class="workspace-layout cfg-page">
    @include('public.business.admin.sidebar')

    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>

    <main class="workspace-content">
        <div class="cfg-heading">
            <button type="button" class="cfg-btn cfg-nav-toggle" data-ws-sidebar-toggle aria-expanded="false" aria-label="{{ t('Open navigation','নেভিগেশন খুলুন') }}">
                ☰ {{ t('Menu','মেনু') }}
            </button>

            <div>
                <div class="cfg-eyebrow">{{ t('Configurations','কনফিগারেশন') }} / {{ t('Currencies','মুদ্রা') }}</div>
                <h1>{{ session('portal_lang') === 'bn' ? $titleBn : $title }}</h1>
                <p>{{ t('Manage master records. Search, filters and pagination run in the database.','মাস্টার তথ্য পরিচালনা করুন। সার্চ, ফিল্টার ও পেজিনেশন ডাটাবেজের মাধ্যমে পরিচালিত হয়।') }}</p>
            </div>

            @if($permissions['create'])
                <button type="button" class="cfg-btn cfg-primary" id="cfg-add">
                    + {{ t('Add New','নতুন যোগ করুন') }}
                </button>
            @endif
        </div>

        <section class="cfg-panel">
            <form method="GET" action="{{ $indexUrl }}" id="cfg-filter-form" class="cfg-toolbar">
                <input
                    name="search"
                    value="{{ $search }}"
                    class="cfg-input cfg-search"
                    placeholder="{{ t('Search Currencies...','মুদ্রা খুঁজুন...') }}"
                    aria-label="{{ t('Search','খুঁজুন') }}"
                />

                <select name="per_page" class="cfg-input cfg-small" aria-label="{{ t('Rows per page','প্রতি পৃষ্ঠায় সারি') }}">
                    @foreach([10,15,25,50,100] as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>
                            {{ $size }} {{ t('per page','প্রতি পৃষ্ঠায়') }}
                        </option>
                    @endforeach
                </select>

                <button class="cfg-btn cfg-primary" type="submit">
                    {{ t('Apply Filters','ফিল্টার প্রয়োগ করুন') }}
                </button>

                <button type="button" class="cfg-btn" id="cfg-toggle-filters" aria-expanded="true">
                    {{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}
                </button>

                <a class="cfg-btn" href="{{ $indexUrl }}">
                    {{ t('Reset','রিসেট') }}
                </a>
            </form>

            <div class="cfg-table-scroll">
                <table class="cfg-table">
                    <thead>
                        <tr>
                            @foreach($columns as $column=>$meta)
                                <th scope="col">
                                    <a href="{{ request()->fullUrlWithQuery(['sort'=>$column,'dir'=>$sort===$column && $dir==='asc'?'desc':'asc','page'=>1]) }}">
                                        {{ t($meta['label'], $meta['label_bn'] ?? $meta['label']) }}
                                        @if($sort===$column)
                                            {{ $dir==='asc'?'↑':'↓' }}
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                            <th>{{ t('Actions','অ্যাকশন') }}</th>
                        </tr>

                        <tr class="cfg-filter-row" id="cfg-filter-row">
                            @foreach($columns as $column=>$meta)
                                <th>
                                    @if(in_array($meta['type'],['status','boolean','select','category'],true))
                                        <select
                                            class="cfg-input"
                                            name="f[{{ $column }}]"
                                            form="cfg-filter-form"
                                            aria-label="{{ t('Filter','ফিল্টার') }} {{ t($meta['label'], $meta['label_bn'] ?? $meta['label']) }}"
                                        >
                                            <option value="">{{ t('All','সব') }}</option>

                                            @if(in_array($meta['type'],['status','boolean'],true))
                                                <option value="1" @selected((string)($filters[$column]??'')==='1')>
                                                    {{ $meta['type']==='status' ? t('Active','সক্রিয়') : t('Yes','হ্যাঁ') }}
                                                </option>
                                                <option value="0" @selected((string)($filters[$column]??'')==='0')>
                                                    {{ $meta['type']==='status' ? t('Inactive','নিষ্ক্রিয়') : t('No','না') }}
                                                </option>
                                            @elseif($meta['type']==='category')
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((string)($filters[$column]??'')===(string)$category->id)>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach($meta['options'] as $value=>$text)
                                                    <option value="{{ $value }}" @selected((string)($filters[$column]??'')===(string)$value)>
                                                        {{ t($text, $meta['options_bn'][$value] ?? $text) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @else
                                        <input
                                            name="f[{{ $column }}]"
                                            form="cfg-filter-form"
                                            class="cfg-input"
                                            type="{{ $meta['type']==='date'?'date':'text' }}"
                                            value="{{ is_scalar($filters[$column]??null)?$filters[$column]:'' }}"
                                            placeholder="{{ t('Filter...','ফিল্টার করুন...') }}"
                                            aria-label="{{ t('Filter','ফিল্টার') }} {{ t($meta['label'], $meta['label_bn'] ?? $meta['label']) }}"
                                        />
                                    @endif
                                </th>
                            @endforeach

                            <th>
                                <button class="cfg-btn cfg-primary" form="cfg-filter-form" type="submit">
                                    {{ t('Go','যান') }}
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                @foreach($columns as $column=>$meta)
                                    <td title="{{ $record->getAttribute($column) }}">
                                        @if($column==='code')
                                            <span class="cfg-code">{{ $record->code }}</span>
                                        @elseif($column==='is_active')
                                            <span class="cfg-status {{ $record->is_active?'':'off' }}">
                                                {{ $record->is_active ? t('Active','সক্রিয়') : t('Inactive','নিষ্ক্রিয়') }}
                                            </span>
                                        @elseif($meta['type']==='boolean')
                                            {{ $record->getAttribute($column) ? t('Yes','হ্যাঁ') : t('No','না') }}
                                        @elseif($meta['type']==='category')
                                            {{ $categoryNames[$record->getAttribute($column)] ?? '—' }}
                                        @elseif($meta['type']==='select')
                                            {{ t($meta['options'][$record->getAttribute($column)] ?? '—', $meta['options_bn'][$record->getAttribute($column)] ?? ($meta['options'][$record->getAttribute($column)] ?? '—')) }}
                                        @elseif($meta['type']==='date')
                                            {{ $record->getAttribute($column)?->format('Y-m-d H:i') ?? '—' }}
                                        @elseif($meta['type']==='hex' && $record->getAttribute($column))
                                            <span class="cfg-swatches" style="background:{{ preg_match('/^#[0-9a-fA-F]{6}$/',(string)$record->getAttribute($column))?$record->getAttribute($column):'transparent' }}"></span>{{ $record->getAttribute($column) }}
                                        @else
                                            {{ $record->getAttribute($column) ?? '—' }}
                                        @endif
                                    </td>
                                @endforeach

                                <td>
                                    <div class="cfg-actions">
                                        @if($permissions['update'])
                                            <button type="button" class="cfg-btn cfg-edit" data-id="{{ $record->id }}">
                                                {{ t('Edit','সম্পাদনা') }}
                                            </button>
                                        @endif

                                        @if($permissions['delete'])
                                            <form method="POST" action="{{ route($routePrefix.'.destroy',['business'=>$businessKey,'record'=>$record->id]) }}" class="cfg-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="cfg-btn cfg-danger">
                                                    {{ t('Delete','মুছে ফেলুন') }}
                                                </button>
                                            </form>
                                        @endif

                                        @unless($permissions['update'] || $permissions['delete'])
                                            <span class="cfg-count">—</span>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns)+1 }}" class="cfg-empty">
                                    {{ t('No records found. Try resetting filters.','কোনো তথ্য পাওয়া যায়নি। ফিল্টার রিসেট করে আবার চেষ্টা করুন।') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="cfg-bottom">
                <span>
                    {{ $records->firstItem()??0 }}–{{ $records->lastItem()??0 }}
                    {{ t('of','মোট') }}
                    {{ $records->total() }}
                    {{ t('records','টি রেকর্ড') }}
                </span>

                <nav class="cfg-pagination" aria-label="{{ t('Pagination','পেজিনেশন') }}">
                    @if($records->previousPageUrl())
                        <a href="{{ $records->previousPageUrl() }}">← {{ t('Prev','আগের') }}</a>
                    @else
                        <span>{{ t('Prev','আগের') }}</span>
                    @endif

                    <span>{{ $records->currentPage() }} / {{ max(1,$records->lastPage()) }}</span>

                    @if($records->nextPageUrl())
                        <a href="{{ $records->nextPageUrl() }}">{{ t('Next','পরের') }} →</a>
                    @else
                        <span>{{ t('Next','পরের') }}</span>
                    @endif
                </nav>
            </div>
        </section>
    </main>
</div>

@if($permissions['create'] || $permissions['update'])
    <div class="cfg-modal" id="cfg-modal" hidden>
        <div class="cfg-dialog" role="dialog" aria-modal="true" aria-labelledby="cfg-dialog-title" tabindex="-1">
            <div class="cfg-dialog-header">
                <div>
                    <h2 id="cfg-dialog-title">{{ t('Create Currencies','নতুন মুদ্রা তৈরি করুন') }}</h2>
                    <p>{{ t('Complete the form and save your changes.','ফর্মটি পূরণ করে পরিবর্তনগুলো সংরক্ষণ করুন।') }}</p>
                </div>

                <button class="cfg-btn cfg-dialog-close" type="button" id="cfg-close" aria-label="{{ t('Close modal','মডাল বন্ধ করুন') }}">
                    ×
                </button>
            </div>

            <form class="cfg-form" method="POST" id="cfg-form" action="{{ $storeUrl }}">
                @csrf
                <input type="hidden" name="_method" id="cfg-method" value="PUT" disabled/>
                <input type="hidden" name="_cfg_form" value="1"/>
                <input type="hidden" name="form_mode" id="cfg-form-mode" value="create"/>
                <input type="hidden" name="edit_id" id="cfg-edit-id" value=""/>

                @if($errors->any())
                    <div class="cfg-notice">{{ $errors->first() }}</div>
                @endif

                <div class="cfg-fields">
                    <div class="cfg-field">
                        <label for="cfg-name">
                            {{ t('Currency Name','মুদ্রার নাম') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <input name="name" id="cfg-name" type="text" class="cfg-input" maxlength="255" required/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-currency_code">
                            {{ t('ISO Code (e.g. BDT)','ISO কোড (যেমন BDT)') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <input name="currency_code" id="cfg-currency_code" type="text" class="cfg-input" maxlength="3" required/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-symbol">
                            {{ t('Symbol','প্রতীক') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <input name="symbol" id="cfg-symbol" type="text" class="cfg-input" maxlength="255" required/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-exchange_rate">
                            {{ t('Exchange Rate','বিনিময় হার') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <input name="exchange_rate" id="cfg-exchange_rate" type="number" class="cfg-input" step="0.000001" min="0" required/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-is_default">{{ t('Default','ডিফল্ট') }}</label>
                        <select name="is_default" id="cfg-is_default" class="cfg-input">
                            <option value="0">{{ t('No','না') }}</option>
                            <option value="1">{{ t('Yes','হ্যাঁ') }}</option>
                        </select>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-is-active">
                            {{ t('Status','অবস্থা') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <select name="is_active" id="cfg-is-active" class="cfg-input">
                            <option value="1">{{ t('Active','সক্রিয়') }}</option>
                            <option value="0">{{ t('Inactive','নিষ্ক্রিয়') }}</option>
                        </select>
                    </div>
                </div>

                <div class="cfg-actions-footer">
                    <button type="button" class="cfg-btn" id="cfg-cancel">
                        {{ t('Cancel','বাতিল') }}
                    </button>

                    <button type="submit" class="cfg-btn cfg-primary" id="cfg-save">
                        {{ t('Save Changes','পরিবর্তন সংরক্ষণ করুন') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@push('scripts')
@include('public.business.admin.configurations._ui_scripts')
@endpush
