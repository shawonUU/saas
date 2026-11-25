<?php
use Illuminate\Support\Facades\Route;

Route::get('online-orders/online-order-list',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList\OnlineOrderController::class,'index'])->name('online-orders.online-order-list.index');
Route::get('online-orders/online-order-list/{order}',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList\OnlineOrderController::class,'show'])->whereNumber('order')->name('online-orders.online-order-list.show');
Route::post('online-orders/cancellation/{record}',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList\OnlineOrderController::class,'reviewCancellation'])->whereNumber('record')->name('online-orders.online-order-list.cancellation.review');
