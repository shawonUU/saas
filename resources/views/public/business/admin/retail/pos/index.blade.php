@extends('public.business.admin.layout')
@section('content')
@php
$brand = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
$catalog = $variants->map(function($v) use($brand,$stocks){
    return [
        'id'=>$v->id,
        'name'=>$v->product->name,
        'sku'=>$v->sku,
        'productSku'=>$v->product->sku,'barcode'=>$v->barcode,'productBarcode'=>$v->product->barcode,
        'category'=>(string)$v->product->category_id,
        'variant'=>collect([$v->size?->name,$v->color?->name])->filter()->implode(' · '),
        'price'=>(float)$v->price,
        'stock'=>$v->product->track_stock?max(0,min((float)$v->stock,(float)($stocks[$v->id]??0))):null,
        'image'=>$brand->imageUrl($v->image_path?:$v->product->image_url)
    ];
})->values();
@endphp

<div class="ret-page pos-page pos-fullscreen" id="pos-app">
@include('public.business.admin.retail._styles')
@include('public.business.admin.retail.pos.styles')



<main class="workspace-content pos-workspace">

    @if($errors->any())
        <div class="ret-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div id="pos-alert" class="pos-alert" role="status" aria-live="polite" hidden></div>

    <form id="pos-sale" method="POST" action="{{ route('business.admin.sales.retail-pos.store',['business'=>request()->route('business')]) }}">
        @csrf
        <input type="hidden" name="request_key" value="{{ old('request_key',(string)\Illuminate\Support\Str::uuid()) }}">

        <div class="pos-shell">

            {{-- Product Catalog --}}
            <section class="pos-catalog pos-panel" aria-label="{{ t('Product catalog','পণ্যের তালিকা') }}">
                <div class="pos-section-head">
                    <div>
                        <h2>{{ t('Product catalog','পণ্যের তালিকা') }}</h2>
                        <span id="pos-result" class="pos-muted"></span>
                    </div>
                    <button type="button" id="pos-theme" class="pos-secondary pos-icon" aria-label="{{ t('Toggle theme','থিম পরিবর্তন') }}" title="{{ t('Toggle theme','থিম পরিবর্তন') }}">◐</button>
                </div>

                {{-- Search --}}
                <div class="pos-search">
                    <span aria-hidden="true">⌕</span>
                    <input id="pos-search" type="search" placeholder="{{ t('Search products, SKU or scan barcode…','পণ্য, SKU বা বারকোড স্ক্যান করুন…') }}" autocomplete="off">
                    <kbd>F2</kbd>
                </div>

                {{-- Categories --}}
                <div class="pos-filter" id="pos-categories">
                    <button type="button" class="pos-chip active" data-category="">
                        {{ t('All products','সব পণ্য') }}
                    </button>
                    @foreach($categories as $id=>$name)
                        <button type="button" class="pos-chip" data-category="{{ $id }}">
                            {{ $name }}
                        </button>
                    @endforeach
                </div>

                {{-- Filter & Sorting --}}
                <div class="pos-filter pos-options">
                    <label>
                        <input type="checkbox" id="pos-stock-only">
                        {{ t('In stock only','শুধু স্টকে আছে') }}
                    </label>
                    <select id="pos-sort" aria-label="{{ t('Sort products','পণ্য সাজান') }}">
                        <option value="name">{{ t('Name A–Z','নাম অনুসারে') }}</option>
                        <option value="low">{{ t('Price: low to high','দাম: কম থেকে বেশি') }}</option>
                        <option value="high">{{ t('Price: high to low','দাম: বেশি থেকে কম') }}</option>
                    </select>
                </div>

                {{-- Product Grid --}}
                <div class="pos-product-scroll" id="pos-product-scroll"><div class="pos-products" id="pos-products"></div>

                <button type="button" id="pos-more" class="pos-secondary pos-more" hidden>
                    {{ t('Show more products','আরও পণ্য দেখুন') }}
                </button></div>
            </section>

            {{-- Cart --}}
            <aside class="pos-cart pos-panel">
                <div class="pos-section-head">
                    <div>
                        <h2>
                            {{ t('Current sale','বর্তমান বিক্রয়') }}
                            <span id="pos-count" class="pos-count">0</span>
                        </h2>
                        <p class="pos-muted">
                            {{ t('Select items to start a new sale','বিক্রয় শুরু করতে পণ্য নির্বাচন করুন') }}
                        </p>
                    </div>
                    <button class="pos-text" id="pos-clear" type="button">
                        {{ t('Clear','মুছুন') }}
                    </button>
                </div>

                {{-- Customer --}}
                <div class="pos-customer">
                    <label for="pos-customer">{{ t('Customer','কাস্টমার') }}</label>
                    <select name="customer_id" id="pos-customer">
                        <option value="">{{ t('Walk-in customer','সাধারণ কাস্টমার') }}</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>
                                {{ $c->name }}{{ $c->phone?' · '.$c->phone:'' }}
                            </option>
                        @endforeach
                    </select>
                    <input name="customer_name" id="pos-guest" value="{{ old('customer_name') }}" maxlength="255" placeholder="{{ t('Guest name (optional)','কাস্টমারের নাম (ঐচ্ছিক)') }}">
                </div>

                {{-- Cart Items --}}
                <div id="pos-cart" class="pos-cart-lines"></div>

                {{-- Hold & Resume --}}
                <div class="pos-actions">
                    <button type="button" id="pos-hold" class="pos-secondary">
                        ▣ {{ t('Hold cart','কার্ট হোল্ড') }}
                    </button>
                    <button type="button" id="pos-resume" class="pos-secondary">
                        ↻ {{ t('Resume cart','কার্ট ফেরত আনুন') }}
                    </button>
                </div>

                {{-- Discount, Coupon & Tax --}}
                <div class="pos-adjust">
                    <label>
                        {{ t('Discount (৳)','ছাড় (৳)') }}
                        <input type="number" min="0" step=".01" name="discount" value="{{ old('discount',0) }}">
                    </label>
                    <label>
                        {{ t('Coupon','কুপন') }}
                        <input name="coupon_code" value="{{ old('coupon_code') }}" maxlength="50" placeholder="{{ t('Code','কোড') }}">
                    </label>
                    <label class="pos-tax">
                        {{ t('Tax / VAT','কর / ভ্যাট') }}
                        <select name="tax_id">
                            <option value="">{{ t('No tax','কর নেই') }}</option>
                            @foreach($taxes as $tax)
                                <option value="{{ $tax->id }}"
                                    data-rate="{{ $tax->rate }}"
                                    data-inclusive="{{ $tax->calculation_type==='inclusive'?'1':'0' }}"
                                    @selected(old('tax_id')==$tax->id)>
                                    {{ $tax->name }} · {{ $tax->rate }}%
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                {{-- Sale Summary --}}
                <div class="pos-totals">
                    <div>
                        <span>{{ t('Subtotal','সাবটোটাল') }}</span>
                        <strong id="pos-subtotal">৳0.00</strong>
                    </div>
                    <div>
                        <span>{{ t('Discount','ছাড়') }}</span>
                        <strong id="pos-discount">৳0.00</strong>
                    </div>
                    <div>
                        <span>{{ t('Tax / VAT','কর / ভ্যাট') }}</span>
                        <strong id="pos-tax-total">৳0.00</strong>
                    </div>
                    <div class="pos-grand">
                        <span>{{ t('Estimated total','আনুমানিক মোট') }}</span>
                        <strong id="pos-total">৳0.00</strong>
                    </div>
                    <small id="pos-estimate">
                        {{ t('Final amount verified at checkout.','চেকআউটে চূড়ান্ত মূল্য যাচাই হবে।') }}
                    </small>
                </div>

                {{-- Checkout --}}
                <button id="pos-checkout" type="button" class="pos-primary" disabled>
                    {{ t('Checkout','চেকআউট') }}
                    <span>→ F8</span>
                </button>
            </aside>
        </div>

        {{-- Payment Dialog --}}
        <dialog id="pos-dialog" class="pos-dialog">
            <div class="pos-section-head">
                <div>
                    <div class="pos-eyebrow">{{ t('FINAL CHECKOUT','চূড়ান্ত চেকআউট') }}</div>
                    <h2>{{ t('Receive payment','পেমেন্ট গ্রহণ') }}</h2>
                </div>
                <button type="button" id="pos-close" class="pos-secondary" aria-label="{{ t('Close','বন্ধ') }}">×</button>
            </div>

            {{-- Payable Amount --}}
            <div class="pos-payment-total">
                <span>{{ t('Amount payable','পরিশোধযোগ্য') }}</span>
                <strong id="pos-pay-total">৳0.00</strong>
            </div>

            <div class="pos-totals">
                <div>
                    <span>{{ t('Discount','ছাড়') }}</span>
                    <strong id="pos-pay-discount"></strong>
                </div>
                <div>
                    <span>{{ t('Tax / VAT','কর / ভ্যাট') }}</span>
                    <strong id="pos-pay-tax"></strong>
                </div>
            </div>

            <input type="hidden" name="expected_total" value="0">

            {{-- Payment Fields --}}
            <div class="pos-payment-fields">
                <label>
                    {{ t('Payment type','পেমেন্ট ধরন') }}
                    <select name="payment_mode">
                        <option value="full">{{ t('Full payment','সম্পূর্ণ পেমেন্ট') }}</option>
                        <option value="partial">{{ t('Partial payment','আংশিক পেমেন্ট') }}</option>
                        <option value="due">{{ t('Pay later','বাকিতে বিক্রয়') }}</option>
                    </select>
                </label>
                <label>
                    {{ t('Method','মাধ্যম') }}
                    <select name="payment_method">
                        <option value="cash">{{ t('Cash','নগদ') }}</option>
                        <option value="bank">{{ t('Bank','ব্যাংক') }}</option>
                    </select>
                </label>
                <label>
                    {{ t('Amount received','গৃহীত টাকা') }}
                    <input name="tendered" type="number" min="0" step=".01" value="0">
                </label>
                <label>
                    {{ t('Payment reference','পেমেন্ট রেফারেন্স') }}
                    <input name="payment_reference" maxlength="255">
                </label>
            </div>

            {{-- Quick Cash --}}
            <div class="pos-actions" id="pos-quick">
                <button type="button" class="pos-secondary" data-cash="exact">
                    {{ t('Exact','সঠিক পরিমাণ') }}
                </button>
                @foreach([100,500,1000,2000] as $amount)
                    <button type="button" class="pos-secondary" data-cash="{{ $amount }}">
                        ৳{{ $amount }}
                    </button>
                @endforeach
            </div>

            {{-- Payment Summary --}}
            <div class="pos-totals">
                <div>
                    <span>{{ t('Change','ফেরত টাকা') }}</span>
                    <strong id="pos-change">৳0.00</strong>
                </div>
                <div>
                    <span>{{ t('Remaining due','অবশিষ্ট বাকি') }}</span>
                    <strong id="pos-due">৳0.00</strong>
                </div>
            </div>

            <p id="pos-pay-message" role="status" class="pos-muted"></p>

            <button type="submit" id="pos-submit" class="pos-primary">
                {{ t('Confirm sale','বিক্রয় নিশ্চিত করুন') }}
            </button>
        </dialog>
    </form>
<dialog id="pos-success" class="pos-dialog pos-success-dialog" aria-labelledby="pos-success-title">
    <div class="pos-section-head"><h2 id="pos-success-title">{{ t('Sale completed','বিক্রয় সম্পন্ন') }}</h2><button type="button" class="pos-secondary" id="pos-success-close">×</button></div>
    <div id="pos-receipt-preview" class="pos-receipt-preview"></div>
    <div class="pos-actions"><button type="button" class="pos-secondary" id="pos-print">{{ t('Print receipt','রসিদ প্রিন্ট') }}</button><button type="button" class="pos-primary" id="pos-next">{{ t('New sale','নতুন বিক্রয়') }}</button></div>
</dialog>
</main>
</div>

@include('public.business.admin.retail.pos.script')
@endsection