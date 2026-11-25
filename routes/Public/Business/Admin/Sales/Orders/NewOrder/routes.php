<?php
use Illuminate\Support\Facades\Route;

Route::get('orders/new-order',[\App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController::class,'create'])->name('orders.new-order.create');
Route::post('orders/new-order',[\App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController::class,'store'])->name('orders.new-order.store');
