<?php
use App\Http\Controllers\Public\Business\Admin\Retail\IntegrationController;
Route::get('settings/integrations/api-keys',[IntegrationController::class,'index'])->name('integrations.index');
Route::post('settings/integrations/api-keys',[IntegrationController::class,'store'])->name('integrations.store');
