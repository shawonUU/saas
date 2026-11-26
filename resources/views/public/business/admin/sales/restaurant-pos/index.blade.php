@extends('public.business.admin.sales.restaurant-pos.layout')
@section('content')
@php
    $key = request()->route('business');
    $prefix = 'business.admin.sales.restaurant-pos.';
    $branding = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $catalog = $variants->map(fn($variant) => [
        'id'=>$variant->id, 'product_id'=>$variant->product_id,
        'name'=>t($variant->product->name,$variant->product->name_bn),
        'variant'=>collect([t($variant->size?->name ?? '',$variant->size?->name_bn),t($variant->color?->name ?? '',$variant->color?->name_bn)])->filter()->implode(' · '),
        'sku'=>$variant->sku, 'category'=>(string)$variant->product->category_id,
        'price'=>(float)$variant->price, 'stock'=>$stocks[$variant->id] ?? null,
        'image'=>$branding->imageUrl($variant->image_path ?: $variant->product->image_url),
    ])->values();
    $routes = ['index'=>route($prefix.'index',['business'=>$key]),'store'=>route($prefix.'store',['business'=>$key]),'quote'=>route($prefix.'quote',['business'=>$key]),'tables'=>route($prefix.'tables',['business'=>$key])];
    foreach (['show','update','send','checkout','pay','close','cancel','serve','receipt','kot'] as $action) {
        $routes[$action] = route($prefix.$action,['business'=>$key,'order'=>'__ORDER__']);
    }
    $routes['void'] = route($prefix.'void',['business'=>$key,'order'=>'__ORDER__','item'=>'__ITEM__']);
