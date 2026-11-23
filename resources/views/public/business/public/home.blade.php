
@extends('public.business.public.ecommerce.layout')

@section('title', $business->name)

@php
    $homeImageService = app(
        \App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class
    );
@endphp

@section('content')

<style>
/* HOME PRODUCT GRID */
.sf-product-grid.sf-home-product-grid {
    display: grid !important;
    grid-template-columns: repeat(6,minmax(0,1fr)) !important;
    gap: 11px !important;
    align-items: stretch;
    width: 100%;
    min-width: 0;
}

.sf-home-product-grid > .sf-product {
    width: 100% !important;
    max-width: none !important;
    min-width: 0;
    height: 100%;
    margin: 0 !important;
}

.sf-home-product-grid > .sf-empty {
    grid-column: 1 / -1;
}

/* RESPONSIVE */
@media(max-width:1439px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(5,minmax(0,1fr)) !important;
    }
}

@media(max-width:1199px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(4,minmax(0,1fr)) !important;
    }
}

@media(max-width:991px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    }
}

@media(max-width:767px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
        gap:9px !important;
    }
}
</style>

{{-- PROMOTIONAL SECTION --}}
@include('public.business.public.ecommerce.partials.promo')

{{-- TOP CATEGORIES --}}
@if($categories->isNotEmpty())
<section class="sf-section sf-top-categories">

    <div class="sf-section-top">
        <h2>{{ t('Top Category','সেরা ক্যাটাগরি') }}</h2>

        <a href="{{ route('business.customer.shop') }}">
            {{ t('View all','সব দেখুন') }} ›
        </a>
    </div>

    <div class="sf-category-circles">

        @foreach($categories->take(15) as $category)

            @php
                $categoryProduct = $products->first(
                    fn($p) =>
                        (int)$p->category_id === (int)$category->id &&
                        (
                            $p->image_url ||
                            $p->variants->first()?->image_path
                        )
                );

                $categoryPhoto = $categoryProduct
                    ? $homeImageService->imageUrl(
                        $categoryProduct->image_url
                        ?: $categoryProduct->variants->first()?->image_path
                    )
                    : null;
            @endphp

            <a class="sf-category-circle"
               href="{{ route('business.customer.shop',[
                   'category'=>$category->id
               ]) }}">

                <span class="sf-circle-image">

                    @if($categoryPhoto)
                        <img
                            src="{{ $categoryPhoto }}"
                            alt="{{ $category->name }}"
                            loading="lazy"
                            onerror="this.style.display='none'">
                    @else
                        <span class="sf-circle-fallback">
                            {{ mb_substr($category->name,0,1) }}
                        </span>
                    @endif

                </span>

                <small>{{ $category->name }}</small>

            </a>

        @endforeach

    </div>
</section>
@endif

{{-- HOME CATEGORY-WISE PRODUCT SECTIONS --}}
@forelse(($homeSections ?? []) as $section)

    <section class="sf-section sf-department">

        <div class="sf-section-top">

            <h2>
                <span class="sf-heading-glyph">✿</span>
                {{ $section['category']->name }}
            </h2>

            <a href="{{ route('business.customer.shop',[
                'category'=>$section['category']->id
            ]) }}">
                {{ t('View More','আরও দেখুন') }}
                <span>›</span>
            </a>

        </div>

        <div class="sf-product-grid sf-home-grid sf-products-grid sf-home-product-grid">

            @foreach($section['products'] as $product)

                @include('public.business.public.ecommerce.partials.product-card')

            @endforeach

        </div>

    </section>

@empty

    {{-- LATEST PRODUCTS FALLBACK --}}
    <section class="sf-section">

        <div class="sf-section-top">

            <h2>
                {{ t('Latest Products','নতুন পণ্য') }}
            </h2>

            <a href="{{ route('business.customer.shop') }}">
                {{ t('View More','আরও দেখুন') }} ›
            </a>

        </div>

        <div class="sf-product-grid sf-home-grid sf-products-grid sf-home-product-grid">

            @forelse($products as $product)

                @include('public.business.public.ecommerce.partials.product-card')

            @empty

                <div class="sf-empty">
                    {{ t(
                        'No published products yet. Add active products and variants from Business Admin.',
                        'এখনও পণ্য প্রকাশ হয়নি। অ্যাডমিনে পণ্য যোগ করুন।'
                    ) }}
                </div>

            @endforelse

        </div>

    </section>

@endforelse

@endsection
