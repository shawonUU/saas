@extends('public.business.admin.layout')
@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.settings.partials.form-styles')
<style>
.bd-page{--bd-border:var(--border,#334155)}.bd-panel{background:var(--white,#1a263b);border:1px solid var(--bd-border);border-radius:16px;padding:22px;margin-bottom:17px;color:var(--text)}.bd-row{display:flex;gap:15px;flex-wrap:wrap}.bd-stat{flex:1;min-width:170px;background:var(--bg,#101b2c);border:1px solid var(--bd-border);border-radius:12px;padding:15px}.bd-stat small{color:var(--muted);display:block;margin-bottom:8px}.bd-stat strong{font-size:22px}.bd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}.bd-grid label{display:flex;flex-direction:column;gap:7px;font-size:13px;font-weight:650}.bd-grid input,.bd-grid select,.bd-panel textarea{width:100%;background:var(--bg);border:1px solid var(--bd-border);border-radius:9px;color:var(--text);padding:11px}.bd-action{border:0;border-radius:9px;background:var(--primary,#6251dc);color:white;padding:11px 18px;font-weight:700;cursor:pointer}.bd-action:disabled{opacity:.45;cursor:not-allowed}.bd-info{color:var(--muted);font-size:13px;line-height:1.6}.bd-warning{border:1px solid #d6923b;background:rgba(255,184,77,.12);border-radius:10px;padding:14px;color:var(--text)}.bd-table{width:100%;border-collapse:collapse}.bd-table th,.bd-table td{padding:12px;text-align:left;border-bottom:1px solid var(--bd-border)}@media(max-width:680px){.bd-panel{padding:15px}.bd-table{font-size:12px}}
</style>
@endpush
@section('content')
<div class="workspace-layout bd-page">
 @include('public.business.admin.sidebar')
 <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
 <main class="workspace-content">
  <h1>{{ t('Business Date & Day Close','বিজনেস তারিখ ও ডে ক্লোজ') }}</h1>
  <p class="bd-info">{{ t('A business date belongs to the day it opened. Closing late starts the new day on the actual close date.','বিজনেস তারিখ যেদিন খোলা হয়েছে সেটিই থাকবে। দেরিতে বন্ধ করলে বন্ধের প্রকৃত তারিখে নতুন দিন শুরু হবে।') }}</p>
  @if($errors->any())<div class="bd-warning">{{ $errors->first() }}</div>@endif
  <div class="bd-panel"><div class="bd-row"><div class="bd-stat"><small>{{ t('Current business date','বর্তমান বিজনেস তারিখ') }}</small><strong>{{ $dayStatus['business_date'] }}</strong></div><div class="bd-stat"><small>{{ t('Today (local calendar)','আজকের তারিখ') }}</small><strong>{{ $dayStatus['today'] }}</strong></div><div class="bd-stat"><small>{{ t('Mode','মোড') }}</small><strong>{{ $state->manual_mode ? t('Manual','ম্যানুয়াল') : t('Automatic','স্বয়ংক্রিয়') }}</strong></div></div></div>
  @if($dayStatus['alert'])<div class="bd-warning" role="alert">{{ $dayStatus['overdue'] ? t('Day close deadline has passed.','ডে ক্লোজের সময়সীমা পেরিয়েছে।') : t('Day close reminder: please complete your day close.','ডে ক্লোজের সতর্কতা: দিন বন্ধ করুন।') }} {{ t('Business date:','বিজনেস তারিখ:') }} {{ $dayStatus['business_date'] }}</div>@endif
  <div class="bd-panel"><h2>{{ t('Business Date Settings','বিজনেস ডেট সেটিংস') }}</h2>
   <form method="POST" action="{{ route('business.admin.settings.business.business-date.update',['business'=>request()->route('business')]) }}">@csrf @method('PUT')
    <div class="bd-grid">
     <label>{{ t('Use manual business date','ম্যানুয়াল বিজনেস তারিখ') }}<select name="manual_mode" @disabled(!$permissions['update'])><option value="0" @selected(!(bool)old('manual_mode',$state->manual_mode))>{{ t('Off — calendar date','বন্ধ — সাধারণ তারিখ') }}</option><option value="1" @selected((bool)old('manual_mode',$state->manual_mode))>{{ t('On — day close required','চালু — ডে ক্লোজ করতে হবে') }}</option></select></label>
     <label>{{ t('Business timezone','বিজনেস টাইমজোন') }}<select name="timezone" @disabled(!$permissions['update'])>@foreach(['Asia/Dhaka'=>'Asia/Dhaka','Asia/Kolkata'=>'Asia/Kolkata','Asia/Singapore'=>'Asia/Singapore','UTC'=>'UTC','Europe/London'=>'Europe/London','America/New_York'=>'America/New_York'] as $tz=>$label)<option value="{{ $tz }}" @selected(old('timezone',$state->timezone)===$tz)>{{ $label }}</option>@endforeach</select></label>
     <label>{{ t('Start close reminder (next day)','ডে ক্লোজ সতর্কতা শুরু (পরের দিন)') }}<input type="time" name="alert_start_time" value="{{ old('alert_start_time',substr((string)$state->alert_start_time,0,5)) }}" @disabled(!$permissions['update']) required></label>
     <label>{{ t('Close by (next day)','ডে ক্লোজ শেষ সময় (পরের দিন)') }}<input type="time" name="closing_deadline" value="{{ old('closing_deadline',substr((string)$state->closing_deadline,0,5)) }}" @disabled(!$permissions['update']) required></label>
    </div>
    <p class="bd-info">{{ t('Example: open 28 Nov; reminder 29 Nov 02:00, deadline 29 Nov 04:00. If closed 30 Nov, next business date is 30 Nov. Deadline triggers alerts; it never auto-closes manual mode.','উদাহরণ: ২৮ নভেম্বর খোলা; সতর্কতা ২৯ নভেম্বর রাত ২টায়, শেষ সময় ২৯ নভেম্বর ভোর ৪টায়। ৩০ নভেম্বর বন্ধ করলে নতুন তারিখ ৩০ নভেম্বর। সময় পেরোলে সতর্কতা আসবে, ম্যানুয়াল দিন নিজে থেকে বন্ধ হবে না।') }}</p>
    @if($permissions['update'])<button class="bd-action" type="submit">{{ t('Save Settings','সেটিংস সংরক্ষণ') }}</button>@endif
   </form></div>
  @if($state->manual_mode)<div class="bd-panel"><h2>{{ t('Close Current Business Day','বর্তমান বিজনেস দিন বন্ধ করুন') }}</h2>
   @if(!$dayStatus['can_close'])<p class="bd-warning">{{ t('Closing is only possible after the business date has ended (local midnight).','বিজনেস তারিখের দিন শেষ হওয়ার পর (মধ্যরাতের পরে) বন্ধ করা যাবে।') }}</p>@endif
   <form method="POST" action="{{ route('business.admin.settings.business.business-date.close',['business'=>request()->route('business')]) }}" onsubmit="return window.confirm(this.dataset.confirm)" data-confirm="{{ t('Close this business day? This cannot be undone.','এই বিজনেস দিন বন্ধ করবেন? এটি ফেরানো যাবে না।') }}" >@csrf
    <input type="hidden" name="confirm_business_date" value="{{ $dayStatus['business_date'] }}">
    <label class="gs-field">{{ t('Close note (optional)','বন্ধের নোট (ঐচ্ছিক)') }}<textarea name="notes" rows="2" maxlength="1000" placeholder="{{ t('Notes about this close','দিন বন্ধের বিবরণ') }}"></textarea></label>
    <button class="bd-action" type="submit" @disabled(!$dayStatus['can_close']||!$permissions['update'])>{{ t('Close Business Day','বিজনেস দিন বন্ধ করুন') }}</button>
   </form></div>@endif
  <div class="bd-panel"><h2>{{ t('Day Close History','ডে ক্লোজ ইতিহাস') }}</h2><div style="overflow-x:auto"><table class="bd-table"><thead><tr><th>{{ t('Closed business date','বন্ধ হওয়া তারিখ') }}</th><th>{{ t('Closed on','বন্ধের সময়') }}</th><th>{{ t('New business date','নতুন তারিখ') }}</th><th>{{ t('Closed by','বন্ধ করেছেন') }}</th><th>{{ t('Note','নোট') }}</th></tr></thead><tbody>@forelse($closures as $closure)<tr data-audit-table="business_day_closures" data-audit-id="{{ $closure->id }}"><td>{{ $closure->closed_business_date->toDateString() }}</td><td>{{ $closure->closed_at?->format('d/m/Y H:i') }}</td><td>{{ $closure->next_business_date->toDateString() }}</td><td>{{ $closure->closed_by_name ?: '—' }}</td><td>{{ $closure->notes ?: '—' }}</td></tr>@empty<tr><td colspan="5">{{ t('No days closed yet.','এখনো কোনো দিন বন্ধ হয়নি।') }}</td></tr>@endforelse</tbody></table></div>{{ $closures->withQueryString()->links() }}</div>
 </main>
</div>
@endsection
