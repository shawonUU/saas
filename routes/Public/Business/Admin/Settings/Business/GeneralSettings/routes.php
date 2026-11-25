<?php
use Illuminate\Support\Facades\Route;

Route::get('business/general-settings',[\App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings\GeneralSettingsController::class,'index'])->name('business.general-settings.index')->defaults('child','general-settings');
Route::put('business/general-settings',[\App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings\GeneralSettingsController::class,'update'])->name('business.general-settings.update');
