<?php
use Illuminate\Support\Facades\Route;

Route::get('courier/courier-booking',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'index'])->name('courier.courier-booking.index');
Route::post('courier/courier-booking',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'book'])->name('courier.courier-booking.book');
Route::post('courier/courier-booking/{record}/refresh',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'refresh'])->whereNumber('record')->name('courier.courier-booking.refresh');
Route::post('courier/courier-booking/{record}/clear',[\App\Http\Controllers\Public\Business\Admin\Logistics\Courier\CourierBooking\CourierController::class,'resetUnknown'])->whereNumber('record')->name('courier.courier-booking.clear');
