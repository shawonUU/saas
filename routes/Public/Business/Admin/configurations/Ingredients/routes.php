<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Admin\Configurations\Ingredients\IngredientController;
Route::get('ingredients',[IngredientController::class,'index'])->name('ingredients.index');
Route::post('ingredients',[IngredientController::class,'store'])->name('ingredients.store');
Route::delete('ingredients/{record}',[IngredientController::class,'destroy'])->whereNumber('record')->name('ingredients.destroy');
