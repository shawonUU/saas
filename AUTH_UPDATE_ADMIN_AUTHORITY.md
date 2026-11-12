# Updated SaaS authentication: admin.saas.test is Authority

The old `saas.test/authority` route has been removed. `admin.saas.test` is now the Authority portal. `admin` is a reserved shop code checked in the registration controller, Shop model and ResolveShop middleware. Do not overwrite `.env` or your database.

## Updated portal URLs

- `admin.saas.test/login` — Authority login only.
- `admin.saas.test/dashboard` — Authority dashboard.
- `saas.test/login` and `/register` — SaaS users.
- `admin.rohimstore.saas.test/login` — Shop owner / staff.
- `rohimstore.saas.test/login` and `/register` — Customers.

Use `SAAS_DOMAIN=saas.test` for production (`saas.test` on local). Keep `SESSION_DOMAIN=null` to prevent sharing login cookies across subdomains.

## Full changed files


### `config/saas.php`

```php
<?php
return [
    'domain' => env('SAAS_DOMAIN', 'saas.test'),
    'reserved_subdomains' => ['admin', 'authority', 'www', 'api', 'mail', 'support'],
];
```


### `routes/web.php`

```php
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
```


### `app/Models/Shop.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
class Shop extends Model
{
    protected $fillable = ['owner_id', 'category_id', 'name', 'code', 'address', 'phone', 'email', 'domain', 'approval_status'];
    protected static function booted(): void
    {
        static::saving(function (self $shop) {
            if (in_array(strtolower((string) $shop->code), config('saas.reserved_subdomains', []), true)) {
                throw ValidationException::withMessages(['code' => 'This subdomain is reserved. Please choose another name.']);
            }
        });
    }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function category() { return $this->belongsTo(ShopCategory::class, 'category_id'); }
    public function admins() { return $this->hasMany(BusinessUser::class); }
    public function customers() { return $this->hasMany(ShopCustomer::class); }
}
```


### `app/Http/Middleware/ResolveShop.php`

```php
<?php
namespace App\Http\Middleware;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class ResolveShop
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = strtolower((string) $request->route('shop'));
        abort_if(in_array($code, config('saas.reserved_subdomains', []), true), 404);
        $shop = Shop::where('code', $code)->where('approval_status', 'Approved')->firstOrFail();
        $request->attributes->set('current_shop', $shop);
        return $next($request);
    }
}
```


### `app/Http/Controllers/Saas/OwnerPortal/ShopRegistration/ShopRegistrationController.php`

```php
<?php
namespace App\Http\Controllers\Saas\OwnerPortal\ShopRegistration;
use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ShopRegistrationController extends Controller
{
    public function create()
    {
        return view('portals.create-shop', ['categories' => ShopCategory::where('approval_status', 'Approved')->orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z][a-z0-9-]*$/', Rule::notIn(config('saas.reserved_subdomains', [])), 'unique:shops,code'],
            'category_id' => ['required', Rule::exists('shop_categories', 'id')->where('approval_status', 'Approved')],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $owner = Auth::guard('web')->user();
        DB::transaction(function () use ($data, $owner) {
            $shop = Shop::create([
                'owner_id' => $owner->id, 'category_id' => $data['category_id'],
                'name' => trim($data['name']), 'code' => $data['code'], 'approval_status' => 'Approved',
            ]);
            BusinessUser::create([
                'shop_id' => $shop->id, 'user_id' => $owner->id, 'role' => 'owner',
                'name' => $owner->name, 'email' => $owner->email,
                'code' => 'BU'.Str::upper(Str::random(12)),
                'password' => filled($data['admin_password'] ?? null) ? $data['admin_password'] : $owner->password,
                'approval_status' => 'Approved',
            ]);
        });
        return redirect()->route('saas.dashboard')->with('success', 'Shop created. You can now log in to its admin subdomain.');
    }
}
```


### `resources/views/portals/create-shop.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card auth" style="max-width:560px">
    <h1 class="title">{{ t('Create shop', 'শপ তৈরি করুন') }}</h1>
    <p class="sub">{{ t('This will also create your shop owner login.', 'এর সঙ্গে আপনার শপ ওনারের লগইনও তৈরি হবে।') }}</p>
    <form method="post" action="{{ route('saas.shops.store') }}">
        @csrf
        <label for="name">{{ t('Shop name', 'শপের নাম') }}</label><input id="name" name="name" value="{{ old('name') }}" required>
        <label for="code">{{ t('Subdomain (lowercase, no spaces)', 'সাবডোমেইন (ছোট হাতের অক্ষর, স্পেস ছাড়া)') }}</label><input id="code" name="code" value="{{ old('code') }}" pattern="[a-z][a-z0-9-]{2,39}" placeholder="rohimstore" required>
        <p class="small">{{ t('Example', 'উদাহরণ') }}: rohimstore.{{ config('saas.domain') }} · admin.rohimstore.{{ config('saas.domain') }}</p>
        <p class="small">{{ t('Reserved: admin, authority, www, api, mail, support. admin.saas.test is reserved for SaaS Authority only.', 'সংরক্ষিত: admin, authority, www, api, mail, support। admin.saas.test শুধু SaaS Authority-এর জন্য সংরক্ষিত।') }}</p>
        <label for="category_id">{{ t('Shop category', 'শপের ক্যাটাগরি') }}</label>
        <select id="category_id" name="category_id" required><option value="">{{ t('Select category', 'ক্যাটাগরি বাছুন') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <label for="admin_password">{{ t('Separate admin password (optional)', 'আলাদা অ্যাডমিন পাসওয়ার্ড (ঐচ্ছিক)') }}</label><input id="admin_password" name="admin_password" type="password" minlength="8" autocomplete="new-password">
        <label for="admin_password_confirmation">{{ t('Confirm admin password', 'অ্যাডমিন পাসওয়ার্ড নিশ্চিত করুন') }}</label><input id="admin_password_confirmation" name="admin_password_confirmation" type="password" minlength="8" autocomplete="new-password">
        <p class="small">{{ t('Leave blank to initially use your SaaS account password. Logins remain separate.', 'খালি রাখলে শুরুতে SaaS অ্যাকাউন্টের পাসওয়ার্ড ব্যবহার হবে। লগইন আলাদাই থাকবে।') }}</p>
        <button type="submit" class="btn wide">{{ t('Create shop', 'শপ তৈরি করুন') }}</button>
    </form>
    <p class="hint"><a href="{{ route('saas.dashboard') }}">{{ t('Back to dashboard', 'ড্যাশবোর্ডে ফিরুন') }}</a></p>
</div>
@endsection
```


### `tests/Feature/PortalAuthenticationTest.php`

```php
<?php
namespace Tests\Feature;
use App\Models\AuthorityUser;
use App\Models\BusinessUser;
use App\Models\Shop;
use App\Models\ShopCategory;
use App\Models\ShopCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PortalAuthenticationTest extends TestCase
{
    use RefreshDatabase;
    private function shop(string $code): Shop
    {
        $category = ShopCategory::firstOrCreate(['code' => 'general'], ['name' => 'General', 'approval_status' => 'Approved']);
        $owner = User::create(['name' => 'Owner', 'code' => 'U-'.$code, 'email' => $code.'@owner.test', 'password' => 'password123', 'approval_status' => 'Approved']);
        return Shop::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => $code, 'code' => $code, 'approval_status' => 'Approved']);
    }
    public function test_saas_registration_does_not_authenticate_authority(): void
    {
        $this->post('http://saas.test/register', [
            'name' => 'New User', 'email' => 'user@example.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('http://saas.test/dashboard');
        $this->assertAuthenticated('web');
        $this->assertGuest('authority');
        $this->get('http://admin.saas.test/dashboard')->assertRedirect('http://admin.saas.test/login');
        $this->get('http://saas.test/authority/login')->assertNotFound();
    }
    public function test_admin_subdomain_is_reserved_for_authority(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U007', 'email' => 'owner7@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'general', 'name' => 'General', 'approval_status' => 'Approved']);
        $this->actingAs($owner, 'web')->post('http://saas.test/shops', [
            'name' => 'Fake Authority', 'code' => 'admin', 'category_id' => $category->id,
        ])->assertSessionHasErrors('code');
        $this->assertDatabaseMissing('shops', ['code' => 'admin']);
        $this->get('http://admin.saas.test/login')->assertOk();
        $this->get('http://admin.saas.test/register')->assertNotFound();
    }
    public function test_only_authority_can_log_in_to_admin_saas_host(): void
    {
        AuthorityUser::create(['name' => 'Authority', 'code' => 'A001', 'email' => 'hq@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $this->post('http://admin.saas.test/login', [
            'email' => 'hq@example.com', 'password' => 'password123',
        ])->assertRedirect('http://admin.saas.test/dashboard');
        $this->assertAuthenticated('authority');
        $this->assertGuest('web');
    }
    public function test_shop_model_cannot_save_reserved_subdomain(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U008', 'email' => 'owner8@example.com', 'password' => 'password123', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'general', 'name' => 'General', 'approval_status' => 'Approved']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        Shop::create(['owner_id' => $owner->id, 'category_id' => $category->id, 'name' => 'Fake Admin', 'code' => 'admin', 'approval_status' => 'Approved']);
    }
    public function test_shop_employee_cannot_access_another_shops_admin(): void
    {
        $a = $this->shop('rohimstore');
        $this->shop('othershop');
        $employee = BusinessUser::create([
            'shop_id' => $a->id, 'name' => 'Employee', 'email' => 'employee@example.com',
            'code' => 'EMP01', 'role' => 'employee', 'password' => 'password123', 'approval_status' => 'Approved',
        ]);
        $this->actingAs($employee, 'shop_admin');
        $this->get('http://admin.rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://admin.othershop.saas.test/dashboard')->assertForbidden();
    }
    public function test_customer_cannot_access_another_shops_storefront_account(): void
    {
        $a = $this->shop('rohimstore');
        $this->shop('othershop');
        $customer = ShopCustomer::create([
            'shop_id' => $a->id, 'name' => 'Customer', 'email' => 'customer@example.com',
            'code' => 'C01', 'password' => 'password123', 'approval_status' => 'Approved',
        ]);
        $this->actingAs($customer, 'shop_customer');
        $this->get('http://rohimstore.saas.test/dashboard')->assertOk();
        $this->get('http://othershop.saas.test/dashboard')->assertForbidden();
        $this->get('http://admin.rohimstore.saas.test/dashboard')->assertRedirect('http://admin.rohimstore.saas.test/login');
    }
    public function test_shop_registration_creates_independent_owner_login(): void
    {
        $owner = User::create(['name' => 'Owner', 'code' => 'U123', 'email' => 'owner@example.com', 'password' => 'rootpassword1', 'approval_status' => 'Approved']);
        $category = ShopCategory::create(['code' => 'retail', 'name' => 'Retail', 'approval_status' => 'Approved']);
        $this->actingAs($owner, 'web')->post('http://saas.test/shops', [
            'name' => 'Rohim Store', 'code' => 'rohimstore', 'category_id' => $category->id,
            'admin_password' => 'shopsecret1', 'admin_password_confirmation' => 'shopsecret1',
        ])->assertRedirect('http://saas.test/dashboard');
        $this->assertDatabaseHas('shops', ['code' => 'rohimstore', 'owner_id' => $owner->id]);
        $this->assertDatabaseHas('business_users', ['email' => 'owner@example.com', 'role' => 'owner']);
        $this->post('http://admin.rohimstore.saas.test/login', ['email' => 'owner@example.com', 'password' => 'rootpassword1'])->assertSessionHasErrors('email');
        $this->post('http://admin.rohimstore.saas.test/login', ['email' => 'owner@example.com', 'password' => 'shopsecret1'])->assertRedirect('http://admin.rohimstore.saas.test/dashboard');
    }
}
```
