<?php
use App\Http\Controllers\Public\Business\Admin\Sales\ReturnController;
use Illuminate\Support\Facades\Route;

Route::get('sales/returns/return-list',[ReturnController::class,'index'])->name('returns.index');
Route::get('sales/returns/new-return',[ReturnController::class,'create'])->name('returns.create');
Route::post('sales/returns/new-return',[ReturnController::class,'store'])->name('returns.store');
