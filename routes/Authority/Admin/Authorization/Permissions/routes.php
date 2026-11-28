<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authority\Authorization\Permissions\PermissionController;
Route::get('permissions', [PermissionController::class,'index'])->name('permissions.index');
Route::post('permissions', [PermissionController::class,'store'])->name('permissions.store');
Route::put('permissions/{record}', [PermissionController::class,'update'])->whereNumber('record')->name('permissions.update');
Route::delete('permissions/{record}', [PermissionController::class,'destroy'])->whereNumber('record')->name('permissions.destroy');
