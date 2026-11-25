<?php
use Illuminate\Support\Facades\Route;

Route::get('pos',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'index'])->name('pos.index');
Route::post('pos',[\App\Http\Controllers\Public\Business\Admin\Sales\Pos\PosController::class,'store'])->name('pos.store');
