<?php
use Illuminate\Support\Facades\Route;

Route::get('online-orders/return-requests',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrders\ReturnRequests\ReturnRequestController::class,'index'])->name('online-orders.return-requests.index');
Route::post('online-orders/return-requests/{record}',[\App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrders\ReturnRequests\ReturnRequestController::class,'review'])->whereNumber('record')->name('online-orders.return-requests.review');
