<?php
use Illuminate\Support\Facades\Route;

Route::get('sizes',[\App\Http\Controllers\Public\Business\Admin\Configurations\Sizes\SizeController::class,'index'])->name('sizes.index');
Route::post('sizes',[\App\Http\Controllers\Public\Business\Admin\Configurations\Sizes\SizeController::class,'store'])->name('sizes.store');
Route::put('sizes/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Sizes\SizeController::class,'update'])->whereNumber('record')->name('sizes.update');
Route::delete('sizes/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Sizes\SizeController::class,'destroy'])->whereNumber('record')->name('sizes.destroy');
