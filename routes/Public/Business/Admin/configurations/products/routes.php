<?php
use Illuminate\Support\Facades\Route;

Route::get('products',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'index'])->name('products.index');
Route::get('products/create',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'create'])->name('products.create');
Route::post('products',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'store'])->name('products.store');
Route::get('products/{record}/edit',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'edit'])->whereNumber('record')->name('products.edit');
Route::put('products/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'update'])->whereNumber('record')->name('products.update');
Route::delete('products/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Products\ProductController::class,'destroy'])->whereNumber('record')->name('products.destroy');
