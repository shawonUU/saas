<?php

use App\Http\Controllers\Public\Business\Admin\Sales\RestaurantPos\RestaurantPosController;
use Illuminate\Support\Facades\Route;

Route::prefix('restaurant-pos')->name('restaurant-pos.')->group(function (): void {
    Route::get('/', [RestaurantPosController::class, 'index'])->name('index');
    Route::get('tables', [RestaurantPosController::class, 'tables'])->name('tables');
    Route::post('quote', [RestaurantPosController::class, 'quote'])->name('quote');
    Route::post('orders', [RestaurantPosController::class, 'store'])->name('store');
    Route::get('orders/{order}', [RestaurantPosController::class, 'show'])->whereNumber('order')->name('show');
    Route::put('orders/{order}', [RestaurantPosController::class, 'update'])->whereNumber('order')->name('update');
    Route::post('orders/{order}/send', [RestaurantPosController::class, 'send'])->whereNumber('order')->name('send');
    Route::post('orders/{order}/checkout', [RestaurantPosController::class, 'checkout'])->whereNumber('order')->name('checkout');
    Route::post('orders/{order}/payment', [RestaurantPosController::class, 'pay'])->whereNumber('order')->name('pay');
    Route::post('orders/{order}/close', [RestaurantPosController::class, 'close'])->whereNumber('order')->name('close');
    Route::post('orders/{order}/cancel', [RestaurantPosController::class, 'cancel'])->whereNumber('order')->name('cancel');
    Route::post('orders/{order}/items/{item}/void', [RestaurantPosController::class, 'voidItem'])->whereNumber(['order', 'item'])->name('void');
    Route::post('orders/{order}/serve', [RestaurantPosController::class, 'serve'])->whereNumber('order')->name('serve');
    Route::get('orders/{order}/receipt', [RestaurantPosController::class, 'receipt'])->whereNumber('order')->name('receipt');
    Route::get('orders/{order}/kot', [RestaurantPosController::class, 'kot'])->whereNumber('order')->name('kot');
});
