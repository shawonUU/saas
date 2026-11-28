<?php

use App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings\GeneralSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('business/branding', [GeneralSettingsController::class, 'branding'])
    ->name('business.branding.index');
