<?php
use App\Http\Controllers\Saas\Auth\AuthorityAuthController;
use App\Http\Controllers\Saas\Auth\OwnerAuthController;
use App\Http\Controllers\Saas\OwnerPortal\ShopRegistration\ShopRegistrationController;
use App\Http\Controllers\Store\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Store\Public\Auth\CustomerAuthController;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$domain = config('saas.domain');
// Reserve admin.saas.test for platform authority before resolving shop domains.
Route::domain('admin.'.$domain)->name('authority.')->group(function () {
    Route::get('/', fn () => redirect()->route('authority.login'))->name('home');
    Route::get('/login', [AuthorityAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthorityAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('portal.auth:authority')->group(function () {
        Route::get('/dashboard', fn () => view('portals.dashboard', [
            'title' => 'SaaS Authority', 'user' => Auth::guard('authority')->user(),
            'subtitle' => t('Platform authority dashboard', 'প্ল্যাটফর্ম অথরিটি ড্যাশবোর্ড'),
            'logoutUrl' => route('authority.logout'), 'links' => [],
        ]))->name('dashboard');
        Route::post('/logout', [AuthorityAuthController::class, 'logout'])->name('logout');
    });
});
// Shop admin portals and the wildcard customer storefront are kept independent.
Route::domain('admin.{shop}.'.$domain)->middleware('shop.resolve')->name('store.admin.')->group(function () {
    Route::get('/', fn (Request $request) => redirect()->route('store.admin.login', ['shop' => $request->route('shop')]))->name('home');
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::middleware('portal.auth:shop_admin')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $shop = $request->attributes->get('current_shop');
            return view('portals.dashboard', [
                'title' => $shop->name.' · Admin', 'user' => Auth::guard('shop_admin')->user(),
                'subtitle' => t('Shop administration', 'শপ অ্যাডমিন প্যানেল'),
                'logoutUrl' => route('store.admin.logout', ['shop' => $shop->code]),
                'links' => [[t('Visit storefront', 'স্টোরফ্রন্ট দেখুন'), route('store.customer.home', ['shop' => $shop->code])]],
            ]);
        })->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
Route::domain('{shop}.'.$domain)->middleware('shop.resolve')->name('store.customer.')->group(function () {
    Route::get('/', function (Request $request) {
        $shop = $request->attributes->get('current_shop');
        return view('portals.landing', [
            'title' => $shop->name, 'description' => t('Welcome to our store.', 'আমাদের স্টোরে স্বাগতম।'),
            'loginUrl' => route('store.customer.login', ['shop' => $shop->code]),
            'registerUrl' => route('store.customer.register', ['shop' => $shop->code]),
            'dashboardUrl' => route('store.customer.dashboard', ['shop' => $shop->code]),
            'guard' => 'shop_customer',
        ]);
    })->name('home');
    Route::get('/login', [CustomerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [CustomerAuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
    Route::middleware('portal.auth:shop_customer')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $shop = $request->attributes->get('current_shop');
            return view('portals.dashboard', [
                'title' => $shop->name.' · Customer', 'user' => Auth::guard('shop_customer')->user(),
                'subtitle' => t('My customer account', 'আমার কাস্টমার অ্যাকাউন্ট'),
                'logoutUrl' => route('store.customer.logout', ['shop' => $shop->code]),
                'links' => [[t('Store home', 'স্টোর হোম'), route('store.customer.home', ['shop' => $shop->code])]],
            ]);
        })->name('dashboard');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});
Route::domain($domain)->group(function () {
    Route::get('/', function () {
        return view('portals.landing', [
            'title' => 'SaaS Platform', 'description' => t('Manage your shops in one place.', 'এক জায়গায় আপনার শপ পরিচালনা করুন।'),
            'loginUrl' => route('saas.login'), 'registerUrl' => route('saas.register'),
            'dashboardUrl' => route('saas.dashboard'), 'guard' => 'web',
        ]);
    })->name('saas.home');
    Route::name('saas.')->group(function () {
        Route::get('/login', [OwnerAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [OwnerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
        Route::get('/register', [OwnerAuthController::class, 'registerForm'])->name('register');
        Route::post('/register', [OwnerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
        Route::middleware('portal.auth:web')->group(function () {
            Route::get('/dashboard', function () {
                return view('portals.owner-dashboard', [
                    'user' => Auth::guard('web')->user(),
                    'shops' => Shop::where('owner_id', Auth::guard('web')->id())->latest()->get(),
                ]);
            })->name('dashboard');
            Route::get('/shops/create', [ShopRegistrationController::class, 'create'])->name('shops.create');
            Route::post('/shops', [ShopRegistrationController::class, 'store'])->name('shops.store');
            Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');
        });
    });
});
// Language preference is scoped by the browser's host-only session cookie.
Route::post('/language', function (Request $request) {
    $data = $request->validate(['lang' => ['required', 'in:en,bn']]);
    $request->session()->put('portal_lang', $data['lang']);
    return back();
})->name('language.set');
