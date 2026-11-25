<?php
use Illuminate\Support\Facades\Route;

Route::get('purchase-orders/po-list',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'index'])->name('purchase-orders.po-list.index');
Route::get('purchase-orders/po-list/{record}',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'show'])->whereNumber('record')->name('purchase-orders.po-list.show');
Route::post('purchase-orders/po-list/{record}/approve',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'approve'])->whereNumber('record')->name('purchase-orders.po-list.approve');
Route::post('purchase-orders/po-list/{record}/receive',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'receive'])->whereNumber('record')->name('purchase-orders.po-list.receive');
