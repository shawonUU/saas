<?php

use App\Http\Controllers\Public\Business\Public\Auth\CustomerAuthController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use App\Http\Controllers\Public\Business\Auth\AdminAuthController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


$domain = config('saas.domain');

Route::domain('{business}.'.$domain)->middleware(['business.resolve', 'business.audit.context'])->name('business.customer.')->group(function () {
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'sendLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
    Route::get('/login', [CustomerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
    Route::middleware(['portal.auth:business_customer', 'auth.session'])->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});
