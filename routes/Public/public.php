<?php

use App\Http\Controllers\Public\Auth\OwnerAuthController;
use App\Http\Controllers\Public\Business\BusinessRegistrationController;
use App\Http\Controllers\Public\DashboardController;
use App\Http\Controllers\Public\HomeController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



$domain = config('saas.domain');

Route::domain($domain)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('saas.home');
    Route::name('saas.')->group(function () {
        Route::middleware('portal.auth:web')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('/businesss/create', [BusinessRegistrationController::class, 'create'])->name('businesss.create');
            Route::post('/businesss', [BusinessRegistrationController::class, 'store'])->name('businesss.store');
        });
    });
});