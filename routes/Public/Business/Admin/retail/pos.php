<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\Business\Admin\Retail\PosController;
Route::get('pos/counter/new-sale',[PosController::class,'index'])->name('pos.index');
Route::post('pos/counter/new-sale',[PosController::class,'store'])->name('pos.store');
