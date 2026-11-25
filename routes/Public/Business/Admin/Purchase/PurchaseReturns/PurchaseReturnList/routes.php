<?php
use Illuminate\Support\Facades\Route;

Route::get('purchase-returns/purchase-return-list',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseReturns\PurchaseReturnList\PurchaseReturnController::class,'index'])->name('purchase-returns.purchase-return-list.index');
Route::post('purchase-returns/purchase-return-list',[\App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseReturns\PurchaseReturnList\PurchaseReturnController::class,'store'])->name('purchase-returns.purchase-return-list.store');
