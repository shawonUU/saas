@extends('public.business.admin.layout')
@section('content')
<div class="workspace-layout listui-page">
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content auth-crud">
@php $base='business.admin.authorization.roles'; $host=request()->route('business'); @endphp
<div class="cfg-heading"><div><div class="cfg-eyebrow">{{ t('Authorization','অনুমোদন') }} / {{ t('Roles','রোল') }}</div><h1>{{ t('Business roles','বিজনেস রোল') }}</h1><p class="auth-help">{{ t('Roles organize team users. Assign individual feature access under Access Control. Owner/Admin roles are protected.','রোল দিয়ে টিম ইউজার সাজান। আলাদা ফিচার অনুমতি অ্যাক্সেস কন্ট্রোলে দিন। Owner/Admin সুরক্ষিত।') }}</p></div></div>
@if(session('success'))<div class="auth-ok">{{session('success')}}</div>@endif
@if($errors->any())<div class="auth-error">{{implode(' · ',$errors->all())}}</div>@endif
<div class="listui-add"><button type="button" class="auth-btn auth-primary" data-listui-open="listui-role-add">+ {{ t('Add role','রোল যোগ করুন') }}</button></div>
<div class="cfg-modal listui-overlay" id="listui-role-add" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden><div class="cfg-dialog listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-role-title">
 <div class="cfg-dialog-header listui-dialog-head"><h2 id="listui-role-title">{{ t('Add business role','বিজনেস রোল যোগ করুন') }}</h2><button type="button" class="cfg-dialog-close listui-dialog-close" data-listui-close>×</button></div><div class="listui-dialog-body"><section class="auth-panel"><form action="{{route($base.'.store',['business'=>$host])}}" method="post" class="auth-form">@csrf
<label>{{ t('Role name','রোলের নাম') }}<input required maxlength="255" name="name" value="{{old('name')}}" placeholder="Supervisor"></label>
<label>{{ t('Status','স্ট্যাটাস') }}<select name="is_active"><option value="1">{{t('Active','সক্রিয়')}}</option><option value="0">{{t('Inactive','নিষ্ক্রিয়')}}</option></select></label>
<button class="auth-btn auth-primary">+ {{t('Add role','রোল যোগ করুন')}}</button></form></section></div></div></div>
<section class="cfg-panel auth-panel listui-list-card">
@include('public.business.admin.shared.list-ui.standalone-toolbar', ['hasFilters'=>true, 'excludeParams'=>['search','status']])
<div class="auth-scroll cfg-table-scroll listui-table-scroll"><table class="cfg-table auth-table"><thead><tr><th>#</th><th>{{t('Name','নাম')}}</th><th>{{t('Users','ইউজার')}}</th><th>{{t('Status','স্ট্যাটাস')}}</th><th>{{t('Actions','অ্যাকশন')}}</th></tr><tr class="cfg-filter-row listui-filter-row" id="listui-colfilters"><th><input class="cfg-input ret-input" form="listui-standard-form" name="f[id]" value="{{ is_scalar(request('f.id')) ? request('f.id') : '' }}" aria-label="{{ t('Column filter','কলাম ফিল্টার') }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th><th><input class="cfg-input ret-input" form="listui-standard-form" name="f[name]" value="{{ is_scalar(request('f.name')) ? request('f.name') : '' }}" aria-label="{{ t('Column filter','কলাম ফিল্টার') }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th><th></th><th><select class="cfg-input ret-input" form="listui-standard-form" name="f[is_active]" aria-label="{{ t('Status','স্ট্যাটাস') }}"><option value="">{{ t('All statuses','সব স্ট্যাটাস') }}</option><option value="1" @selected((string)request('f.is_active')==='1')>{{ t('Active','সক্রিয়') }}</option><option value="0" @selected((string)request('f.is_active')==='0')>{{ t('Inactive','নিষ্ক্রিয়') }}</option></select></th><th><button class="cfg-btn cfg-primary" form="listui-standard-form" type="submit">{{ t('Go','যান') }}</button></th></tr></thead><tbody>
@forelse($records as $role)
@php $protected=$role->is_owner || in_array(strtolower(trim($role->name)),['owner','admin']); @endphp
<tr data-audit-table="{{ $role->getTable() }}" data-audit-id="{{ $role->id }}"><td>{{$role->id}}</td><td><strong>{{$role->name}}</strong> @if($protected)<small>({{t('Protected','সুরক্ষিত')}})</small>@endif</td><td>{{$role->users_count}}</td><td>{{$role->is_active?t('Active','সক্রিয়'):t('Inactive','নিষ্ক্রিয়')}}</td><td>
@if(!$protected)
<button type="button" class="auth-btn" data-listui-open="role-edit-{{ $role->id }}">{{t('Edit','সম্পাদনা')}}</button><div class="cfg-modal listui-overlay" id="role-edit-{{ $role->id }}" data-listui-modal hidden><div class="cfg-dialog listui-dialog" role="dialog" aria-modal="true" aria-labelledby="role-edit-title-{{ $role->id }}"><div class="cfg-dialog-header listui-dialog-head"><h2 id="role-edit-title-{{ $role->id }}">{{ t('Edit role','রোল সম্পাদনা') }}: {{ $role->name }}</h2><button type="button" class="cfg-dialog-close listui-dialog-close" data-listui-close>×</button></div><div class="listui-dialog-body"><form method="post" action="{{route($base.'.update',['business'=>$host,'record'=>$role->id])}}" class="auth-form auth-edit">@csrf @method('PUT')<input name="name" required value="{{$role->name}}"><select name="is_active"><option value="1" @selected($role->is_active)>{{t('Active','সক্রিয়')}}</option><option value="0" @selected(!$role->is_active)>{{t('Inactive','নিষ্ক্রিয়')}}</option></select><button class="auth-btn auth-primary">{{t('Save','সংরক্ষণ')}}</button></form></div></div></div>
@if(!$role->users_count)<form method="post" action="{{route($base.'.destroy',['business'=>$host,'record'=>$role->id])}}" onsubmit="return confirm(@js(t('Delete this role?','এই রোল মুছবেন?')))" class="auth-inline">@csrf @method('DELETE')<button class="auth-btn auth-danger">{{t('Delete','মুছুন')}}</button></form>@endif
@endif</td></tr>
@empty<tr><td colspan="5">{{t('No roles found.','কোনো রোল পাওয়া যায়নি।')}}</td></tr>@endforelse
</tbody></table></div>@include('public.business.admin.shared.list-ui.pagination',['paginator'=>$records])</section>
</main></div>
@include('public.business.admin.authorization._crud_styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.shared.list-ui.scripts')
@endsection
