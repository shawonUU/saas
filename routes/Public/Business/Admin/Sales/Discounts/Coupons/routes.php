<?php
use Illuminate\Support\Facades\Route;

Route::get('discounts/coupons',[\App\Http\Controllers\Public\Business\Admin\Sales\Discounts\Coupons\DiscountController::class,'index'])->name('discounts.coupons.index');
Route::post('discounts/coupons',[\App\Http\Controllers\Public\Business\Admin\Sales\Discounts\Coupons\DiscountController::class,'store'])->name('discounts.coupons.store');
Route::put('discounts/coupons/{record}',[\App\Http\Controllers\Public\Business\Admin\Sales\Discounts\Coupons\DiscountController::class,'update'])->whereNumber('record')->name('discounts.coupons.update');
