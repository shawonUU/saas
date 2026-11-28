@extends('public.business.admin.layout')
@section('content')
<div class="workspace-layout listui-page">
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content auth-crud">
<div class="cfg-heading"><div><div class="cfg-eyebrow">{{ t('Authorization','অনুমোদন') }} / {{ t('Permissions','পারমিশন') }}</div><h1>{{ t('Global permissions','গ্লোবাল পারমিশন') }}</h1><p class="auth-help">{{ t('These permission definitions are shared by all businesses. Manage employee grants in Access Control; only SaaS Authority can change definitions.','এই পারমিশনগুলো সব ব্যবসার জন্য একই। ইউজারের অনুমতি Access Control থেকে দিন। শুধু SaaS Authority পারমিশনের সংজ্ঞা পরিবর্তন করতে পারে।') }}</p></div></div>
<section class="cfg-panel auth-panel listui-list-card">

@include('public.business.admin.shared.list-ui.standalone-toolbar', ['hasFilters'=>true, 'excludeParams'=>['search']])
<div class="auth-scroll cfg-table-scroll listui-table-scroll"><table class="cfg-table auth-table"><thead><tr><th>#</th><th>{{t('Permission / key','পারমিশন / কি')}}</th><th>{{t('Module / Menu / Child','মডিউল / মেনু / চাইল্ড')}}</th><th>{{t('Status','অবস্থা')}}</th><th>{{ t('Log','লগ') }}</th></tr><tr class="cfg-filter-row listui-filter-row" id="listui-colfilters"><th><input class="cfg-input ret-input" form="listui-standard-form" name="f[id]" value="{{ is_scalar(request('f.id')) ? request('f.id') : '' }}" aria-label="{{ t('Column filter','কলাম ফিল্টার') }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th><th><input class="cfg-input ret-input" form="listui-standard-form" name="f[identity]" value="{{ is_scalar(request('f.identity')) ? request('f.identity') : '' }}" aria-label="{{ t('Permission name or key','পারমিশনের নাম বা কি') }}" placeholder="{{ t('Name / key','নাম / কি') }}"></th><th><input class="cfg-input ret-input" form="listui-standard-form" name="f[feature]" value="{{ is_scalar(request('f.feature')) ? request('f.feature') : '' }}" aria-label="{{ t('Module, menu or child','মডিউল মেনু বা চাইল্ড') }}" placeholder="{{ t('Module / menu','মডিউল / মেনু') }}"></th><th><div class="listui-status-filter"><select class="cfg-input ret-input" form="listui-standard-form" name="f[is_active]" aria-label="{{ t('Status','স্ট্যাটাস') }}"><option value="">{{ t('All statuses','সব স্ট্যাটাস') }}</option><option value="1" @selected((string)request('f.is_active')==='1')>{{ t('Active','সক্রিয়') }}</option><option value="0" @selected((string)request('f.is_active')==='0')>{{ t('Inactive','নিষ্ক্রিয়') }}</option></select><button class="cfg-btn cfg-primary" form="listui-standard-form" type="submit">{{ t('Go','যান') }}</button></div></th><th></th></tr></thead><tbody>
@forelse($records as $item)
<tr data-audit-table="{{ $item->getTable() }}" data-audit-id="{{ $item->id }}"><td>{{ $item->id }}</td><td><strong>{{ $item->name }}</strong><small class="auth-key">{{ $item->key }}</small><small>{{ $item->description }}</small></td>
<td>{{ $item->module?->name ?? '—' }} / {{ $item->menu?->name ?? '—' }} / {{ $item->childMenu?->name ?? '—' }}</td><td>{{ $item->is_active ? t('Active','সক্রিয়') : t('Inactive','নিষ্ক্রিয়') }}</td><td></td></tr>
@empty<tr><td colspan="5">{{t('No permissions found.','পারমিশন পাওয়া যায়নি।')}}</td></tr>@endforelse
</tbody></table></div>@include('public.business.admin.shared.list-ui.pagination',['paginator'=>$records])
</section></main></div>
@include('public.business.admin.authorization._crud_styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.shared.list-ui.scripts')
@endsection
