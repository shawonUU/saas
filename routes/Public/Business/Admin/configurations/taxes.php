<?php
// Taxes routes: independent, explicit paths under /configurations.
use App\Http\Controllers\Public\Business\Admin\Configurations\TaxController;
use Illuminate\Support\Facades\Route;

Route::prefix('taxes')->name('taxes.')->controller(TaxController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('/{record}', 'update')->whereNumber('record')->name('update');
    Route::delete('/{record}', 'destroy')->whereNumber('record')->name('destroy');
});
