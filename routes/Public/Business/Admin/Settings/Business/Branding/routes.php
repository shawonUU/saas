<?php
use Illuminate\Support\Facades\Route;

Route::get('business/branding',[\App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings\GeneralSettingsController::class,'index'])->name('business.branding.index')->defaults('child','branding');
