<?php
use Illuminate\Support\Facades\Route;

Route::get('orders/order-details/{order?}',[\App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController::class,'show'])->whereNumber('order')->name('orders.order-details.show');
Route::post('orders/order-details/{order}/cancel',[\App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController::class,'cancel'])->whereNumber('order')->name('orders.order-details.cancel');
