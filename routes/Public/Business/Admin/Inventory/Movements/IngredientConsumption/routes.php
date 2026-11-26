<?php

use App\Http\Controllers\Public\Business\Admin\Inventory\Movements\IngredientConsumption\IngredientConsumptionController;
use Illuminate\Support\Facades\Route;

Route::get('movements/ingredient-consumption', [IngredientConsumptionController::class, 'index'])->name('movements.ingredient-consumption.index');
