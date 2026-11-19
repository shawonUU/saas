<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\OrderController;
Route::get('sales/orders/order-list',[OrderController::class,'index'])->name('orders.index');
Route::get('sales/orders/new-order',[OrderController::class,'create'])->name('orders.create');
Route::post('sales/orders/new-order',[OrderController::class,'store'])->name('orders.store');
Route::get('sales/orders/order-details/{order?}',[OrderController::class,'show'])->whereNumber('order')->name('orders.show');
Route::post('sales/orders/order-details/{order}/cancel',[OrderController::class,'cancel'])->whereNumber('order')->name('orders.cancel');
