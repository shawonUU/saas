<?php
use App\Http\Controllers\Public\Business\Admin\Retail\ProductController;
Route::get('configurations/products',[ProductController::class,'index'])->name('products.index');
Route::get('configurations/products/create',[ProductController::class,'create'])->name('products.create');
Route::post('configurations/products',[ProductController::class,'store'])->name('products.store');
Route::get('configurations/products/{record}/edit',[ProductController::class,'edit'])->whereNumber('record')->name('products.edit');
Route::put('configurations/products/{record}',[ProductController::class,'update'])->whereNumber('record')->name('products.update');
Route::delete('configurations/products/{record}',[ProductController::class,'destroy'])->whereNumber('record')->name('products.destroy');
