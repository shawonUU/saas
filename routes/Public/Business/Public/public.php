<?php

use App\Http\Controllers\Public\Business\Public\Ecommerce\Storefront\Home\StorefrontController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Catalog\Products\CatalogController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Catalog\ProductDetails\ProductDetailsController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\Cart\CartController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Wishlist\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



$domain = config('saas.domain');

Route::domain('{business}.'.$domain)->middleware('business.resolve')->name('business.customer.')->group(function () {
    Route::get('/', [StorefrontController::class, 'index'])->name('home');
    Route::get('/shop', [CatalogController::class,'index'])->name('shop');
    Route::get('/product/{product}',[ProductDetailsController::class,'show'])->whereNumber('product')->name('product');
    Route::get('/cart',[CartController::class,'index'])->name('cart');
    Route::get('/checkout',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\PlaceOrder\CheckoutController::class,'index'])->name('checkout');
    Route::post('/checkout/quote',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\PlaceOrder\QuoteController::class,'quote'])->middleware('throttle:40,1')->name('checkout.quote');
    Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist');
    Route::post('/shop/checkout', [\App\Http\Controllers\Public\Business\Public\ShopController::class, 'checkout'])->middleware('throttle:15,1')->name('shop.checkout');
    Route::post('/shop/abandoned-cart', [\App\Http\Controllers\Public\Business\Public\ShopController::class,'saveCart'])->middleware('throttle:10,1')->name('shop.abandoned');
    Route::get('/payments/sslcommerz/{order}/pay', [\App\Http\Controllers\Public\Business\Public\GatewayController::class,'start'])->middleware(['signed','throttle:10,1'])->name('gateway.start');
    Route::match(['get','post'],'/payments/sslcommerz/callback', [\App\Http\Controllers\Public\Business\Public\GatewayController::class,'callback'])->middleware('throttle:30,1')->name('gateway.callback');
    Route::get('/shop/thanks/{order}', [\App\Http\Controllers\Public\Business\Public\ShopController::class, 'success'])->name('shop.success');
    Route::middleware(['portal.auth:business_customer', 'auth.session'])->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $business = $request->attributes->get('current_business');
            return view('portals.dashboard', [
                'title' => $business->name.' · Customer', 'user' => Auth::guard('business_customer')->user(),
                'subtitle' => t('My customer account', 'আমার কাস্টমার অ্যাকাউন্ট'),
                'logoutUrl' => route('business.customer.logout', ['business' => $business->domain]),
                'links' => [[t('Store home', 'স্টোর হোম'), route('business.customer.home', ['business' => $business->domain])]],
            ]);
        })->name('dashboard');
    });
});
