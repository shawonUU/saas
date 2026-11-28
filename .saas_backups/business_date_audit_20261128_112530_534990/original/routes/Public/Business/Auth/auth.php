<?php

use App\Http\Controllers\Auth\PortalPasswordController;
use App\Http\Controllers\Public\Business\Auth\AdminAuthController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$domain = config('saas.domain');
// Reserve admin.saas.test for platform authority before resolving business domains.

// Business admin portals and the wildcard customer storefront are kept independent.
Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->name('business.admin.')->group(function () {
    Route::get('/', fn (Request $request) => redirect()->route('business.admin.login', ['business' => $request->route('business')]))->name('home');
    Route::get('/forgot-password', [PortalPasswordController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [PortalPasswordController::class, 'sendLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PortalPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PortalPasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware(['portal.auth:business_admin', 'auth.session'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
