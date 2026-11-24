<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Ecommerce\OnlineOrderController;
Route::get('ecommerce/online-orders/online-order-list',[OnlineOrderController::class,'index'])->name('business.admin.ecommerce.orders');
Route::get('ecommerce/online-orders/online-order-list/{order}',[OnlineOrderController::class,'show'])->whereNumber('order')->name('business.admin.ecommerce.orders.show');

Route::post('ecommerce/online-orders/cancellation/{record}',[OnlineOrderController::class,'reviewCancellation'])->whereNumber('record')->name('business.admin.ecommerce.cancellation.review');
