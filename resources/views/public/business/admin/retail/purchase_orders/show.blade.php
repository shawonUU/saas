@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Purchase Order Details","ক্রয় আদেশ বিস্তারিত") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><h2>{{ $po->po_no }} — {{ $po->status }}</h2><p>{{ t('Total','মোট') }}: {{ number_format($po->total,2) }}</p>
@if($po->status==='draft')<form method="POST" action="{{ route('business.admin.purchase.purchase-orders.po-list.approve',['record'=>$po->id]) }}">@csrf<button class="ret-btn">{{ t('Approve PO','ক্রয় অনুমোদন') }}</button></form>@endif</div>
<form class="ret-card" method="POST" action="{{ route('business.admin.purchase.purchase-orders.po-list.receive',['record'=>$po->id]) }}">@csrf<h3>{{ t('Receive against PO','ক্রয় আদেশ অনুযায়ী পণ্য গ্রহণ') }}</h3><div class="ret-table-wrap"><table class="ret-table"><thead><tr><th>Variant ID</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>{{ t('Receive Now','এখন গ্রহণ') }}</th></tr></thead><tbody>
@foreach($po->items as $i)<tr><td>{{ $i->variant_id }}</td><td>{{ $i->quantity }}</td><td>{{ $i->received_quantity }}</td><td>{{ max(0,$i->quantity-$i->received_quantity) }}</td><td><input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $i->id }}"><input type="number" class="ret-input" name="items[{{ $loop->index }}][quantity]" value="0" step=".001" min="0" max="{{ max(0,$i->quantity-$i->received_quantity) }}"></td></tr>@endforeach</tbody></table></div>
@if(in_array($po->status,['approved','partial']))<div class="ret-grid" style="margin-top:12px"><div class="ret-field"><label>{{ t('Supplier Payment Now','সরবরাহকারীকে প্রদান') }}</label><input class="ret-input" type="number" name="pay_amount" min="0" step=".01" value="0"></div><div class="ret-field"><label>{{ t('Method','মাধ্যম') }}</label><select class="ret-input" name="payment_method"><option value="cash">Cash</option><option value="bank">Bank</option></select></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Receive & Post','গ্রহণ ও পোস্ট') }}</button>@endif</form>
<div class="ret-card"><h3>{{ t('Receipt History','গ্রহণের ইতিহাস') }}</h3>@foreach($po->receipts as $receipt)<p>GRN #{{ $receipt->purchase_id }} · {{ $receipt->created_at }}</p>@endforeach</div>
</main></div>
@endsection
