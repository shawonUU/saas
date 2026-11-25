<?php
use Illuminate\Support\Facades\Route;

Route::get('orders/order-list',[\App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController::class,'index'])->name('orders.order-list.index');
