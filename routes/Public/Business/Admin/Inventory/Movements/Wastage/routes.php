<?php

use App\Http\Controllers\Public\Business\Admin\Inventory\Movements\Wastage\WastageController;
use Illuminate\Support\Facades\Route;

Route::get('movements/wastage', [WastageController::class, 'index'])->name('movements.wastage.index');
Route::post('movements/wastage', [WastageController::class, 'store'])->name('movements.wastage.store');
