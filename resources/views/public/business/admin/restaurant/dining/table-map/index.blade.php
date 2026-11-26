@extends('public.business.admin.layout')
@section('content')
@php
    $key=request()->route('business');
    $activeOrders=collect($openOrders);
    $occupiedIds=$activeOrders->pluck('dining_table_id')->filter()->unique();
    $reservedNow=collect($reservations)->filter(fn($reservation)=>$reservation->status==='confirmed' && $reservation->starts_at?->lte(now()) && $reservation->ends_at?->gt(now()))->pluck('dining_table_id')->unique();
    $unavailableIds=$occupiedIds->merge($reservedNow)->unique();
@endphp
<div class="ret-page restaurant-workspace">
@include('public.business.admin.retail._styles')
@include('public.business.admin.restaurant.dining.table-map.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div class="rst-inline"><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle aria-label="{{ t('Open navigation','মেনু খুলুন') }}">☰</button><div><h1>{{ t('Table map','টেবিল ম্যাপ') }}</h1><p>{{ t('Manage seating and continue open checks.','আসন পরিচালনা করুন ও চলমান বিল খুলুন।') }}</p></div></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.restaurant.dining.reservations.index',['business'=>$key]) }}">{{ t('Reservations','রিজার্ভেশন') }}</a><a class="ret-btn" href="{{ route('business.admin.sales.restaurant-pos.index',['business'=>$key]) }}">+ {{ t('New order','নতুন অর্ডার') }}</a></div></div>
<div class="ret-kpis" style="margin-bottom:20px"><div class="ret-kpi"><span class="rst-kpi-label">{{ t('Total tables','মোট টেবিল') }}</span><strong>{{ $tables->count() }}</strong></div><div class="ret-kpi"><span class="rst-kpi-label">{{ t('Occupied tables','ব্যবহৃত টেবিল') }}</span><strong>{{ $occupiedIds->count() }}</strong></div><div class="ret-kpi"><span class="rst-kpi-label">{{ t('Available tables','খালি টেবিল') }}</span><strong>{{ max(0,$tables->count()-$unavailableIds->count()) }}</strong></div><div class="ret-kpi"><span class="rst-kpi-label">{{ t('Open checks','চলমান বিল') }}</span><strong>{{ $activeOrders->count() }}</strong></div></div>
@forelse($tables->groupBy(fn($table)=>$table->diningArea ? t($table->diningArea->name,$table->diningArea->name_bn) : t('Dining area','ডাইনিং এরিয়া')) as $areaName=>$areaTables)
<section style="margin-bottom:24px"><h2 style="font-size:18px;margin-bottom:12px">{{ $areaName }}</h2><div class="rst-grid">
@foreach($areaTables as $table)
@php
    $check=$activeOrders->firstWhere('dining_table_id',$table->id);
    $reserved=collect($reservations)->first(fn($reservation)=>(int)$reservation->dining_table_id===(int)$table->id && in_array($reservation->status,['confirmed','seated'],true));
    $isCurrentReservation=$reserved && $reserved->starts_at?->lte(now()) && $reserved->ends_at?->gt(now());
@endphp
<article class="rst-table-card {{ $check?'occupied':($isCurrentReservation?'reserved':'available') }}"><div class="rst-inline between"><h2>{{ t($table->name,$table->name_bn) }}</h2><span class="rst-badge" data-status="{{ $check?'occupied':($isCurrentReservation?'reserved':'available') }}">{{ $check?t('Occupied','ব্যবহৃত'):($isCurrentReservation?t('Reserved','রিজার্ভ আছে'):t('Available','খালি')) }}</span></div><p class="rst-muted">{{ t('Seats','আসন') }}: {{ $table->capacity ?? $table->seats ?? '—' }}</p>
@if($check)<div><strong>{{ $check->order_no }}</strong><p class="rst-muted">{{ $check->customer_name ?: t('Walk-in guest','সাধারণ অতিথি') }} · {{ t('Guests','অতিথি') }}: {{ $check->guest_count }}</p><strong>৳{{ number_format((float)$check->total,2) }}</strong><p class="rst-muted">{{ $check->status==='confirmed'?t('Billed · service in progress','বিল হয়েছে · সার্ভিস চলছে'):t('Open order','চলমান অর্ডার') }}</p></div>
@elseif($reserved)<div class="rst-note"><strong>{{ $isCurrentReservation?t('Reserved now','এখন রিজার্ভ আছে'):t('Upcoming reservation','পরবর্তী রিজার্ভেশন') }}</strong><br>{{ $reserved->customer_name }} · {{ $reserved->starts_at?->format('h:i A') }}–{{ $reserved->ends_at?->format('h:i A') }}</div>
@else<p class="rst-muted">{{ t('Ready to seat your guests.','অতিথিদের বসানোর জন্য প্রস্তুত।') }}</p>@endif
<div class="ret-actions"><a class="ret-btn {{ $check?'':'alt' }}" href="{{ route('business.admin.sales.restaurant-pos.index',['business'=>$key]+($check?['order'=>$check->id]:($isCurrentReservation?['reservation'=>$reserved->id]:['table'=>$table->id]))) }}">{{ $check?t('Open check','বিল খুলুন'):($isCurrentReservation?t('Seat reservation','রিজার্ভেশন বসান'):t('Start order','অর্ডার শুরু করুন')) }}</a></div></article>
@endforeach</div></section>
@empty<div class="ret-card rst-empty">{{ t('No dining tables have been configured. Add dining areas and tables in Configurations to start dine-in orders.','ডাইনিং টেবিল তৈরি করা হয়নি। ডাইন-ইন অর্ডার শুরু করতে কনফিগারেশনে ডাইনিং এরিয়া ও টেবিল যোগ করুন।') }}</div>@endforelse
@php $offTable=$activeOrders->filter(fn($check)=>!$check->dining_table_id); @endphp
@if($offTable->isNotEmpty())<section class="ret-card"><h2>{{ t('Takeaway & delivery checks','টেকঅ্যাওয়ে ও ডেলিভারির বিল') }}</h2><div class="ret-table-wrap"><table class="ret-table"><thead><tr><th>{{ t('Order','অর্ডার') }}</th><th>{{ t('Guest','অতিথি') }}</th><th>{{ t('Type','ধরন') }}</th><th>{{ t('Total','মোট') }}</th><th>{{ t('Action','কার্যক্রম') }}</th></tr></thead><tbody>@foreach($offTable as $check)<tr><td>{{ $check->order_no }}</td><td>{{ $check->customer_name ?: '—' }}</td><td>{{ $check->service_type==='delivery'?t('Delivery','ডেলিভারি'):t('Takeaway','টেকঅ্যাওয়ে') }}</td><td>৳{{ number_format((float)$check->total,2) }}</td><td><a class="ret-btn alt" href="{{ route('business.admin.sales.restaurant-pos.index',['business'=>$key,'order'=>$check->id]) }}">{{ t('Open','খুলুন') }}</a></td></tr>@endforeach</tbody></table></div></section>@endif
</main></div>
@endsection
