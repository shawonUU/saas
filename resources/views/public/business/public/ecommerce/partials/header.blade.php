<header class="sf-header"><div class="sf-headrow">
 <button type="button" class="sf-mobile-menu" data-sidebar-open aria-label="{{ t('Open categories','ক্যাটাগরি খুলুন') }}">☰</button>
 <a href="{{ route('business.customer.home') }}" class="sf-logo" aria-label="{{ $b->name }}">
  @if($storeLogo)<img src="{{ $storeLogo }}" alt="{{ $b->name }}">@else<span class="sf-logo-mark">{{ mb_substr($b->name,0,1) }}</span><span class="sf-logo-text">{{ $b->name }}</span>@endif
 </a>
 <form class="sf-search" action="{{ route('business.customer.shop') }}" method="GET"><input name="q" value="{{ request('q') }}" placeholder="{{ t('Search Product','পণ্য খুঁজুন') }}" aria-label="{{ t('Search Product','পণ্য খুঁজুন') }}"><button type="submit" title="{{ t('Search','খুঁজুন') }}" aria-label="{{ t('Search','খুঁজুন') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/></svg></button></form>
 <nav class="sf-actions" aria-label="{{ t('Shop navigation','শপ নেভিগেশন') }}">
  <form method="POST" action="{{ route('language.set') }}">@csrf<input type="hidden" name="lang" value="{{ session('portal_lang','en')==='bn'?'en':'bn' }}"><button class="sf-header-setting" title="{{ t('Language','ভাষা') }}">{{ session('portal_lang','en')==='bn'?'EN':'বাং' }}</button></form>
  <button type="button" class="sf-header-setting" data-theme-toggle title="{{ t('Light / Dark','লাইট / ডার্ক') }}">◐</button>
  <a class="sf-header-icon" href="{{ route('business.customer.cart') }}" title="{{ t('Cart','কার্ট') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h2l2.7 12h12.7l2.1-9H5"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><em data-cart-count>0</em></a>
  <a class="sf-header-icon" href="{{ route('business.customer.wishlist') }}" title="{{ t('Wishlist','উইশলিস্ট') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.5 8.8c0 5-8.5 10.5-8.5 10.5S3.5 13.8 3.5 8.8a4.8 4.8 0 0 1 8.5-3.1 4.8 4.8 0 0 1 8.5 3.1Z"/></svg><em data-wish-count>0</em></a>
  <a class="sf-header-icon" href="{{ \Illuminate\Support\Facades\Auth::guard('business_customer')->check() ? route('business.customer.dashboard') : route('business.customer.login') }}" title="{{ t('My Account','আমার অ্যাকাউন্ট') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></a>
 </nav>
</div></header>
