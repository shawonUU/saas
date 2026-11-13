<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Authority\Auth\AuthorityAuthController;
use App\Http\Controllers\Authority\FeatureAccessController;
use App\Http\Controllers\Authority\FeatureController;

$domain = config('saas.domain');
Route::domain('admin.'.$domain)->name('authority.')->group(function () {
    Route::get('/', fn () => redirect()->route('authority.login'))->name('home');
    Route::get('/login', [AuthorityAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthorityAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('portal.auth:authority')->group(function () {
        Route::get('/dashboard', fn () => view('portals.dashboard', [
            'title' => 'SaaS Authority', 'user' => Auth::guard('authority')->user(),
            'subtitle' => t('Platform authority dashboard', 'প্ল্যাটফর্ম অথরিটি ড্যাশবোর্ড'),
            'logoutUrl' => route('authority.logout'), 'links' => [[t('Manage modules & menus', 'মডিউল ও মেনু পরিচালনা'), route('authority.features.index')], [t('Category and shop access', 'ক্যাটাগরি ও শপ অ্যাক্সেস'), route('authority.access.index')]],
        ]))->name('dashboard');
        Route::get('/features', [FeatureController::class, 'index'])->name('features.index');
        Route::post('/features/{type}', [FeatureController::class, 'store'])->name('features.store');
        Route::put('/features/{type}/{id}', [FeatureController::class, 'update'])->name('features.update');
        Route::delete('/features/{type}/{id}', [FeatureController::class, 'destroy'])->name('features.destroy');
        Route::get('/access', [FeatureAccessController::class, 'categories'])->name('access.index');
        Route::get('/access/category/{category}', [FeatureAccessController::class, 'category'])->name('access.category');
        Route::put('/access/category/{category}', [FeatureAccessController::class, 'saveCategory'])->name('access.category.save');
        Route::get('/access/shop/{business}', [FeatureAccessController::class, 'shop'])->name('access.shop');
        Route::put('/access/shop/{business}', [FeatureAccessController::class, 'saveShop'])->name('access.shop.save');
        Route::post('/logout', [AuthorityAuthController::class, 'logout'])->name('logout');
    });
});