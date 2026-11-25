<?php
use Illuminate\Support\Facades\Route;

Route::get('receivables/collections',[\App\Http\Controllers\Public\Business\Admin\Finance\PaymentController::class,'index'])->defaults('kind','customer')->name('receivables.collections.index');
Route::post('receivables/collections',[\App\Http\Controllers\Public\Business\Admin\Finance\PaymentController::class,'store'])->defaults('kind','customer')->name('receivables.collections.store');
