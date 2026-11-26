@extends('public.business.public.ecommerce.layout')
@section('title',$item->name)
@php
$detailBrand=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
$selectedVariant=$item->variants->first(fn($v)=>!$item->track_stock||(float)$v->stock>0)??$item->variants->first();
$detailPhoto=$detailBrand->imageUrl($selectedVariant?->image_path?:$item->image_url);
$detailPrice=(float)($selectedVariant?->price??$item->price);
$detailBefore=(float)($item->compare_at_price??0);
$detailOff=$detailBefore>$detailPrice&&$detailPrice>0?round((1-$detailPrice/$detailBefore)*100):0;
$gallery=collect([$item->image_url])->merge($item->variants->pluck('image_path'))->filter()->unique()->map(fn($path)=>$detailBrand->imageUrl($path))->values();
@endphp
@section('content')
<div class="sf-page-heading sf-page-heading-small"><a href="{{ route('business.customer.shop') }}" class="sf-back">‹</a><h1>{{ t('Product Details','পণ্যের বিস্তারিত') }}</h1></div>
<div class="sf-details-title">{{ $item->name }}</div>
<div class="sf-detail-cols">
 <div class="sf-gallery"><div class="sf-gallery-thumbs">@foreach($gallery->take(12) as $photo)<button class="sf-gallery-thumb" data-gallery-src="{{ $photo }}" type="button"><img src="{{ $photo }}" alt="{{ $item->name }}"></button>@endforeach</div><div class="sf-main-art">@if($detailPhoto)<img id="sf-detail-image" src="{{ $detailPhoto }}" alt="{{ $item->name }}">@else<div class="sf-placeholder">▧</div>@endif @if($detailOff)<span class="sf-sale-badge sf-detail-sale" id="sf-pdp-discount">-{{ $detailOff }}%</span>@endif</div></div>
 <section class="sf-pdp-order"><div class="sf-pdp-prices"><strong id="sf-detail-price">৳ {{ number_format($detailPrice,2) }}</strong>@if($detailOff)<del id="sf-before-price">৳{{ number_format($detailBefore,2) }}</del><small id="sf-saving-badge">-{{ $detailOff }}%</small>@else<del id="sf-before-price" hidden></del><small id="sf-saving-badge" hidden></small>@endif</div><p class="sf-detail-sku">{{ t('SKU','SKU') }}: {{ $item->sku }}</p><h2>{{ t('Color, Size & Variants','রং, সাইজ ও ভ্যারিয়েন্ট') }}</h2>
 @php
 $colorGroups=$item->variants->groupBy(fn($v)=>$v->color_id??0);
 $hasColor=$item->variants->contains(fn($v)=>$v->color_id!==null);
 $hasSize=$item->variants->contains(fn($v)=>$v->size_id!==null);
 $initialColor=$selectedVariant?->color_id??0;
 @endphp
 @if($hasColor)
 <div class="pdp-color-label">{{ t('Color','রং') }}: <strong id="pdp-color-name">{{ $selectedVariant?->color?->name??t('No color','রং ছাড়া') }}</strong></div>
 <div class="pdp-colors" role="group" aria-label="{{ t('Color','রং') }}">
 @foreach($colorGroups as $colorId=>$variants)
 @php $colorVariant=$variants->first(fn($v)=>$v->image_path)??$variants->first(); $colorName=$colorVariant->color?->name??t('No color','রং ছাড়া'); @endphp
 <button type="button" class="pdp-color @if($colorId==$initialColor) active @endif" data-color="{{ $colorId }}" data-label="{{ $colorName }}" aria-pressed="{{ $colorId==$initialColor?'true':'false' }}" title="{{ $colorName }}">@if($colorVariant->image_path||$item->image_url)<img src="{{ $detailBrand->imageUrl($colorVariant->image_path?:$item->image_url) }}" alt="{{ $colorName }}">@else<span>{{ $colorName }}</span>@endif</button>
 @endforeach
 </div>
 @endif
 <div class="pdp-size-list">
 @foreach($colorGroups as $colorId=>$variants)
 @foreach($variants->sortBy(fn($v)=>$v->size?->name??'',SORT_NATURAL) as $variant)
 @php $available=!$item->track_stock||(float)$variant->stock>0; @endphp
 <div class="pdp-size-row" data-color-row="{{ $colorId }}" @if($colorId!=$initialColor) hidden @endif>
 <div class="pdp-size-name">{{ $variant->size?->name??($hasSize?t('No size','সাইজ ছাড়া'):t('Standard','সাধারণ')) }}@if($variants->count()>1 && $variants->where('size_id',$variant->size_id)->count()>1)<small>{{ $variant->sku }}</small>@endif</div>
 <div class="pdp-row-price"><strong>৳ {{ number_format((float)$variant->price,2) }}</strong>@if($detailBefore>$variant->price)<del>৳ {{ number_format($detailBefore,2) }}</del>@endif</div>
 <div class="pdp-row-action"><button type="button" class="pdp-row-add" data-row-variant="{{ $variant->id }}" @disabled(!$available)>🛒 {{ t('Add','যোগ করুন') }}</button><small>{{ !$item->track_stock?t('Available','স্টকে আছে'):($available?t('Stock','স্টক').' '.number_format((float)$variant->stock,3,'.',''):t('Out of stock','স্টক নেই')) }}</small></div>
 </div>
 @endforeach
 @endforeach
 </div>
 <label class="sf-detail-select" style="display:none">{{ t('Selected Variant','নির্বাচিত ভ্যারিয়েন্ট') }}<select id="sf-detail-variant" data-compare="{{ $detailBefore }}">@foreach($item->variants as $variant)<option value="{{ $variant->id }}" data-stock="{{ $item->track_stock?$variant->stock:99999 }}" data-price="{{ $variant->price }}" data-image="{{ $detailBrand->imageUrl($variant->image_path?:$item->image_url) }}" @selected($selectedVariant?->id===$variant->id) @disabled($item->track_stock && $variant->stock<=0)>{{ collect([$variant->size?->name,$variant->color?->name,$variant->sku])->filter()->implode(' · ') }} · ৳{{ number_format($variant->price,2) }}</option>@endforeach</select></label>
 <div class="sf-pdp-stock" id="sf-detail-stock" style="display:none">{{ t('Choose a variant to see availability','স্টক দেখতে ভ্যারিয়েন্ট বেছে নিন') }}</div><div class="sf-detail-buttons"><button class="sf-btn sf-pdp-add" id="sf-detail-add" style="display:none" data-detail-add data-product="{{ $item->id }}" data-name="{{ $item->name }}" type="button">{{ t('Add to Cart','কার্টে যোগ করুন') }}</button><button class="sf-btn sf-pdp-buy" type="button" data-buy-now style="display:none">{{ t('Buy Now','এখনই কিনুন') }}</button><button class="sf-pdp-wish" type="button" data-wish-toggle data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $detailPrice }}" data-url="{{ route('business.customer.product',['product'=>$item->id]) }}" data-image="{{ $detailPhoto }}" aria-label="Wishlist">♡</button></div>
 </section>
 <aside class="sf-pdp-recommend"><h3>{{ t('Suggestions','আরও পণ্য') }}</h3>@foreach($related->take(12) as $recommendation)<a href="{{ route('business.customer.product',['product'=>$recommendation->id]) }}"><span>@php $rPhoto=$detailBrand->imageUrl($recommendation->image_url?:$recommendation->variants->first()?->image_path); @endphp @if($rPhoto)<img src="{{ $rPhoto }}" alt="">@else  a @endif</span><div><small>{{ $recommendation->name }}</small><strong>৳{{ number_format((float)($recommendation->variants->first()?->price??$recommendation->price),2) }}</strong></div></a>@endforeach</aside>
