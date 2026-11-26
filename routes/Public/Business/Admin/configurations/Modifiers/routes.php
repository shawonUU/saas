<?php

use App\Http\Controllers\Public\Business\Admin\Configurations\Modifiers\ModifierController;
use Illuminate\Support\Facades\Route;

Route::get('modifiers', [ModifierController::class, 'index'])->name('modifiers.index');
Route::post('modifiers', [ModifierController::class, 'store'])->name('modifiers.store');
Route::put('modifiers/{record}', [ModifierController::class, 'update'])->whereNumber('record')->name('modifiers.update');
Route::delete('modifiers/{record}', [ModifierController::class, 'destroy'])->whereNumber('record')->name('modifiers.destroy');
