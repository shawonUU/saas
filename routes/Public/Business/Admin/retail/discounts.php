<?php
use App\Http\Controllers\Public\Business\Admin\Retail\DiscountController;
Route::get('sales/discounts/coupons',[DiscountController::class,'index'])->name('discounts.index');
Route::post('sales/discounts/coupons',[DiscountController::class,'store'])->name('discounts.store');
Route::put('sales/discounts/coupons/{record}',[DiscountController::class,'update'])->whereNumber('record')->name('discounts.update');
