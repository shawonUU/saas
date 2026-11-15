<?php

use App\Http\Controllers\Public\Auth\OwnerAuthController;
use App\Http\Controllers\Public\Business\BusinessRegistrationController;
use App\Http\Controllers\Public\HomeController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;





$domain = config('saas.domain');

Route::domain($domain)->group(function () {
    Route::name('saas.')->group(function () {
        Route::get('/forgot-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'forgotForm'])->name('password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'sendLink'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'resetForm'])->name('password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\Auth\PortalPasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
        Route::get('/login', [OwnerAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [OwnerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
        Route::get('/register', [OwnerAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [OwnerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
        Route::middleware(['portal.auth:saas_user', 'auth.session'])->group(function () {
            Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');
        });
    });
});
