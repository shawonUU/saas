@extends('public.business.public.ecommerce.account.shell')
@section('title',t('Buy again','আবার কিনুন'))
@section('account_heading',t('Buy again','আবার কিনুন'))
@section('account_content')
<div class="ec-card"><h2>{{ $record->order_no }}</h2><p>{{ t('Select products to purchase again. Current prices and availability apply.','আবার কেনার জন্য পণ্য বেছে নিন। বর্তমান মূল্য ও স্টক প্রযোজ্য।') }}</p></div>
@foreach($items as $item)@php $product=$products->first(fn($p)=>$p->variants->contains('id',$item->variant_id)); @endphp<div class="ec-card ec-row"><div><strong>{{ $item->name }}</strong><p>{{ t('Previous quantity','আগের পরিমাণ') }}: {{ $item->quantity+0 }}</p></div>@if($product)<a class="ec-btn" href="{{ route('business.customer.product',['product'=>$product->id]) }}">{{ t('Choose product','পণ্য বেছে নিন') }}</a>@else<span>{{ t('Unavailable','অনুপলব্ধ') }}</span>@endif</div>@endforeach
@endsection
