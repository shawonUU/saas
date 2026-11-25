<?php
use Illuminate\Support\Facades\Route;

Route::get('quotations/new-quotation',[\App\Http\Controllers\Public\Business\Admin\Sales\Quotations\QuotationController::class,'create'])->name('quotations.new-quotation.create');
Route::post('quotations/new-quotation',[\App\Http\Controllers\Public\Business\Admin\Sales\Quotations\QuotationController::class,'store'])->name('quotations.new-quotation.store');
