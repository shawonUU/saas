<?php
use Illuminate\Support\Facades\Route;

Route::get('users',[\App\Http\Controllers\Public\Business\Admin\Authorization\Users\BusinessUserController::class,'index'])->name('users.index');
Route::post('users',[\App\Http\Controllers\Public\Business\Admin\Authorization\Users\BusinessUserController::class,'store'])->name('users.store');
Route::put('users/{memberId}',[\App\Http\Controllers\Public\Business\Admin\Authorization\Users\BusinessUserController::class,'update'])->name('users.update');
