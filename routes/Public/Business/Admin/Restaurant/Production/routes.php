<?php

use App\Http\Controllers\Public\Business\Admin\Restaurant\Production\ProductionController;
use Illuminate\Support\Facades\Route;

Route::get('production', [ProductionController::class, 'index'])->name('production.index');
Route::post('production', [ProductionController::class, 'store'])->name('production.store');
