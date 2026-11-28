@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Inventory','ইনভেন্টরি') }} / {{ t('Warehouses','গুদাম') }}</div><h1>{{ t("Stock Transfers","স্টক স্থানান্তর") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('Transfer Stock','স্টক স্থানান্তর') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('Transfer Stock','স্টক স্থানান্তর') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form method="POST" class="ret-card" action="{{ route('business.admin.inventory.movements.stock-transfer.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('From Warehouse','কোন গুদাম থেকে') }}</label><select class="ret-input" name="from_warehouse_id" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div><div class="ret-field"><label>{{ t('To Warehouse','কোন গুদামে') }}</label><select class="ret-input" name="to_warehouse_id" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Variant','ভ্যারিয়েন্ট') }}</label><select class="ret-input" name="variant_id" required>@foreach($variants as $x)<option value="{{ $x->id }}">{{ $x->sku }}</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Quantity','পরিমাণ') }}</label><input class="ret-input" name="quantity" type="number" min=".001" step=".001" required></div><div class="ret-field"><label>{{ t('Note','নোট') }}</label><input class="ret-input" name="note"></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Transfer Stock','স্টক স্থানান্তর') }}</button></form></div>
 </div>
</div>
<div class="ret-card"><h3>{{ t('Warehouse Stock','গুদামভিত্তিক স্টক') }}</h3><div class="ret-table-wrap"><table class="ret-table"><tr><th>Warehouse</th><th>SKU</th><th>Quantity</th></tr>@foreach($stocks as $wid=>$rows)@foreach($rows as $s)<tr><td>{{ $warehouses->firstWhere('id',$wid)?->name }}</td><td>{{ $variants->firstWhere('id',$s->variant_id)?->sku }}</td><td>{{ $s->quantity }}</td></tr>@endforeach @endforeach</table></div></div>@php $columns=['transfer_no'=>'Reference','from_warehouse_id'=>'From','to_warehouse_id'=>'To','created_at'=>'Created']; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection
