<?php

use App\Http\Controllers\Public\Business\Admin\Restaurant\KitchenDisplay\KitchenTickets\KitchenTicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('kitchen-display/kitchen-tickets')->name('kitchen-display.kitchen-tickets.')->group(function (): void {
    Route::get('/', [KitchenTicketController::class, 'index'])->name('index');
    Route::get('data', [KitchenTicketController::class, 'data'])->name('data');
    Route::post('{order}/items/{item}', [KitchenTicketController::class, 'update'])->whereNumber(['order', 'item'])->name('update');
});
