<?php
use Illuminate\Support\Facades\Route;

Route::post('returns/refund',[\App\Http\Controllers\Public\Business\Admin\Sales\Returns\ReturnList\RefundController::class,'refund'])->name('returns.return-list.refund');
Route::get('returns/return-list',[\App\Http\Controllers\Public\Business\Admin\Sales\Returns\ReturnController::class,'index'])->name('returns.return-list.index');
