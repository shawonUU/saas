<?php
use App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings\GeneralSettingsController;
use Illuminate\Support\Facades\Route;
Route::get('settings/business/general-settings',[GeneralSettingsController::class,'index'])->name('business.admin.settings.business.general-settings.index')->defaults('child','general-settings');
Route::get('settings/business/branding',[GeneralSettingsController::class,'index'])->name('business.admin.settings.business.branding.index')->defaults('child','branding');
Route::put('settings/business/general-settings',[GeneralSettingsController::class,'update'])->name('business.admin.settings.business.general-settings.update');
