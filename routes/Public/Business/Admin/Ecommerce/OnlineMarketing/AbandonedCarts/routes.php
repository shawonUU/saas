<?php
use Illuminate\Support\Facades\Route;

Route::get('online-marketing/abandoned-carts',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineMarketing\MarketingController::class,'abandoned'])->name('online-marketing.abandoned-carts.index');
