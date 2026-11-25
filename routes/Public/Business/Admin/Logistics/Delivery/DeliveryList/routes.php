<?php
use Illuminate\Support\Facades\Route;

Route::get('delivery/delivery-list',[\App\Http\Controllers\Public\Business\Admin\Logistics\Delivery\DeliveryList\DeliveryController::class,'index'])->name('delivery.delivery-list.index');
Route::put('delivery/delivery-list/{record}',[\App\Http\Controllers\Public\Business\Admin\Logistics\Delivery\DeliveryList\DeliveryController::class,'update'])->whereNumber('record')->name('delivery.delivery-list.update');
