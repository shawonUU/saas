<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Ecommerce\ReturnRequestController;
Route::get('ecommerce/online-orders/return-requests',[ReturnRequestController::class,'index'])->name('business.admin.ecommerce.returns');
Route::post('ecommerce/online-orders/return-requests/{record}',[ReturnRequestController::class,'review'])->whereNumber('record')->name('business.admin.ecommerce.returns.review');
