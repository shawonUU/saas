<?php
use Illuminate\Support\Facades\Route;

Route::get('retail-pos',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'index'])->name('retail-pos.index');
Route::post('retail-pos',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'store'])->name('retail-pos.store');
Route::post('retail-pos/quote',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'quote'])->name('retail-pos.quote');
Route::get('retail-pos/receipt/{order}',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'receipt'])->whereNumber('order')->name('retail-pos.receipt');
