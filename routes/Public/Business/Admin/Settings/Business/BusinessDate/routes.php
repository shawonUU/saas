<?php

use App\Http\Controllers\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDateController;
use Illuminate\Support\Facades\Route;

Route::get('business/business-date', [BusinessDateController::class, 'index'])->name('business.business-date.index');
Route::put('business/business-date', [BusinessDateController::class, 'update'])->name('business.business-date.update');
Route::post('business/business-date/close', [BusinessDateController::class, 'close'])->name('business.business-date.close');
