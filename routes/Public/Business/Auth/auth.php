<?php

use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use App\Http\Controllers\Public\Business\Auth\AdminAuthController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;




$domain = config('saas.domain');
// Reserve admin.saas.test for platform authority before resolving business domains.

// Business admin portals and the wildcard customer storefront are kept independent.
Route::domain('admin.{business}.'.$domain)->middleware('business.resolve')->name('store.admin.')->group(function () {
    Route::get('/', fn (Request $request) => redirect()->route('store.admin.login', ['business' => $request->route('business')]))->name('home');
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('portal.auth:business_admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});