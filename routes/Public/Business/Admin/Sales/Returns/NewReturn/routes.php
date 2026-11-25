<?php
use Illuminate\Support\Facades\Route;

Route::get('returns/new-return',[\App\Http\Controllers\Public\Business\Admin\Sales\Returns\ReturnController::class,'create'])->name('returns.new-return.create');
Route::post('returns/new-return',[\App\Http\Controllers\Public\Business\Admin\Sales\Returns\ReturnController::class,'store'])->name('returns.new-return.store');
