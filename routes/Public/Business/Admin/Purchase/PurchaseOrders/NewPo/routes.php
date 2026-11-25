<?php
use Illuminate\Support\Facades\Route;

Route::get('purchase-orders/new-po',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'create'])->name('purchase-orders.new-po.create');
Route::post('purchase-orders/new-po',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseOrders\PurchaseOrderController::class,'store'])->name('purchase-orders.new-po.store');
