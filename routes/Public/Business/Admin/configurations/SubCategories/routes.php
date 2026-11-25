<?php
use Illuminate\Support\Facades\Route;

Route::get('sub-categories',[\App\Http\Controllers\Public\Business\Admin\Configurations\SubCategories\SubCategoryController::class,'index'])->name('sub-categories.index');
Route::post('sub-categories',[\App\Http\Controllers\Public\Business\Admin\Configurations\SubCategories\SubCategoryController::class,'store'])->name('sub-categories.store');
Route::put('sub-categories/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\SubCategories\SubCategoryController::class,'update'])->whereNumber('record')->name('sub-categories.update');
Route::delete('sub-categories/{record}',[\App\Http\Controllers\Public\Business\Admin\Configurations\SubCategories\SubCategoryController::class,'destroy'])->whereNumber('record')->name('sub-categories.destroy');
