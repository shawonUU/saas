
<?php

use Illuminate\Support\Facades\Route;
use Modules\OnlineStore\App\Http\Controllers\OnlineStoreController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        'businesses/{businessId}/online-stores',
        [OnlineStoreController::class, 'index']
    );

    Route::post(
        'businesses/{businessId}/online-stores',
        [OnlineStoreController::class, 'store']
    );

    Route::get(
        'online-stores/{storeId}',
        [OnlineStoreController::class, 'show']
    );

    Route::patch(
        'online-stores/{storeId}',
        [OnlineStoreController::class, 'update']
    );

    Route::post(
        'online-stores/{storeId}/publish',
        [OnlineStoreController::class, 'publish']
    );
});