</div>
<section class="sf-details-tabs"><h3>{{ t('Specifications','বিস্তারিত তথ্য') }}</h3><div class="sf-spec-row"><span>{{ t('Product','পণ্য') }}</span><strong>{{ $item->name }}</strong></div><div class="sf-spec-row"><span>SKU</span><strong>{{ $item->sku }}</strong></div><div class="sf-spec-row"><span>{{ t('Variants','ভ্যারিয়েন্ট') }}</span><strong>{{ $item->variants->count() }}</strong></div>@foreach((json_decode($item->specifications??'[]',true)?:[]) as $spec)@if(is_array($spec)&&!empty($spec['name'])&&!empty($spec['value']))<div class="sf-spec-row"><span>{{ $spec['name'] }}</span><strong>{{ $spec['value'] }}</strong></div>@endif @endforeach @if($item->description)<div class="sf-spec-row"><span>{{ t('Description','বিবরণ') }}</span><strong style="white-space:pre-wrap">{{ $item->description }}</strong></div>@endif</section>
@endsection

@push('styles')
<style>
.sf-detail-cols{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:12px;align-items:start}.sf-pdp-recommend{grid-column:1/-1}.sf-gallery,.sf-pdp-order{min-width:0}.pdp-color-label{margin:12px 0 8px;font-size:14px}.pdp-colors{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:14px}.pdp-color{position:relative;width:58px;height:58px;border:1px solid #d0d5db;border-radius:8px;background:var(--sf-card,#fff);padding:3px;cursor:pointer;overflow:hidden;color:#222}.pdp-color img{width:100%;height:100%;object-fit:contain}.pdp-color.active{border:2px solid #d6224e}.pdp-color.active:after{content:'✓';position:absolute;right:3px;bottom:3px;background:#d6224e;color:white;border-radius:50%;width:18px;height:18px;line-height:18px}.pdp-size-list{border:1px solid #80808040;border-radius:8px;overflow:auto;max-height:430px}.pdp-size-row{display:grid;grid-template-columns:minmax(55px,.7fr) minmax(85px,1fr) minmax(90px,1fr);align-items:center;gap:8px;padding:12px;border-bottom:1px solid #80808030}.pdp-size-row[hidden]{display:none!important}.pdp-size-row:nth-child(even){background:#80808008}.pdp-size-name{font-weight:600}.pdp-size-name small,.pdp-row-price del,.pdp-row-action small{display:block;font-size:11px;opacity:.75}.pdp-row-action{text-align:center}.pdp-row-add{border:0;border-radius:22px;background:#003b44;color:white;padding:7px 15px;font-weight:600;cursor:pointer}.pdp-row-add:disabled{opacity:.4;cursor:not-allowed}.pdp-row-price strong{font-size:14px}.pdp-row-action small{margin-top:5px}.sf-pdp-order h2{font-size:15px}@media(max-width:760px){.sf-detail-cols{grid-template-columns:minmax(0,1fr)}.pdp-size-list{max-height:none}}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const select=document.getElementById('sf-detail-variant'), add=document.getElementById('sf-detail-add');
 if(!select||!add)return;
 const rows=[...document.querySelectorAll('[data-color-row]')], colors=[...document.querySelectorAll('.pdp-color[data-color]')];
 const choose=id=>{select.value=id;select.dispatchEvent(new Event('change',{bubbles:true}));};
 colors.forEach(btn=>btn.addEventListener('click',()=>{
 colors.forEach(c=>{c.classList.toggle('active',c===btn);c.setAttribute('aria-pressed',String(c===btn));});
 document.getElementById('pdp-color-name').textContent=btn.dataset.label;
 rows.forEach(row=>row.hidden=row.dataset.colorRow!==btn.dataset.color);
 const visible=rows.filter(row=>!row.hidden).map(row=>row.querySelector('[data-row-variant]'));
 const first=visible.find(b=>!b.disabled)||visible[0];if(first)choose(first.dataset.rowVariant);
 }));
 rows.forEach(row=>row.querySelector('[data-row-variant]').addEventListener('click',e=>{
 const btn=e.currentTarget;if(btn.disabled)return;choose(btn.dataset.rowVariant);if(!add.disabled)add.click();
 }));
});
</script>
@endpush
