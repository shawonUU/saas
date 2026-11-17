<?php
use App\Http\Controllers\Public\Business\Admin\Retail\PurchaseOrderController;
Route::get('purchasing/purchase-orders/po-list',[PurchaseOrderController::class,'index'])->name('purchase_orders.index');
Route::get('purchasing/purchase-orders/new-po',[PurchaseOrderController::class,'create'])->name('purchase_orders.create');
Route::post('purchasing/purchase-orders/new-po',[PurchaseOrderController::class,'store'])->name('purchase_orders.store');
Route::get('purchasing/purchase-orders/po-list/{record}',[PurchaseOrderController::class,'show'])->whereNumber('record')->name('purchase_orders.show');
Route::post('purchasing/purchase-orders/po-list/{record}/approve',[PurchaseOrderController::class,'approve'])->whereNumber('record')->name('purchase_orders.approve');
Route::post('purchasing/purchase-orders/po-list/{record}/receive',[PurchaseOrderController::class,'receive'])->whereNumber('record')->name('purchase_orders.receive');
