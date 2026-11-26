<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Configurations\Recipes\RecipeController;
Route::get('recipes',[RecipeController::class,'index'])->name('recipes.index');
Route::post('recipes',[RecipeController::class,'store'])->name('recipes.store');
Route::put('recipes/{record}',[RecipeController::class,'update'])->whereNumber('record')->name('recipes.update');
Route::delete('recipes/{record}',[RecipeController::class,'destroy'])->whereNumber('record')->name('recipes.destroy');
