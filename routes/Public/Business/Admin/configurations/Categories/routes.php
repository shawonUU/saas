<?php
use Illuminate\Support\Facades\Route;

Route::get('categories',[\App\Http\Controllers\Public\Business\Admin\Configurations\Categories\CategoryController::class,'index'])->name('categories.index');
Route::post('categories',[\App\Http\Controllers\Public\Business\Admin\Configurations\Categories\CategoryController::class,'store'])->name('categories.store');
Route::put('categories/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Categories\CategoryController::class,'update'])->whereNumber('record')->name('categories.update');
Route::delete('categories/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Categories\CategoryController::class,'destroy'])->whereNumber('record')->name('categories.destroy');
