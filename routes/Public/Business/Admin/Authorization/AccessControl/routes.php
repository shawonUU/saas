<?php
use Illuminate\Support\Facades\Route;

Route::get('access-control',[\App\Http\Controllers\Public\Business\Admin\Authorization\AccessControl\AuthorizationController::class,'index'])->name('access-control.index');
Route::get('access-control/{memberId}',[\App\Http\Controllers\Public\Business\Admin\Authorization\AccessControl\AuthorizationController::class,'show'])->name('access-control.show');
Route::post('access-control/store',[\App\Http\Controllers\Public\Business\Admin\Authorization\AccessControl\AuthorizationController::class,'store'])->name('access-control.store');
