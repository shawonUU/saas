<?php

use App\Http\Controllers\Public\Business\Public\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



$domain = config('saas.domain');

Route::domain('{business}.'.$domain)->middleware('business.resolve')->name('store.customer.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::middleware('portal.auth:business_customer')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $business = $request->attributes->get('current_business');
            return view('portals.dashboard', [
                'title' => $business->name.' · Customer', 'user' => Auth::guard('business_customer')->user(),
                'subtitle' => t('My customer account', 'আমার কাস্টমার অ্যাকাউন্ট'),
                'logoutUrl' => route('store.customer.logout', ['business' => $business->code]),
                'links' => [[t('Store home', 'স্টোর হোম'), route('store.customer.home', ['business' => $business->code])]],
            ]);
        })->name('dashboard');
    });
});