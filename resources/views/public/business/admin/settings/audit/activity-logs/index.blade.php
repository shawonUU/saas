@extends('public.business.admin.layout')
@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.shared.list-ui.styles')
@endpush
@section('content')
<div class="workspace-layout">
 @include('public.business.admin.sidebar')
 <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
 <main class="workspace-content">
  <h1>{{ t('Activity Logs','কার্যক্রমের ইতিহাস') }}</h1>
  <p style="color:var(--muted)">{{ t('Every recorded create, update and delete. Sensitive credentials are never logged.','সংরক্ষিত তৈরি, পরিবর্তন ও মুছে ফেলার ইতিহাস। গোপন পাসওয়ার্ড/টোকেন সংরক্ষিত হয় না।') }}</p>
  <section class="cfg-panel">
   <form method="GET" style="padding:14px;display:flex;gap:9px;flex-wrap:wrap">
    <input class="cfg-input" name="table" value="{{ request('table') }}" placeholder="{{ t('Table name','টেবিল') }}" style="max-width:220px">
    <select class="cfg-input" name="action" style="max-width:160px"><option value="">{{ t('All actions','সব কার্যক্রম') }}</option>@foreach(['created','updated','deleted'] as $action)<option value="{{ $action }}" @selected(request('action')===$action)>{{ ucfirst($action) }}</option>@endforeach</select>
    <input class="cfg-input" name="business_date" type="date" value="{{ request('business_date') }}" style="max-width:180px">
    <button class="cfg-btn cfg-primary">{{ t('Filter','ফিল্টার') }}</button>
    <a class="cfg-btn" href="{{ route('business.admin.settings.audit.activity-logs.index',['business'=>request()->route('business')]) }}">{{ t('Reset','রিসেট') }}</a>
   </form>
   <div class="cfg-table-scroll"><table class="cfg-table"><thead><tr><th>{{ t('Time','সময়') }}</th><th>{{ t('Business date','বিজনেস তারিখ') }}</th><th>{{ t('Table / Record','টেবিল / রেকর্ড') }}</th><th>{{ t('Action','অ্যাকশন') }}</th><th>{{ t('Changed by','পরিবর্তন করেছেন') }}</th><th>{{ t('Details','বিস্তারিত') }}</th></tr></thead><tbody>
    @forelse($logs as $log)<tr data-audit-table="{{ $log->subject_table }}" data-audit-id="{{ $log->subject_id }}"><td>{{ $log->created_at }}</td><td>{{ $log->business_date ?? '—' }}</td><td>{{ $log->subject_table }} #{{ $log->subject_id }}</td><td>{{ ucfirst($log->action) }}</td><td>{{ $log->actor_name ?: ($log->actor_id ?: '—') }}</td><td><button type="button" class="cfg-btn" data-audit-open="1">{{ t('View Log','লগ দেখুন') }}</button></td></tr>
    @empty<tr><td colspan="6" class="cfg-empty">{{ t('No audit entries found.','লগ পাওয়া যায়নি।') }}</td></tr>@endforelse
   </tbody></table></div>
   <div style="padding:15px">{{ $logs->links() }}</div>
  </section>
 </main>
</div>
@endsection
