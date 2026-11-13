<?php
use App\Http\Controllers\Authority\Auth\AuthorityAuthController;

use App\Http\Controllers\Authority\FeatureAccessController;
use App\Http\Controllers\Authority\FeatureController;
use App\Http\Controllers\Business\Public\Auth\CustomerAuthController;
use App\Http\Controllers\Public\Auth\OwnerAuthController;
use App\Http\Controllers\Public\Business\Admin\WorkspaceController;
use App\Http\Controllers\Public\Business\Auth\AdminAuthController;
use App\Http\Controllers\Public\Business\BusinessRegistrationController;
use App\Http\Controllers\Public\HomeController;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


$domain = config('saas.domain');

require __DIR__.'/Authority/Auth/auth.php';
require __DIR__.'/Authority/Admin/admin.php';
require __DIR__.'/Public/public.php';
require __DIR__.'/Public/Auth/auth.php';
require __DIR__.'/Public/Business/business.php';
require __DIR__.'/Public/Business/Auth/auth.php';
require __DIR__.'/Public/Business/Admin/admin.php';
require __DIR__.'/Public/Business/public/public.php';
require __DIR__.'/Public/Business/public/Auth/auth.php';


Route::post('/language', function (Request $request) {
    $data = $request->validate(['lang' => ['required', 'in:en,bn']]);
    $request->session()->put('portal_lang', $data['lang']);
    return back();
})->name('language.set');
