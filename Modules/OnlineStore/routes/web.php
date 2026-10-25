<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineStore\App\Http\Controllers\OnlineStoreController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('onlinestores', OnlineStoreController::class)->names('onlinestore');
});
