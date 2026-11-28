
@extends('public.business.admin.layout')

@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.configurations._ui_styles')
<style>
.cfg-customers-toolbar{display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:13px 17px;flex-wrap:wrap}
.cfg-customers-toolbar .cfg-toolbar-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;margin-left:auto;flex-wrap:wrap}
.cfg-customers-toolbar .cfg-small{height:38px;width:auto;min-width:132px;flex:none;font-size:12px}
.cfg-customers-toolbar .cfg-action-icon{display:inline-flex;align-items:center;justify-content:center;gap:7px;height:38px;padding:0 12px;flex:none}
.cfg-customers-toolbar .cfg-action-icon svg{width:16px;height:16px;flex:none;stroke:currentColor;stroke-width:1.8;fill:none;stroke-linecap:round;stroke-linejoin:round}
.cfg-customers-toolbar .cfg-filter-toggle{position:relative;padding-left:35px}
.cfg-customers-toolbar .cfg-filter-toggle::before{content:'';position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;background:currentColor;-webkit-mask:center/contain no-repeat url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round"%3E%3Cpath d="M4 7h16M7 12h10M10 17h4"/%3E%3C/svg%3E');mask:center/contain no-repeat url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round"%3E%3Cpath d="M4 7h16M7 12h10M10 17h4"/%3E%3C/svg%3E')}
.cfg-customers-toolbar .cfg-export-excel{color:#16764c!important;border-color:rgba(22,118,76,.3)!important;background:rgba(22,118,76,.07)!important}
.cfg-customers-toolbar .cfg-export-pdf{color:#bc4355!important;border-color:rgba(188,67,85,.3)!important;background:rgba(188,67,85,.07)!important}
.cfg-customers-toolbar .cfg-export-excel:hover{background:rgba(22,118,76,.14)!important}
.cfg-customers-toolbar .cfg-export-pdf:hover{background:rgba(188,67,85,.14)!important}
.cfg-customers-toolbar .cfg-toolbar-separator{height:23px;border-left:1px solid var(--ui-border);margin:0 2px}
@media(max-width:630px){.cfg-customers-toolbar .cfg-toolbar-actions{width:100%;justify-content:flex-end}.cfg-customers-toolbar .cfg-action-icon{padding:0 9px;gap:5px}.cfg-customers-toolbar .cfg-small{min-width:110px}.cfg-customers-toolbar .cfg-filter-toggle{padding-left:30px}.cfg-customers-toolbar .cfg-filter-toggle::before{left:9px}.cfg-customers-toolbar .cfg-toolbar-separator{display:none}}

/* customers-toolbar-inline-v2: strict non-stretch desktop alignment */
.cfg-page form.cfg-customers-toolbar{display:flex!important;align-items:center!important;justify-content:flex-end!important;flex-wrap:nowrap!important;min-height:61px;padding:10px 15px!important;box-sizing:border-box}
.cfg-page .cfg-customers-toolbar>.cfg-toolbar-actions{display:flex!important;align-items:center!important;justify-content:flex-end!important;flex:0 1 auto!important;flex-wrap:nowrap!important;width:max-content!important;max-width:100%!important;min-width:0!important;margin:0 0 0 auto!important;gap:8px!important}
.cfg-page .cfg-customers-toolbar select.cfg-input.cfg-small{display:block!important;box-sizing:border-box!important;width:136px!important;min-width:136px!important;max-width:136px!important;flex:0 0 136px!important;height:38px!important;padding:0 29px 0 12px!important;font-size:12px!important}
.cfg-page .cfg-customers-toolbar .cfg-btn.cfg-action-icon{display:inline-flex!important;align-items:center!important;justify-content:center!important;flex:0 0 auto!important;min-width:0!important;width:auto!important;height:38px!important;min-height:38px!important;padding:0 11px!important;white-space:nowrap!important;line-height:1!important}
.cfg-page .cfg-customers-toolbar .cfg-btn.cfg-filter-toggle{padding-left:35px!important}
.cfg-page .cfg-customers-toolbar .cfg-filter-toggle::before{left:11px}
.cfg-page .cfg-customers-toolbar .cfg-toolbar-separator{flex:0 0 1px!important;height:22px!important;margin:0 2px!important}
@media(max-width:780px){.cfg-page form.cfg-customers-toolbar{flex-wrap:wrap!important}.cfg-page .cfg-customers-toolbar>.cfg-toolbar-actions{flex-wrap:wrap!important;width:100%!important;justify-content:flex-end!important}.cfg-page .cfg-customers-toolbar .cfg-toolbar-separator{display:none!important}}
@media(max-width:480px){.cfg-page form.cfg-customers-toolbar{padding:10px!important}.cfg-page .cfg-customers-toolbar>.cfg-toolbar-actions{gap:7px!important}.cfg-page .cfg-customers-toolbar select.cfg-input.cfg-small{width:124px!important;min-width:124px!important;max-width:124px!important;flex-basis:124px!important}.cfg-page .cfg-customers-toolbar .cfg-btn.cfg-action-icon{padding:0 9px!important}.cfg-page .cfg-customers-toolbar .cfg-btn.cfg-filter-toggle{padding-left:30px!important}.cfg-page .cfg-customers-toolbar .cfg-filter-toggle::before{left:8px}}
</style>
@endpush

@section('content')
@php
    $businessKey = request()->route('business');
    $businessKey = is_object($businessKey) ? ($businessKey->domain ?? $businessKey->slug ?? '') : $businessKey;
    $indexUrl = route($routePrefix.'.index', ['business'=>$businessKey]);
    $storeUrl = route($routePrefix.'.store', ['business'=>$businessKey]);
    $excelUrl = route($routePrefix.'.export', ['business'=>$businessKey, 'format'=>'excel']);
    $pdfUrl = route($routePrefix.'.export', ['business'=>$businessKey, 'format'=>'pdf']);
    $updateTemplate = route($routePrefix.'.update', ['business'=>$businessKey,'record'=>'__ID__']);
    $recordPayload = $records->getCollection()->mapWithKeys(fn($r) => [
        $r->id => $r->only(array_merge(['id','is_active'],array_keys($fields)))
    ])->all();
    $categoryNames = $categories->mapWithKeys(fn($category) => [$category->id => t($category->name, $category->name_bn)]);
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
                <div class="cfg-eyebrow">{{ t('Configurations','কনফিগারেশন') }} / {{ t('Customers','কাস্টমার') }}</div>
                <h1>{{ session('portal_lang') === 'bn' ? $titleBn : $title }}</h1>
            </div>

            @if($permissions['create'])
                <button type="button" class="cfg-btn cfg-primary" id="cfg-add">
                    + {{ t('Add New','নতুন যোগ করুন') }}
                </button>
            @endif
        </div>

        @if(session('error'))
            <div class="cfg-notice" role="alert" style="margin:0 0 14px">{{ session('error') }}</div>
        @endif

        <section class="cfg-panel">
            <form method="GET" action="{{ $indexUrl }}" id="cfg-filter-form" class="cfg-toolbar cfg-customers-toolbar">
                <div class="cfg-toolbar-actions">
                    <select name="per_page" class="cfg-input cfg-small" aria-label="{{ t('Rows per page','প্রতি পৃষ্ঠায় সারি') }}" onchange="this.form.requestSubmit()">
                        @foreach([10,15,25,50,100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} {{ t('per page','প্রতি পৃষ্ঠায়') }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="cfg-btn cfg-action-icon cfg-filter-toggle" id="cfg-toggle-filters" aria-expanded="true" title="{{ t('Show or hide column filters','কলাম ফিল্টার দেখান বা লুকান') }}">
                        {{ t('Hide Column Filters','কলাম ফিল্টার লুকান') }}
                    </button>
                    <a class="cfg-btn cfg-action-icon" href="{{ $indexUrl }}" title="{{ t('Reset all filters','সব ফিল্টার রিসেট করুন') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11a9 9 0 1 1 3.2 6.9"/><path d="M3 4v7h7"/></svg>{{ t('Reset','রিসেট') }}
                    </a>
                    <span class="cfg-toolbar-separator" aria-hidden="true"></span>
                    <a class="cfg-btn cfg-action-icon cfg-export-excel" data-cfg-export href="{{ $excelUrl }}?{{ http_build_query(request()->except('page')) }}" title="{{ t('Download filtered Excel','ফিল্টার করা এক্সেল ডাউনলোড') }}" aria-label="{{ t('Download Excel','এক্সেল ডাউনলোড') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 5 6m0-6-5 6"/></svg>Excel
                    </a>
                    <a class="cfg-btn cfg-action-icon cfg-export-pdf" data-cfg-export href="{{ $pdfUrl }}?{{ http_build_query(request()->except('page')) }}" title="{{ t('Download filtered PDF','ফিল্টার করা পিডিএফ ডাউনলোড') }}" aria-label="{{ t('Download PDF','পিডিএফ ডাউনলোড') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>PDF
                    </a>
                </div>
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
                                                        {{ t($category->name, $category->name_bn) }}
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
                            <tr data-audit-table="{{ $record->getTable() }}" data-audit-id="{{ $record->id }}">
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
                                            {{ in_array($column, ['name','description','address'], true) ? t($record->getAttribute($column), $record->getAttribute($column.'_bn')) : ($record->getAttribute($column) ?? '—') }}
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
                    <h2 id="cfg-dialog-title">{{ t('Create Customers','নতুন কাস্টমার তৈরি করুন') }}</h2>
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
                            {{ t('Customer Name','কাস্টমারের নাম') }}
                            <span class="cfg-required">*</span>
                        </label>
                        <input name="name" id="cfg-name" type="text" class="cfg-input" maxlength="255" required/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-phone">{{ t('Phone','ফোন নম্বর') }}</label>
                        <input name="phone" id="cfg-phone" type="text" class="cfg-input" maxlength="255"/>
                    </div>

                    <div class="cfg-field">
                        <label for="cfg-email">{{ t('Email','ইমেইল') }}</label>
                        <input name="email" id="cfg-email" type="email" class="cfg-input" maxlength="255"/>
                    </div>

                    <div class="cfg-field wide">
                        <label for="cfg-address">{{ t('Address','ঠিকানা') }}</label>
                        <textarea name="address" id="cfg-address" class="cfg-input" maxlength="2000"></textarea>
                    </div>

                    <div class="cfg-field wide">
                        <label for="cfg-notes">{{ t('Notes','নোট') }}</label>
                        <textarea name="notes" id="cfg-notes" class="cfg-input" maxlength="2000"></textarea>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('cfg-filter-form');
    if (!form) return;
    document.querySelectorAll('.cfg-customers-toolbar [data-cfg-export]').forEach(link => {
        link.addEventListener('click', () => {
            const params = new URLSearchParams(new FormData(form));
            link.href = link.href.split('?')[0] + (params.toString() ? '?' + params.toString() : '');
        });
    });
});
</script>
@endpush