@endphp
<main class="restaurant-workspace rpos-workspace" id="restaurant-pos">
    <div class="rpos-heading">
        <div class="rpos-heading-title">
            <span class="rpos-brand-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3v5a3 3 0 0 0 6 0V3M7 3v18M17 3c-2 3-3 6-3 10h4V3h-1Zm1 10v8"/></svg></span>
            <div><p class="rpos-eyebrow">{{ t($business->name,$business->name_bn) }}</p><h1>{{ t('Restaurant POS','রেস্টুরেন্ট পস') }}</h1></div>
        </div>
        <div class="rpos-utilities">
            <a class="ret-btn alt rpos-kitchen-link" href="{{ route('business.admin.restaurant.kitchen-display.kitchen-tickets.index',['business'=>$key]) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 14a8 8 0 0 1 16 0M3 17h18M5 20h14M12 3v3" stroke-linecap="round"/></svg><span>{{ t('Kitchen','কিচেন') }}</span></a>
            <button type="button" id="rpos-shortcuts" class="ret-btn alt rpos-shortcuts-button" title="{{ t('Keyboard shortcuts','কিবোর্ড শর্টকাট') }}" aria-label="{{ t('Keyboard shortcuts','কিবোর্ড শর্টকাট') }}"><span aria-hidden="true">⌨</span></button>
            <button type="button" id="theme-toggle" class="ret-btn alt rpos-theme" aria-label="{{ t('Toggle light and dark mode','লাইট ও ডার্ক মোড বদলান') }}" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20.5 13a8.5 8.5 0 1 1-9.5-9.5A6.5 6.5 0 0 0 20.5 13Z" stroke-linejoin="round"/></svg><span id="theme-label">{{ t('Dark','ডার্ক') }}</span></button>
            <form class="language-form" action="{{ url('/language') }}" method="POST">@csrf<input type="hidden" name="lang" value="{{ session('portal_lang','en') === 'en' ? 'bn' : 'en' }}"><button class="ret-btn alt" type="submit" aria-label="{{ t('Switch to Bangla','ইংরেজিতে দেখুন') }}">{{ session('portal_lang','en') === 'en' ? 'বাংলা' : 'EN' }}</button></form>
            <button type="button" class="ret-btn rpos-new-button" id="rpos-new" aria-keyshortcuts="F4" title="{{ t('New order','নতুন অর্ডার') }} · F4"><span aria-hidden="true">＋</span>{{ t('New order','নতুন অর্ডার') }}</button>
        </div>
    </div>
    @if(session('success'))<div class="rpos-alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rpos-alert error" role="alert">{{ $errors->first() }}</div>@endif
    <div id="rpos-alert" class="rpos-alert" role="status" aria-live="polite" hidden></div>
    <div id="rpos-recovery" class="rst-note rpos-recovery" hidden><span id="rpos-recovery-text"></span><div><button type="button" id="rpos-retry" class="ret-btn alt" hidden>{{ t('Retry safely','আবার চেষ্টা করুন') }}</button><button type="button" id="rpos-reload" class="ret-btn alt" hidden>{{ t('Load latest order','সর্বশেষ অর্ডার দেখুন') }}</button></div></div>
    <div id="rpos-last-sale" class="rpos-last-sale" role="status" hidden><div><strong id="rpos-last-sale-title"></strong><span id="rpos-last-sale-detail"></span></div><div class="rpos-last-sale-links"><a id="rpos-last-receipt" data-rpos-print="receipt" class="rpos-small-btn">{{ t('Reprint receipt','রসিদ আবার প্রিন্ট') }}</a><a id="rpos-last-kot" data-rpos-print="kot" class="rpos-small-btn">{{ t('Print KOT','কিচেন টিকিট প্রিন্ট') }}</a><button type="button" id="rpos-last-sale-dismiss" class="rpos-icon-button" aria-label="{{ t('Dismiss payment summary','পেমেন্টের সারাংশ সরান') }}">×</button></div></div>
    <div class="rpos-toolbar">
        <div class="rpos-service-tabs" id="rpos-service"><button type="button" data-service="takeaway">{{ t('Counter / Takeaway','কাউন্টার / টেকঅ্যাওয়ে') }}</button><button type="button" data-service="dine_in">{{ t('Dine-in','ডাইন-ইন') }}</button><button type="button" data-service="delivery">{{ t('Delivery','ডেলিভারি') }}</button></div>
        <div class="rpos-order-tools">
            <div id="rpos-table-field" hidden><button type="button" id="rpos-change-table" aria-keyshortcuts="F3" title="{{ t('Change table','টেবিল পরিবর্তন') }} · F3" class="ret-btn alt"><span aria-hidden="true">‹</span><strong id="rpos-selected-table">{{ t('Tables','টেবিল') }}</strong></button></div>
            <button type="button" id="rpos-continue-order" aria-keyshortcuts="F3" class="ret-btn alt" hidden>{{ t('Continue order','অর্ডারে ফিরুন') }} <span aria-hidden="true">→</span></button>
            <details class="rpos-held-orders" id="rpos-open-details"><summary id="rpos-open-picker">{{ t('Open orders','চলমান অর্ডার') }} <span id="rpos-open-count" class="rpos-count">0</span><span aria-hidden="true">⌄</span></summary><div class="rpos-open-popover"><div class="rpos-open-checks"><label class="rpos-sr-only" for="rpos-open">{{ t('Open orders','চলমান অর্ডার') }}</label><select id="rpos-open" class="ret-input" data-no-search><option value="">{{ t('Select an open order','চলমান অর্ডার নির্বাচন') }}</option></select><button type="button" class="ret-btn alt" id="rpos-open-load">{{ t('Open','খুলুন') }}</button></div></div></details>
        </div>
    </div>
    <div class="rpos-shell">
        <div class="rpos-main-column">
            <section class="rpos-panel rpos-floor" id="rpos-floor" aria-labelledby="rpos-tables-title">
                <div class="rpos-section-heading">
                    <div><div class="rpos-title-line"><h2 id="rpos-tables-title">{{ t('Select a table','টেবিল বেছে নিন') }}</h2><span class="rpos-live-dot" aria-hidden="true"></span></div><p id="rpos-table-summary" class="rst-muted">{{ t('Select a table to start or continue an order','অর্ডার শুরু করতে বা চলমান অর্ডার দেখতে টেবিল বেছে নিন') }}</p></div>
                    <div class="rpos-floor-actions"><span id="rpos-floor-sync" class="rst-muted" role="status" aria-live="polite"></span><button type="button" id="rpos-refresh-tables" class="rpos-icon-button" aria-label="{{ t('Refresh table status','টেবিলের অবস্থা হালনাগাদ করুন') }}" title="{{ t('Refresh table status','টেবিলের অবস্থা হালনাগাদ করুন') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M6.1 7a7 7 0 0 1 11.6-1L20 9M4 15l2.3 3A7 7 0 0 0 17.9 17"/></svg></button></div>
                </div>
                <div class="rpos-floor-filters">
                    <div class="rpos-area-tabs" id="rpos-table-areas" aria-label="{{ t('Dining areas','ডাইনিং এরিয়া') }}"><button type="button" class="active" data-area="" aria-pressed="true">{{ t('All areas','সব এরিয়া') }}</button>@foreach($areas as $area)<button type="button" data-area="{{ $area->id }}" aria-pressed="false">{{ t($area->name,$area->name_bn) }}</button>@endforeach</div>
                    <label class="rpos-search-box rpos-table-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4" stroke-linecap="round"/></svg><input type="search" id="rpos-table-search" aria-keyshortcuts="F2" placeholder="{{ t('Find a table…','টেবিল খুঁজুন…') }}" aria-label="{{ t('Search tables','টেবিল খুঁজুন') }}" autocomplete="off"></label>
                </div>
                <div class="rpos-table-grid" id="rpos-tables" aria-label="{{ t('Select a table','টেবিল নির্বাচন করুন') }}"></div>
                <div class="rpos-floor-footer"><div class="rpos-table-legend"><span><i class="available"></i>{{ t('Available','খালি') }}</span><span><i class="occupied"></i>{{ t('Occupied','ব্যবহৃত') }}</span><span><i class="reserved"></i>{{ t('Reserved','সংরক্ষিত') }}</span></div></div>
            </section>
            <section class="rpos-panel rpos-catalog" id="rpos-catalog" aria-labelledby="rpos-catalog-title">
                <div class="rpos-section-heading rpos-menu-heading"><div class="rpos-title-line"><h2 id="rpos-catalog-title">{{ t('Food menu','খাবারের মেনু') }}</h2><span id="rpos-result" class="rpos-count"></span></div><label class="rpos-search-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4" stroke-linecap="round"/></svg><input type="search" id="rpos-search" aria-keyshortcuts="F2" placeholder="{{ t('Search food, variant or SKU…','খাবার, ভ্যারিয়েন্ট বা SKU খুঁজুন…') }}" aria-label="{{ t('Search food','খাবার খুঁজুন') }}" autocomplete="off"></label></div>
                <div class="rpos-categories" id="rpos-categories" aria-label="{{ t('Food categories','খাবারের ক্যাটাগরি') }}"><button type="button" class="rpos-category" data-category="popular" title="{{ t('Popular in the last 30 days','গত ৩০ দিনের জনপ্রিয় খাবার') }}">{{ t('Popular','জনপ্রিয়') }}</button><button type="button" class="rpos-category active" data-category="">{{ t('All foods','সব খাবার') }}</button>@foreach($categories as $categoryId=>$categoryName)<button type="button" class="rpos-category" data-category="{{ $categoryId }}">{{ $categoryName }}</button>@endforeach</div>
                <p id="rpos-popular-hint" class="rst-muted rpos-popular-hint" hidden>{{ t('Popular items will appear as you make sales. Showing all foods for now.','বিক্রি হলে এখানে জনপ্রিয় খাবার দেখা যাবে। এখন সব খাবার দেখানো হচ্ছে।') }}</p>
                <div class="rpos-products" id="rpos-products"></div>
            </section>
        </div>
        <section class="rpos-panel rpos-order-panel" id="rpos-order-panel" aria-labelledby="rpos-order-title">
            <div class="rpos-order-top"><div><p class="rpos-eyebrow">{{ t('ORDER','অর্ডার') }}</p><div class="rpos-title-line"><h2 id="rpos-order-title">{{ t('New order','নতুন অর্ডার') }}</h2><span id="rpos-order-count" class="rpos-count">0</span></div></div><span class="rst-badge" id="rpos-status">{{ t('Draft','খসড়া') }}</span></div>
            <div id="rpos-closed" class="rpos-closed-banner" hidden>{{ t('Table closed','টেবিল বন্ধ হয়েছে') }}</div>
            <p id="rpos-workflow-label" class="rpos-workflow-label"></p>
            <form id="rpos-form" class="rpos-order-header" autocomplete="off">
                @csrf
                <input type="hidden" name="service_type" value="{{ $posSettings['default_service'] ?? 'takeaway' }}">
                <input type="hidden" name="dining_table_id" id="rpos-table">
                <div class="rpos-order-context" id="rpos-order-context"><label class="rpos-guest-count" for="rpos-guests"><span>{{ t('Guests','অতিথি') }}</span><input type="number" class="ret-input" name="guest_count" id="rpos-guests" value="1" min="1" max="1000" step="1" required></label></div>
                <div class="rpos-cart-heading"><span>{{ t('Item','খাবার') }}</span><span>{{ t('Qty','পরিমাণ') }}</span><span>{{ t('Amount','মূল্য') }}</span></div>
                <div id="rpos-lines" class="rpos-lines"></div>
                <details class="rpos-details" id="rpos-guest-details"><summary><span>{{ t('Customer & guest details','কাস্টমার ও অতিথির তথ্য') }}</span><span id="rpos-customer-summary" class="rpos-details-hint">{{ t('Walk-in','সাধারণ অতিথি') }}</span></summary><div class="rpos-customer-field"><label class="rpos-sr-only" for="rpos-customer">{{ t('Customer','কাস্টমার') }}</label><select class="ret-input" name="customer_id" id="rpos-customer"><option value="">{{ t('Walk-in guest','সাধারণ অতিথি') }}</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ t($customer->name,$customer->name_bn) }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>@endforeach</select></div><div class="rpos-fields"><div><label for="rpos-name">{{ t('Guest name','অতিথির নাম') }}</label><input class="ret-input" name="customer_name" id="rpos-name" maxlength="255"></div><div><label for="rpos-phone">{{ t('Phone','ফোন') }}</label><input class="ret-input" name="customer_phone" id="rpos-phone" maxlength="40" inputmode="tel"></div><div class="wide" id="rpos-address-field" hidden><label for="rpos-address">{{ t('Delivery address','ডেলিভারির ঠিকানা') }}</label><textarea class="ret-input" name="customer_address" id="rpos-address" maxlength="2000" rows="2"></textarea></div></div></details>
                <details class="rpos-details rpos-adjustments-details"><summary><span>{{ t('Discount, tax & notes','ছাড়, কর ও নোট') }}</span><span aria-hidden="true">＋</span></summary><div class="rpos-fields" id="rpos-adjustments"><div><label for="rpos-discount">{{ t('Discount amount','ছাড়ের পরিমাণ') }}</label><input class="ret-input" name="discount" id="rpos-discount" type="number" min="0" step=".01" value="0"></div><div><label for="rpos-coupon">{{ t('Coupon','কুপন') }}</label><input class="ret-input" name="coupon_code" id="rpos-coupon" maxlength="50"></div><div><label for="rpos-tax">{{ t('Tax / VAT','কর / ভ্যাট') }}</label><select class="ret-input" name="tax_id" id="rpos-tax"><option value="">{{ t('No tax','কর নেই') }}</option>@foreach($taxes as $tax)<option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" data-inclusive="{{ $tax->calculation_type === 'inclusive' ? 1 : 0 }}">{{ t($tax->name,$tax->name_bn) }} · {{ $tax->rate }}%</option>@endforeach</select></div><div id="rpos-shipping-field" hidden><label for="rpos-shipping">{{ t('Delivery charge','ডেলিভারি চার্জ') }}</label><input class="ret-input" name="shipping" id="rpos-shipping" type="number" min="0" step=".01" value="0"></div><div class="wide"><label for="rpos-notes">{{ t('Order notes','অর্ডারের নোট') }}</label><textarea class="ret-input" name="restaurant_notes" id="rpos-notes" maxlength="2000" rows="2" placeholder="{{ t('Any special instructions?','বিশেষ কোনো নির্দেশনা আছে?') }}"></textarea></div></div></details>
            </form>
            <div class="rpos-order-footer">
                <div class="rpos-totals"><div><span>{{ t('Subtotal','সাবটোটাল') }}</span><strong id="rpos-subtotal">৳0.00</strong></div><div><span>{{ t('Discount','ছাড়') }}</span><strong id="rpos-discount-total">৳0.00</strong></div><div><span>{{ t('Tax / VAT','কর / ভ্যাট') }}</span><strong id="rpos-tax-total">৳0.00</strong></div><div><span>{{ t('Delivery','ডেলিভারি') }}</span><strong id="rpos-shipping-total">৳0.00</strong></div><div id="rpos-paid-row" hidden><span>{{ t('Paid','পরিশোধিত') }}</span><strong id="rpos-paid">৳0.00</strong></div><div id="rpos-due-row" hidden><span>{{ t('Due','বাকি') }}</span><strong id="rpos-due">৳0.00</strong></div><div class="grand"><span id="rpos-total-label">{{ t('Estimated total','আনুমানিক মোট') }}</span><strong id="rpos-total">৳0.00</strong></div></div>
                <div class="rpos-actions">
                    <button type="button" id="rpos-checkout" aria-keyshortcuts="F8" title="{{ t('Payment','পেমেন্ট') }} · F8" class="ret-btn wide rpos-pay-button">{{ t('Pay & new order','পেমেন্ট ও নতুন অর্ডার') }}</button>
                    <button type="button" id="rpos-save" aria-keyshortcuts="F6" title="{{ t('Hold order','অর্ডার হোল্ড করুন') }} · F6" class="ret-btn alt">{{ t('Hold','হোল্ড') }}</button>
                    <button type="button" id="rpos-print-kot" aria-keyshortcuts="F10" class="ret-btn alt" title="{{ t('Print kitchen ticket','কিচেন টিকিট প্রিন্ট') }} · F10">{{ t('KOT','কিচেন টিকিট') }}</button>
                    <button type="button" id="rpos-print-bill" aria-keyshortcuts="F9 Control+p Meta+p" class="ret-btn alt" title="{{ t('Preview and print bill','বিল দেখুন ও প্রিন্ট করুন') }} · F9">{{ t('Bill','বিল') }}</button>
                    <button type="button" id="rpos-serve" class="ret-btn alt wide" hidden>{{ t('Mark ready items served','প্রস্তুত খাবার পরিবেশিত করুন') }}</button>
                    <button type="button" id="rpos-close" class="ret-btn wide" hidden>{{ t('Close table','টেবিল বন্ধ করুন') }}</button>
                </div>
                <details class="rpos-order-more" id="rpos-order-more"><summary>{{ t('More actions','অন্যান্য কাজ') }} <span aria-hidden="true">⌄</span></summary><div><button type="button" id="rpos-send" class="rpos-small-btn">{{ t('Send to kitchen','কিচেনে পাঠান') }}</button><button type="button" id="rpos-cancel" class="rpos-small-btn danger" hidden>{{ t('Cancel order','অর্ডার বাতিল') }}</button></div></details>
                <div class="rpos-print-links" hidden><a id="rpos-receipt" data-rpos-print="receipt" class="rpos-small-btn" hidden>{{ t('Print bill','বিল প্রিন্ট') }}</a><a id="rpos-kot" data-rpos-print="kot" class="rpos-small-btn" hidden>{{ t('Print KOT','কিচেন টিকিট প্রিন্ট') }}</a></div>
                <span id="rpos-draft-state" class="rpos-draft-label" aria-live="polite"></span>
            </div>
        </section>
    </div>
    <div class="rpos-mobile-bar" id="rpos-mobile-bar"><button type="button" id="rpos-mobile-cart" class="ret-btn alt"><span id="rpos-mobile-count"></span><strong id="rpos-mobile-total"></strong></button><button type="button" id="rpos-mobile-pay" class="ret-btn"></button></div>
    <dialog id="rpos-payment" class="rst-dialog" aria-labelledby="rpos-payment-title"><div class="rst-dialog-head"><h2 id="rpos-payment-title">{{ t('Receive payment','পেমেন্ট গ্রহণ') }}</h2><button type="button" class="rst-dialog-close" id="rpos-payment-close" aria-label="{{ t('Close','বন্ধ') }}">×</button></div><div class="rpos-payment-total"><span class="rst-muted">{{ t('Amount payable','পরিশোধযোগ্য') }}</span><strong id="rpos-pay-total"></strong></div><form id="rpos-payment-form"><div class="rpos-payment-grid"><label>{{ t('Payment type','পেমেন্টের ধরন') }}<select class="ret-input" name="payment_mode"><option value="full">{{ t('Full payment','সম্পূর্ণ পেমেন্ট') }}</option><option value="partial">{{ t('Partial payment','আংশিক পেমেন্ট') }}</option><option value="due">{{ t('Pay later','পরে পরিশোধ') }}</option></select></label><label>{{ t('Payment method','পেমেন্ট মাধ্যম') }}<select class="ret-input" name="method"><option value="cash" data-method="cash">{{ t('Cash','নগদ') }}</option><option value="bank" data-method="bank">{{ t('Bank','ব্যাংক') }}</option>@foreach($paymentMethods as $method)<option value="configured:{{ $method->id }}" data-id="{{ $method->id }}" data-method="{{ $method->method_type ?? 'bank' }}">{{ t($method->name,$method->name_bn) }}</option>@endforeach</select></label><label>{{ t('Amount received','গৃহীত টাকা') }}<input class="ret-input" type="number" name="tendered" min="0" step=".01" required></label><label>{{ t('Reference','রেফারেন্স') }}<input class="ret-input" name="payment_reference" maxlength="255"></label></div><div class="rpos-quick" id="rpos-quick"><button type="button" class="ret-btn alt" data-amount="exact">{{ t('Exact','সঠিক পরিমাণ') }}</button>@foreach([500,1000,2000] as $amount)<button type="button" class="ret-btn alt" data-amount="{{ $amount }}">৳{{ $amount }}</button>@endforeach</div><div class="rpos-totals"><div><span>{{ t('Change','ফেরত টাকা') }}</span><strong id="rpos-change"></strong></div><div><span>{{ t('Remaining due','অবশিষ্ট বাকি') }}</span><strong id="rpos-payment-due"></strong></div></div><p id="rpos-payment-error" class="rpos-payment-error" role="alert"></p><button type="submit" id="rpos-payment-submit" class="ret-btn" style="width:100%">{{ t('Confirm payment','পেমেন্ট নিশ্চিত করুন') }}</button></form></dialog>
    @include('public.business.admin.sales.restaurant-pos.print-dialog')
</main>
@include('public.business.admin.sales.restaurant-pos.script')
@include('public.business.admin.sales.restaurant-pos.print-script')
@endsection
