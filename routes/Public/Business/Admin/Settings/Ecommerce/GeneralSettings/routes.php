<?php

use App\Http\Controllers\Public\Business\Admin\Settings\Ecommerce\GeneralSettings\GeneralSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('ecommerce/general-settings', [GeneralSettingsController::class, 'index'])
    ->name('ecommerce.general-settings.index');
Route::put('ecommerce/general-settings', [GeneralSettingsController::class, 'update'])
    ->name('ecommerce.general-settings.update');
