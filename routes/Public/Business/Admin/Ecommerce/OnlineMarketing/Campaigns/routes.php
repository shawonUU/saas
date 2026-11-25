<?php
use Illuminate\Support\Facades\Route;

Route::get('online-marketing/campaigns',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineMarketing\MarketingController::class,'campaigns'])->name('online-marketing.campaigns.index');
Route::post('online-marketing/campaigns',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineMarketing\MarketingController::class,'store'])->name('online-marketing.campaigns.store');
Route::post('online-marketing/campaigns/{record}/toggle',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineMarketing\MarketingController::class,'toggle'])->whereNumber('record')->name('online-marketing.campaigns.toggle');
