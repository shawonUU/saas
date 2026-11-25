<?php
use Illuminate\Support\Facades\Route;

Route::get('brands',[\App\Http\Controllers\Public\Business\Admin\Configurations\Brands\BrandController::class,'index'])->name('brands.index');
Route::post('brands',[\App\Http\Controllers\Public\Business\Admin\Configurations\Brands\BrandController::class,'store'])->name('brands.store');
Route::put('brands/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Brands\BrandController::class,'update'])->whereNumber('record')->name('brands.update');
Route::delete('brands/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\Brands\BrandController::class,'destroy'])->whereNumber('record')->name('brands.destroy');
