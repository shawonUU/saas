<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Dashboard\DashboardController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile\ProfileController;
use App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Orders\OrdersController;
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
Route::get('/account/profile',[ProfileController::class,'index'])->name('profile');
Route::put('/account/profile',[ProfileController::class,'update'])->name('profile.update');
Route::put('/account/password',[ProfileController::class,'password'])->middleware('throttle:5,1')->name('profile.password');
Route::get('/account/orders',[OrdersController::class,'index'])->name('orders');
Route::get('/account/orders/{order}',[OrdersController::class,'show'])->whereNumber('order')->name('orders.show');
Route::post('/account/orders/{order}/return',[OrdersController::class,'requestReturn'])->whereNumber('order')->middleware('throttle:10,1')->name('orders.return');
Route::get('/account/returns',[OrdersController::class,'returns'])->name('returns');

Route::get('/account/addresses',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile\AddressController::class,'index'])->name('addresses');
Route::post('/account/addresses',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile\AddressController::class,'store'])->middleware('throttle:15,1')->name('addresses.store');
Route::put('/account/addresses/{address}',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile\AddressController::class,'update'])->whereNumber('address')->name('addresses.update');
Route::delete('/account/addresses/{address}',[\App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile\AddressController::class,'destroy'])->whereNumber('address')->name('addresses.destroy');
Route::get('/account/orders/{order}/receipt',[OrdersController::class,'receipt'])->whereNumber('order')->name('orders.receipt');

Route::post('/account/orders/{order}/cancel-request',[OrdersController::class,'cancelRequest'])->whereNumber('order')->middleware('throttle:5,1')->name('orders.cancel-request');
Route::get('/account/orders/{order}/buy-again',[OrdersController::class,'buyAgain'])->whereNumber('order')->name('orders.buy-again');
