<?php
use Illuminate\Support\Facades\Route;

Route::get('colors',[\App\Http\Controllers\Public\Business\Admin\Configurations\Colors\ColorController::class,'index'])->name('colors.index');
Route::post('colors',[\App\Http\Controllers\Public\Business\Admin\Configurations\Colors\ColorController::class,'store'])->name('colors.store');
Route::put('colors/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Colors\ColorController::class,'update'])->whereNumber('record')->name('colors.update');
Route::delete('colors/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Colors\ColorController::class,'destroy'])->whereNumber('record')->name('colors.destroy');
