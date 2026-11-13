@extends('layouts.portal')
@section('content')
<main class="authority-wrap">
    <div class="authority-head"><div><small>{{ t('SAAS AUTHORITY','SaaS অথরিটি') }}</small><h1>{{ t('Modules, menus & pages','মডিউল, মেনু ও পেজ') }}</h1><p>{{ t('Manage the navigation tree used by every business.','সব শপের নেভিগেশন তালিকা এখানে নিয়ন্ত্রণ করুন।') }}</p></div><a class="btn btn-outline" href="{{ route('authority.access.index') }}">{{ t('Category / shop access','ক্যাটাগরি / শপ অ্যাক্সেস') }}</a></div>
    <div class="ws-card"><h2>{{ t('Add a module','মডিউল যোগ করুন') }}</h2>
        <form class="authority-form" method="post" action="{{ route('authority.features.store','module') }}">@csrf
            <input name="name" placeholder="Module name" aria-label="Module name" required><input name="name_bn" placeholder="মডিউলের বাংলা নাম" aria-label="Module name Bengali"><input name="slug" placeholder="module-slug" aria-label="Module slug" required><input name="icon" placeholder="Icon (optional)" aria-label="Icon"><input type="number" name="sort_order" value="0" min="0" aria-label="Sort order"><input type="hidden" name="is_active" value="1"><button class="btn">{{ t('Add module','মডিউল যোগ') }}</button>
        </form>
    </div>
    <div class="ws-card"><h2>{{ t('Add a menu','মেনু যোগ করুন') }}</h2>
        <form class="authority-form" method="post" action="{{ route('authority.features.store','menu') }}">@csrf
            <select name="module_id" required><option value="">{{ t('Select module','মডিউল বাছুন') }}</option>@foreach($modules as $module)<option value="{{ $module->id }}">{{ $module->name }}</option>@endforeach</select><input name="name" placeholder="Menu name" aria-label="Menu name" required><input name="name_bn" placeholder="মেনুর বাংলা নাম" aria-label="Menu name Bengali"><input name="slug" placeholder="menu-slug" aria-label="Menu slug" required><input name="icon" placeholder="Icon" aria-label="Icon"><input type="hidden" name="is_active" value="1"><button class="btn">{{ t('Add menu','মেনু যোগ') }}</button>
        </form>
    </div>
    <div class="ws-card"><h2>{{ t('Add a child menu','চাইল্ড মেনু যোগ করুন') }}</h2>
        <form class="authority-form" method="post" action="{{ route('authority.features.store','child') }}">@csrf
            <select name="menu_id" required><option value="">{{ t('Select menu','মেনু বাছুন') }}</option>@foreach($modules as $module)@foreach($module->menus as $menu)<option value="{{ $menu->id }}">{{ $module->name }} / {{ $menu->name }}</option>@endforeach @endforeach</select><input name="name" placeholder="Page name" aria-label="Page name" required><input name="name_bn" placeholder="পেজের বাংলা নাম" aria-label="Page name Bengali"><input name="slug" placeholder="page-slug" aria-label="Page slug" required><input name="route" placeholder="Route (optional)" aria-label="Route"><input type="hidden" name="is_active" value="1"><button class="btn">{{ t('Add page','চাইল্ড মেনু যোগ') }}</button>
        </form>
    </div>
    <div class="ws-card"><h2>{{ t('Existing menu tree','বিদ্যমান মেনু তালিকা') }}</h2>
    @forelse($modules as $module)
        <details class="editor-item" open><summary><strong>{{ $module->name }}</strong><span>{{ $module->slug }} · {{ $module->is_active ? t('Active','চালু') : t('Disabled','বন্ধ') }}</span></summary>
        <form class="authority-edit" method="post" action="{{ route('authority.features.update',['type'=>'module','id'=>$module->id]) }}">@csrf @method('PUT')
            <input aria-label="Module name" name="name" value="{{ $module->name }}" required><input aria-label="Module Bengali name" name="name_bn" value="{{ $module->name_bn }}" placeholder="বাংলা নাম"><input aria-label="Module slug" name="slug" value="{{ $module->slug }}" required><input aria-label="Module icon" name="icon" value="{{ $module->icon }}" placeholder="Icon"><input aria-label="Module order" type="number" min="0" name="sort_order" value="{{ $module->sort_order }}"><select name="is_active" aria-label="Module status"><option value="1" @selected($module->is_active)>{{ t('Active','চালু') }}</option><option value="0" @selected(!$module->is_active)>{{ t('Disabled','বন্ধ') }}</option></select><button class="btn btn-outline" type="submit">{{ t('Update module','মডিউল আপডেট') }}</button>
        </form>
        @foreach($module->menus as $menu)
            <details class="editor-item editor-nested"><summary><strong>{{ $menu->name }}</strong><span>{{ $menu->slug }} · {{ $menu->is_active ? t('Active','চালু') : t('Disabled','বন্ধ') }}</span></summary>
            <form class="authority-edit" method="post" action="{{ route('authority.features.update',['type'=>'menu','id'=>$menu->id]) }}">@csrf @method('PUT')
                <select name="module_id" aria-label="Parent module" required>@foreach($modules as $option)<option value="{{ $option->id }}" @selected($menu->module_id === $option->id)>{{ $option->name }}</option>@endforeach</select><input aria-label="Menu name" name="name" value="{{ $menu->name }}" required><input aria-label="Menu Bengali name" name="name_bn" value="{{ $menu->name_bn }}" placeholder="বাংলা নাম"><input aria-label="Menu slug" name="slug" value="{{ $menu->slug }}" required><input aria-label="Menu icon" name="icon" value="{{ $menu->icon }}" placeholder="Icon"><input type="hidden" name="route" value="{{ $menu->route }}"><input type="hidden" name="permission" value="{{ $menu->permission }}"><input aria-label="Menu order" type="number" min="0" name="sort_order" value="{{ $menu->sort_order }}"><select name="is_active" aria-label="Menu status"><option value="1" @selected($menu->is_active)>{{ t('Active','চালু') }}</option><option value="0" @selected(!$menu->is_active)>{{ t('Disabled','বন্ধ') }}</option></select><button class="btn btn-outline" type="submit">{{ t('Update menu','মেনু আপডেট') }}</button>
            </form>
            @foreach($menu->children as $child)
                <details class="editor-item editor-nested"><summary><strong>{{ $child->name }}</strong><span>{{ $child->slug }} · {{ $child->is_active ? t('Active','চালু') : t('Disabled','বন্ধ') }}</span></summary>
                <form class="authority-edit" method="post" action="{{ route('authority.features.update',['type'=>'child','id'=>$child->id]) }}">@csrf @method('PUT')
                    <select name="menu_id" aria-label="Parent menu" required>@foreach($modules as $optionModule)@foreach($optionModule->menus as $optionMenu)<option value="{{ $optionMenu->id }}" @selected($child->menu_id === $optionMenu->id)>{{ $optionModule->name }} / {{ $optionMenu->name }}</option>@endforeach @endforeach</select><input aria-label="Page name" name="name" value="{{ $child->name }}" required><input aria-label="Page Bengali name" name="name_bn" value="{{ $child->name_bn }}" placeholder="বাংলা নাম"><input aria-label="Page slug" name="slug" value="{{ $child->slug }}" required><input aria-label="Page route" name="route" value="{{ $child->route }}" placeholder="Route"><input aria-label="Permission key" name="permission" value="{{ $child->permission }}" placeholder="Permission"><input aria-label="Page order" type="number" min="0" name="sort_order" value="{{ $child->sort_order }}"><select name="is_active" aria-label="Page status"><option value="1" @selected($child->is_active)>{{ t('Active','চালু') }}</option><option value="0" @selected(!$child->is_active)>{{ t('Disabled','বন্ধ') }}</option></select><button class="btn btn-outline" type="submit">{{ t('Update page','পেজ আপডেট') }}</button>
                </form></details>
            @endforeach
            </details>
        @endforeach
        </details>
    @empty<p>{{ t('No modules yet. Add the first module above.','এখনো কোনো মডিউল নেই। উপরে প্রথম মডিউল যোগ করুন।') }}</p>@endforelse</div>
</main>
@endsection
