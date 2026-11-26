<?php

use App\Http\Controllers\Public\Business\Admin\Sales\Settings\PosSettings\PosSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('settings/pos-settings', [PosSettingsController::class, 'index'])->name('settings.pos-settings.index');
Route::put('settings/pos-settings', [PosSettingsController::class, 'update'])->name('settings.pos-settings.update');
