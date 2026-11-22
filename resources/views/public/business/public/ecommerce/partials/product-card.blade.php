@php
$variant=$product->variants->first(fn($v)=>!$product->track_stock||(float)$v->stock>0);
$price=(float)($variant?->price??$product->price);
$previous=(float)($product->compare_at_price??0);
$discount=($previous>$price&&$price>0)?round((($previous-$price)/$previous)*100):0;
$photo=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class)->imageUrl($variant?->image_path?:$product->image_url);
$sold=(int)($soldCounts[$product->id]??0);
$detailUrl=route('business.customer.product',['product'=>$product->id]);
@endphp
<article class="sf-product" data-product-card="{{ $product->id }}">
 <div class="sf-product-image-wrap"><a class="sf-product-image" href="{{ $detailUrl }}">@if($photo)<img src="{{ $photo }}" alt="{{ $product->name }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false"><span class="sf-placeholder" hidden>▧</span>@else<span class="sf-placeholder">▧</span>@endif</a>
 @if($discount>0)<span class="sf-sale-badge">-{{ $discount }}%</span>@endif
 <button class="sf-heart" type="button" title="{{ t('Wishlist','উইশলিস্ট') }}" aria-label="{{ t('Add to wishlist','উইশলিস্টে রাখুন') }}" data-wish-toggle data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $price }}" data-url="{{ $detailUrl }}" data-image="{{ $photo }}">♡</button></div>
 <div class="sf-product-content"><div class="sf-product-price"><strong>৳ {{ number_format($price,2) }}</strong>@if($discount)<del>৳{{ number_format($previous,2) }}</del>@endif</div>
 <a href="{{ $detailUrl }}" class="sf-product-title" title="{{ $product->name }}">{{ $product->name }}</a><div class="sf-product-meta"><span class="sf-product-stock">{{ $variant?t('In Stock','স্টকে আছে'):t('Out of Stock','স্টক নেই') }}</span>@if($sold>0)<small>{{ number_format($sold) }} {{ t('Sold','বিক্রি') }}</small>@endif</div>
 <div class="sf-product-progress"><i></i></div>
 @if($variant)<button type="button" class="sf-add sf-add-compact" data-add-to-cart data-id="{{ $variant->id }}" data-product="{{ $product->id }}" data-name="{{ $product->name }}" data-stock="{{ $product->track_stock?$variant->stock:99999 }}" data-price="{{ $price }}" data-before="{{ $discount?$previous:$price }}" data-image="{{ $photo }}">+ {{ t('Add to Cart','কার্টে যোগ') }}</button>@else<a class="sf-add sf-add-compact sf-disabled" href="{{ $detailUrl }}">{{ t('Details','বিস্তারিত') }}</a>@endif
 </div>
</article>
