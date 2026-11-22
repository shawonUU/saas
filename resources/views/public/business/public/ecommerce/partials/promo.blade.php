@php
 $promoService=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
 $promoSettings=$promoService->find($business);
 $mainBanner=$promoService->imageUrl($promoSettings?->banner);
 $sideBanner=$promoService->imageUrl($promoSettings?->secondary_banner);
@endphp
<div class="sf-promos">
<a class="sf-promo-main {{ $mainBanner?'sf-promo-with-image':'' }}" href="{{ route('business.customer.shop') }}" @if($mainBanner) style="background-image:url('{{ $mainBanner }}')" @endif>
@if(!$mainBanner)<div class="sf-promo-copy"><small>{{ t('MEGA SHOPPING FEST','মেগা শপিং ফেস্ট') }}</small><h1>{{ t('Big deals, better shopping','দারুণ দামে সেরা কেনাকাটা') }}</h1><p>{{ $promoSettings?->tagline?:t('Find something special today','আজই পছন্দের পণ্য খুঁজুন') }}</p><span>{{ t('Shop all products','সব পণ্য দেখুন') }} ›</span></div><div class="sf-promo-illustration" aria-hidden="true"><i>✦</i><b>SALE</b><i>★</i></div>@endif
</a>
<a class="sf-promo-side {{ $sideBanner?'sf-promo-with-image':'' }}" href="{{ route('business.customer.shop',['sort'=>'latest']) }}" @if($sideBanner) style="background-image:url('{{ $sideBanner }}')" @endif>
@if(!$sideBanner)<small>{{ t('NEW ARRIVALS','নতুন কালেকশন') }}</small><h2>{{ t('Explore the latest','নতুন পণ্য দেখুন') }}</h2><span>{{ t('Shop now','কেনাকাটা করুন') }} ›</span><div aria-hidden="true" class="sf-promo-side-art">✳</div>@endif
</a>
</div>
