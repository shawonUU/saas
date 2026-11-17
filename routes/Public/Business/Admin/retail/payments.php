<?php
use App\Http\Controllers\Public\Business\Admin\Retail\PaymentController;
Route::get('finance/receivables/collections',[PaymentController::class,'index'])->defaults('kind','customer')->name('payments.customers');
Route::post('finance/receivables/collections',[PaymentController::class,'store'])->defaults('kind','customer')->name('payments.customer.store');
Route::get('finance/payables/vendor-payments',[PaymentController::class,'index'])->defaults('kind','supplier')->name('payments.suppliers');
Route::post('finance/payables/vendor-payments',[PaymentController::class,'store'])->defaults('kind','supplier')->name('payments.supplier.store');
Route::post('sales/returns/refund',[PaymentController::class,'refund'])->name('returns.refund');
