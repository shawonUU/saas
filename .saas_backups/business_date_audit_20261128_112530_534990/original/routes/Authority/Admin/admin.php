<?php
use Illuminate\Support\Facades\Route;
$domain = config('saas.domain');
Route::domain('admin.'.$domain)->middleware(['portal.auth:authority','auth.session'])
    ->prefix('authorization')->name('authority.authorization.')->group(function () {
        require __DIR__.'/Authorization/Permissions/routes.php';
    });
