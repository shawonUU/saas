@extends('public.business.admin.layout')
@section('content')
<div class="workspace-layout">
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content auth-crud">
@php $base='business.admin.authorization.permissions'; $host=request()->route('business'); @endphp
<h2>{{t('Business permissions','বিজনেস পারমিশন')}}</h2><p class="auth-help">{{t('Create business-specific permissions under entitled modules, menus and child menus. Global permissions are read-only.','অনুমোদিত মডিউল, মেনু ও চাইল্ড মেনুর অধীনে পারমিশন তৈরি করুন। গ্লোবাল পারমিশন পরিবর্তনযোগ্য নয়।')}}</p>
@if(session('success'))<div class="auth-ok">{{session('success')}}</div>@endif
@if($errors->any())<div class="auth-error">{{implode(' · ',$errors->all())}}</div>@endif
<section class="auth-panel"><h3>{{t('New permission','নতুন পারমিশন')}}</h3><form method="post" action="{{route($base.'.store',['business'=>$host])}}" class="auth-form auth-perm-form">@csrf
<label>{{t('Name','নাম')}}<input name="name" required maxlength="255" value="{{old('name')}}"></label><label>{{t('Key (unique)','কি (ইউনিক)')}}<input name="key" required maxlength="255" placeholder="task.assign" value="{{old('key')}}"></label>
<label>{{t('Module','মডিউল')}}<select name="module_id" class="permission-module" required><option value="">{{t('Select module','মডিউল নির্বাচন')}}</option>@foreach($tree as $module)<option value="{{$module->id}}" @selected(old('module_id')==$module->id)>{{t($module->name,$module->name_bn)}}</option>@endforeach</select></label>
<label>{{t('Menu','মেনু')}}<select name="menu_id" class="permission-menu"><option value="">{{t('Module level','মডিউল পর্যায়')}}</option></select></label>
<label>{{t('Child menu','চাইল্ড মেনু')}}<select name="child_menu_id" class="permission-child"><option value="">{{t('None / menu level','নেই / মেনু পর্যায়')}}</option></select></label>
<label>{{t('Status','অবস্থা')}}<select name="is_active"><option value="1">{{t('Active','সক্রিয়')}}</option><option value="0">{{t('Inactive','নিষ্ক্রিয়')}}</option></select></label>
<label class="auth-wide">{{t('Description','বিবরণ')}}<input name="description" maxlength="2000" value="{{old('description')}}"></label><button class="auth-btn auth-primary">+ {{t('Add permission','পারমিশন যোগ করুন')}}</button></form></section>
<section class="auth-panel"><form method="get" class="auth-form"><input name="search" placeholder="{{t('Search by name or key','নাম বা কি দিয়ে খুঁজুন')}}" value="{{$search}}"><button class="auth-btn">{{t('Search','খুঁজুন')}}</button></form>
<div class="auth-scroll"><table class="auth-table"><thead><tr><th>#</th><th>{{t('Name / Key','নাম / কি')}}</th><th>{{t('Scope','পরিধি')}}</th><th>{{t('Module / Menu / Child','মডিউল / মেনু / চাইল্ড')}}</th><th>{{t('Status','অবস্থা')}}</th><th>{{t('Action','অ্যাকশন')}}</th></tr></thead><tbody>
@forelse($records as $record)
<tr><td>{{$record->id}}</td><td><strong>{{$record->name}}</strong><small class="auth-key">{{$record->key}}</small><small>{{$record->description}}</small></td><td>{{$record->business_id===null?t('Global','গ্লোবাল'):t('Business','বিজনেস')}}</td><td>{{$record->module_id??'—'}} / {{$record->menu_id??'—'}} / {{$record->child_menu_id??'—'}}</td><td>{{$record->is_active?t('Active','সক্রিয়'):t('Inactive','নিষ্ক্রিয়')}}</td><td>
@if($record->business_id!==null)
<details><summary class="auth-btn">{{t('Edit','সম্পাদনা')}}</summary><form method="post" action="{{route($base.'.update',['business'=>$host,'record'=>$record->id])}}" class="auth-form auth-perm-form auth-edit">@csrf @method('PUT')
<input name="name" required value="{{$record->name}}" placeholder="Name"><input name="key" required value="{{$record->key}}" placeholder="Key"><input name="description" value="{{$record->description}}" placeholder="Description">
<select name="module_id" required class="permission-module" data-selected="{{$record->module_id}}"><option value="">{{t('Module','মডিউল')}}</option>@foreach($tree as $module)<option value="{{$module->id}}" @selected($record->module_id==$module->id)>{{t($module->name,$module->name_bn)}}</option>@endforeach</select>
<select name="menu_id" class="permission-menu" data-selected="{{$record->menu_id}}"></select><select name="child_menu_id" class="permission-child" data-selected="{{$record->child_menu_id}}"></select>
<select name="is_active"><option value="1" @selected($record->is_active)>{{t('Active','সক্রিয়')}}</option><option value="0" @selected(!$record->is_active)>{{t('Inactive','নিষ্ক্রিয়')}}</option></select><button class="auth-btn auth-primary">{{t('Save','সংরক্ষণ')}}</button></form></details>
<form method="post" class="auth-inline" action="{{route($base.'.destroy',['business'=>$host,'record'=>$record->id])}}" onsubmit="return confirm(@js(t('Delete this permission?','এই পারমিশন মুছবেন?')))" >@csrf @method('DELETE')<button class="auth-btn auth-danger">{{t('Delete','মুছুন')}}</button></form>
@else<small>{{t('Managed centrally','কেন্দ্রীয়ভাবে নিয়ন্ত্রিত')}}</small>@endif</td></tr>
@empty<tr><td colspan="6">{{t('No permissions found.','পারমিশন পাওয়া যায়নি।')}}</td></tr>@endforelse
</tbody></table></div><div class="auth-pagination">{{$records->links()}}</div></section></main></div>
@include('public.business.admin.authorization._crud_styles')
@php $permissionTree=$tree->map(fn($m)=>['id'=>$m->id,'menus'=>$m->menus->map(fn($menu)=>['id'=>$menu->id,'name'=>t($menu->name,$menu->name_bn),'children'=>$menu->children->map(fn($child)=>['id'=>$child->id,'name'=>t($child->name,$child->name_bn)])->values()])->values()])->values(); @endphp
<script>
(()=>{const tree=@json($permissionTree);
const fill=(select,items,placeholder,selected)=>{select.replaceChildren(new Option(placeholder,''));items.forEach(i=>select.add(new Option(i.name,i.id)));if(selected)select.value=selected;};
document.querySelectorAll('.auth-perm-form').forEach(form=>{const mod=form.querySelector('.permission-module'),menu=form.querySelector('.permission-menu'),child=form.querySelector('.permission-child');
function updateMenus(keep=false){const m=tree.find(x=>String(x.id)===mod.value);fill(menu,m?.menus||[],'—',keep?menu.dataset.selected:'');updateChildren(keep);}
function updateChildren(keep=false){const m=tree.find(x=>String(x.id)===mod.value),mnu=m?.menus.find(x=>String(x.id)===menu.value);fill(child,mnu?.children||[],'—',keep?child.dataset.selected:'');}
mod.addEventListener('change',()=>updateMenus());menu.addEventListener('change',()=>updateChildren());updateMenus(true);
});})();
</script>
@endsection
