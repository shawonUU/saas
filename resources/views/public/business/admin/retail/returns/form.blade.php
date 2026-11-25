@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Create Sales Return','বিক্রয় রিটার্ন তৈরি') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form method="POST" class="ret-card" action="{{ route('business.admin.sales.returns.new-return.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Sales Order','বিক্রয় অর্ডার') }}</label><select name="order_id" id="ret-return-order" required class="ret-input"><option value="">{{ t('Select Order','অর্ডার নির্বাচন করুন') }}</option>@foreach($orders as $order)<option value="{{ $order->id }}">{{ $order->order_no }} ({{ $order->total }})</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Reason','কারণ') }}</label><input class="ret-input" name="reason" placeholder="{{ t('Customer return','কাস্টমার রিটার্ন') }}"></div></div><hr class="ret-divider"><div id="ret-return-items"></div><button class="ret-btn" type="submit">{{ t('Post Return & Restock','রিটার্ন পোস্ট ও স্টক ফেরত') }}</button></form>
@php $orderData=$orders->mapWithKeys(fn($o)=>[$o->id=>$o->items->map(fn($i)=>['id'=>$i->id,'name'=>$i->name,'left'=>round($i->quantity-$i->returned_quantity,3)])->values()->all()])->all(); @endphp
<script>
document.addEventListener('DOMContentLoaded',()=>{const orderData=@json($orderData);const sel=document.getElementById('ret-return-order'),holder=document.getElementById('ret-return-items');
sel.onchange=()=>{holder.replaceChildren();let n=0;for(const item of orderData[sel.value]||[]){if(item.left<=0)continue;const row=document.createElement('div');row.className='ret-cart-row';const label=document.createElement('div');label.textContent=item.name+' (available '+item.left+')';const id=document.createElement('input');id.type='hidden';id.name=`items[${n}][order_item_id]`;id.value=item.id;const qty=document.createElement('input');qty.className='ret-input';qty.type='number';qty.min=.001;qty.max=item.left;qty.step=.001;qty.value=0;qty.name=`items[${n}][quantity]`;row.append(label,id,qty);holder.append(row);n++;}};
});
</script>
</main></div>
@endsection
