<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\CourierController;
Route::get('ecommerce/online-orders/fulfillment/courier',[CourierController::class,'index'])->name('courier.index');
Route::post('ecommerce/online-orders/fulfillment/courier',[CourierController::class,'book'])->name('courier.book');
Route::post('ecommerce/online-orders/fulfillment/courier/{record}/refresh',[CourierController::class,'refresh'])->whereNumber('record')->name('courier.refresh');
Route::post('ecommerce/online-orders/fulfillment/courier/{record}/clear',[CourierController::class,'resetUnknown'])->whereNumber('record')->name('courier.clear');
