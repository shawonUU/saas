<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseReturnController;
Route::get('purchasing/purchase-returns/purchase-return-list',[PurchaseReturnController::class,'index'])->name('purchase_returns.index');
Route::post('purchasing/purchase-returns/purchase-return-list',[PurchaseReturnController::class,'store'])->name('purchase_returns.store');
