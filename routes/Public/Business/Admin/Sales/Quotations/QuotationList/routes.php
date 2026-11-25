<?php
use Illuminate\Support\Facades\Route;

Route::get('quotations/quotation-list',[\App\Http\Controllers\Public\Business\Admin\Sales\Quotations\QuotationController::class,'index'])->name('quotations.quotation-list.index');
Route::post('quotations/quotation-list/{record}/convert',[\App\Http\Controllers\Public\Business\Admin\Sales\Quotations\QuotationController::class,'convert'])->whereNumber('record')->name('quotations.quotation-list.convert');
