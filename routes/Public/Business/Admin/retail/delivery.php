<?php
use App\Http\Controllers\Public\Business\Admin\Retail\DeliveryController;
Route::get('ecommerce/online-orders/fulfillment',[DeliveryController::class,'index'])->name('delivery.index');
Route::put('ecommerce/online-orders/fulfillment/{record}',[DeliveryController::class,'update'])->whereNumber('record')->name('delivery.update');
