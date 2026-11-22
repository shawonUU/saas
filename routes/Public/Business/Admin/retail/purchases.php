<?php
use App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('purchasing/receiving/grn-list',[PurchaseController::class,'index'])->name('purchases.index');
Route::get('purchasing/receiving/new-grn',[PurchaseController::class,'create'])->name('purchases.create');
Route::post('purchasing/receiving/new-grn',[PurchaseController::class,'store'])->name('purchases.store');
