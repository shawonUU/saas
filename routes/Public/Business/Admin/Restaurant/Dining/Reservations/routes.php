<?php

use App\Http\Controllers\Public\Business\Admin\Restaurant\Dining\Reservations\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('dining/reservations', [ReservationController::class, 'index'])->name('dining.reservations.index');
Route::post('dining/reservations', [ReservationController::class, 'store'])->name('dining.reservations.store');
Route::put('dining/reservations/{record}', [ReservationController::class, 'update'])->whereNumber('record')->name('dining.reservations.update');
Route::post('dining/reservations/{record}/cancel', [ReservationController::class, 'cancel'])->whereNumber('record')->name('dining.reservations.cancel');
