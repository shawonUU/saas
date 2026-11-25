<?php
use Illuminate\Support\Facades\Route;

Route::get('integrations/api-keys',[\App\Http\Controllers\Public\Business\Admin\Settings\Integrations\ApiKeys\IntegrationController::class,'index'])->name('integrations.api-keys.index');
Route::post('integrations/api-keys',[\App\Http\Controllers\Public\Business\Admin\Settings\Integrations\ApiKeys\IntegrationController::class,'store'])->name('integrations.api-keys.store');
