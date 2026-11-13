@extends('layouts.portal')
@section('content')
<main class="authority-wrap"><div class="authority-head"><div><small>{{ $mode === 'category' ? t('CATEGORY DEFAULTS','ক্যাটাগরি ডিফল্ট') : t('SHOP OVERRIDES','শপ ওভাররাইড') }}</small><h1>{{ $subject->name }}</h1><p>{{ $mode === 'shop' ? t('Inherit uses category defaults. Override only what is different.','Inherit বাছলে ক্যাটাগরির ডিফল্ট কার্যকর থাকবে।') : t('Select features available to businesses in this category.','এই ক্যাটাগরির শপগুলো যে ফিচার পাবে তা নির্বাচন করুন।') }}</p></div><a class="btn btn-outline" href="{{ route('authority.access.index') }}">{{ t('Back','ফিরুন') }}</a></div>
<form method="post" action="{{ $mode === 'category' ? route('authority.access.category.save',$subject) : route('authority.access.shop.save',$subject) }}">@csrf @method('PUT')
<div class="ws-card"><h2>{{ t('Modules / Menus / Child Menus','মডিউল / মেনু / চাইল্ড মেনু') }}</h2>
@foreach($modules as $module)
    <div class="access-module"><strong>{{ navName($module) }}</strong>
        @if($mode === 'category')<label><input type="checkbox" name="access[module][{{ $module->id }}]" value="1" @checked(in_array($module->id,$selected['module']))> {{ t('Enable module','মডিউল চালু') }}</label>
        @else <select name="access[module][{{ $module->id }}]" aria-label="{{ navName($module) }}">@foreach(['inherit'=>t('Inherit','ডিফল্ট'),'1'=>t('Enabled','চালু'),'0'=>t('Disabled','বন্ধ')] as $value=>$label)<option value="{{ $value }}" @selected(array_key_exists($module->id,$selected['module']) && (string)$selected['module'][$module->id] === (string)$value || $value === 'inherit' && !array_key_exists($module->id,$selected['module']))>{{ $label }}</option>@endforeach</select> @endif
        @foreach($module->menus as $menu)
            <div class="access-menu"><span>↳ {{ navName($menu) }}</span>
            @if($mode === 'category')<label><input type="checkbox" name="access[menu][{{ $menu->id }}]" value="1" @checked(in_array($menu->id,$selected['menu']))> {{ t('Enable','চালু') }}</label>
            @else <select name="access[menu][{{ $menu->id }}]" aria-label="{{ navName($menu) }}">@foreach(['inherit'=>t('Inherit','ডিফল্ট'),'1'=>t('Enabled','চালু'),'0'=>t('Disabled','বন্ধ')] as $value=>$label)<option value="{{ $value }}" @selected(array_key_exists($menu->id,$selected['menu']) && (string)$selected['menu'][$menu->id] === (string)$value || $value === 'inherit' && !array_key_exists($menu->id,$selected['menu']))>{{ $label }}</option>@endforeach</select> @endif
            @foreach($menu->children as $child)
                <div class="access-child"><span>↳ {{ navName($child) }}</span>
                @if($mode === 'category')<label><input type="checkbox" name="access[child][{{ $child->id }}]" value="1" @checked(in_array($child->id,$selected['child']))> {{ t('Enable','চালু') }}</label>
                @else <select name="access[child][{{ $child->id }}]" aria-label="{{ navName($child) }}">@foreach(['inherit'=>t('Inherit','ডিফল্ট'),'1'=>t('Enabled','চালু'),'0'=>t('Disabled','বন্ধ')] as $value=>$label)<option value="{{ $value }}" @selected(array_key_exists($child->id,$selected['child']) && (string)$selected['child'][$child->id] === (string)$value || $value === 'inherit' && !array_key_exists($child->id,$selected['child']))>{{ $label }}</option>@endforeach</select> @endif
                </div>
            @endforeach</div>
        @endforeach
    </div>
@endforeach
<div class="access-save"><span>{{ t('A disabled parent hides every child below it.','Parent বন্ধ থাকলে সব Child-ও বন্ধ থাকবে।') }}</span><button class="btn" type="submit">{{ t('Save access','অ্যাক্সেস সেভ করুন') }}</button></div></div></form></main>
@endsection
