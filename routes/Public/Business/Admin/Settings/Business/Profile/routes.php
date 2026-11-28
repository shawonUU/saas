<?php
use App\Http\Controllers\Public\Business\Admin\Settings\Business\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/business/profile/password', [ProfileController::class, 'password'])
    ->middleware('throttle:5,1')->name('business.profile.password');
