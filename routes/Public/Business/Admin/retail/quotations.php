<?php
use App\Http\Controllers\Public\Business\Admin\Retail\QuotationController;
Route::get('sales/quotations/quotation-list',[QuotationController::class,'index'])->name('quotations.index');
Route::get('sales/quotations/new-quotation',[QuotationController::class,'create'])->name('quotations.create');
Route::post('sales/quotations/new-quotation',[QuotationController::class,'store'])->name('quotations.store');
Route::post('sales/quotations/quotation-list/{record}/convert',[QuotationController::class,'convert'])->whereNumber('record')->name('quotations.convert');
