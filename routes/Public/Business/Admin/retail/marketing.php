<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\MarketingController;
Route::get('ecommerce/online-marketing/campaigns',[MarketingController::class,'campaigns'])->name('marketing.campaigns');
Route::post('ecommerce/online-marketing/campaigns',[MarketingController::class,'store'])->name('marketing.store');
Route::post('ecommerce/online-marketing/campaigns/{record}/toggle',[MarketingController::class,'toggle'])->whereNumber('record')->name('marketing.toggle');
Route::get('ecommerce/online-marketing/abandoned-carts',[MarketingController::class,'abandoned'])->name('marketing.abandoned');
