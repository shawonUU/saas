<?php
use Illuminate\Support\Facades\Route;

Route::get('online-orders/fulfillment/courier',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'index'])->name('online-orders.fulfillment.courier.index');
Route::post('online-orders/fulfillment/courier',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'book'])->name('online-orders.fulfillment.courier.book');
Route::post('online-orders/fulfillment/courier/{record}/refresh',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'refresh'])->whereNumber('record')->name('online-orders.fulfillment.courier.refresh');
Route::post('online-orders/fulfillment/courier/{record}/clear',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'resetUnknown'])->whereNumber('record')->name('online-orders.fulfillment.courier.clear');
Route::get('online-orders/fulfillment',[\App\Http\Controllers\Public\Business\Admin\Logistics\Delivery\DeliveryList\DeliveryController::class,'index'])->name('online-orders.fulfillment.index');
Route::put('online-orders/fulfillment/{record}',[\App\Http\Controllers\Public\Business\Admin\Logistics\Delivery\DeliveryList\DeliveryController::class,'update'])->whereNumber('record')->name('online-orders.fulfillment.update');
