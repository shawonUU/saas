<?php

use App\Http\Controllers\Business\Public\Auth\CustomerAuthController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use App\Http\Controllers\Public\Business\Auth\AdminAuthController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


$domain = config('saas.domain');

Route::domain('{business}.'.$domain)->middleware('business.resolve')->name('store.customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
    Route::middleware('portal.auth:business_customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});