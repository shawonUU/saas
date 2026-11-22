@extends('public.business.public.ecommerce.layout')
@section('title',t('Order received','অর্ডার গৃহীত'))
@section('content')
<section style="max-width:580px;margin:75px auto;background:var(--sf-card);border:1px solid var(--sf-line);padding:48px 30px;border-radius:20px;text-align:center"><div style="color:#1c9b61;font-size:50px">✓</div><h1>{{ t('Order received!','অর্ডার গ্রহণ করা হয়েছে!') }}</h1><p>{{ t('Order number','অর্ডার নম্বর') }}: <b>{{ $orderNo }}</b></p><p style="color:var(--sf-muted);line-height:1.7">{{ t('The store will contact you about delivery and payment.','ডেলিভারি ও পেমেন্টের বিষয়ে দোকান থেকে যোগাযোগ করা হবে।') }}</p><a class="sf-btn" style="margin-top:15px" href="{{ route('business.customer.shop') }}">{{ t('Continue Shopping','আরও কেনাকাটা করুন') }}</a></section>
@endsection
@push('scripts')
<script>
try{
 const bid='{{ $business->id }}',key='nexabiz:checkout:pending:'+bid,pending=JSON.parse(sessionStorage.getItem(key)||'null');
 if(Array.isArray(pending)&&pending.length){const cartKey='nexabiz:cart:'+bid,cart=JSON.parse(localStorage.getItem(cartKey)||'[]');
  const updated=cart.map(item=>{const line=pending.find(p=>String(p.id)===String(item.id));return line?{...item,qty:Math.max(0,Number(item.qty)-Number(line.qty))}:item}).filter(item=>item.qty>0);
  localStorage.setItem(cartKey,JSON.stringify(updated));sessionStorage.removeItem(key);
 }
}catch(e){}
</script>
@endpush
