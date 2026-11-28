<?php
use App\Http\Controllers\Public\Business\Admin\Settings\Business\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('business/notifications')->name('business.notifications.')->group(function () {
    Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
    Route::post('/read-all', [NotificationController::class, 'readAll'])->middleware('throttle:60,1')->name('read-all');
    Route::post('/{notification}/read', [NotificationController::class, 'read'])
        ->whereUuid('notification')->middleware('throttle:60,1')->name('read');
});
