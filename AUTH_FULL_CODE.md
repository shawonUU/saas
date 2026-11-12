# Laravel SaaS authentication — all updated files
Use these exact paths in the original Laravel project. Setup: `AUTH_SETUP.md`. No `.env` secrets or database dumps are included. Authority runs on `admin.<SAAS_DOMAIN>`, not the old `/authority` path.

## `config/saas.php`

```php
<?php
return [
    'domain' => env('SAAS_DOMAIN', 'saas.test'),
    'reserved_subdomains' => ['admin', 'authority', 'www', 'api', 'mail', 'support'],
];
```

## `config/auth.php`

```php
<?php
use App\Models\User;
use App\Models\AuthorityUser;
use App\Models\BusinessUser;
use App\Models\ShopCustomer;
return [
    'defaults' => ['guard' => 'web', 'passwords' => 'users'],
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'authority' => ['driver' => 'session', 'provider' => 'authority_users'],
        'shop_admin' => ['driver' => 'session', 'provider' => 'business_users'],
        'shop_customer' => ['driver' => 'session', 'provider' => 'shop_customers'],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => User::class],
        'authority_users' => ['driver' => 'eloquent', 'model' => AuthorityUser::class],
        'business_users' => ['driver' => 'eloquent', 'model' => BusinessUser::class],
        'shop_customers' => ['driver' => 'eloquent', 'model' => ShopCustomer::class],
    ],
    'passwords' => [
        'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],
    'password_timeout' => 10800,
];
```

## `bootstrap/app.php`

```php
<?php
use App\Http\Middleware\PortalAuth;
use App\Http\Middleware\ResolveShop;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['shop.resolve' => ResolveShop::class, 'portal.auth' => PortalAuth::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
```

## `app/Helpers/helpers.php`

```php
<?php
if (!function_exists('approvalStatus')) {
    function approvalStatus(): array { return ['Approved', 'Pending', 'Rejected', 'Cancelled']; }
}
if (!function_exists('t')) {
    function t(string $english, string $bangla): string {
        return session('portal_lang', 'en') === 'bn' ? $bangla : $english;
    }
}
```

## `app/Models/User.php`

```php
<?php
namespace App\Models;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'code', 'email', 'password', 'approval_status'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function shops() { return $this->hasMany(Shop::class, 'owner_id'); }
}
```

## `app/Models/AuthorityUser.php`

```php
<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class AuthorityUser extends Authenticatable
{
    use Notifiable;
    protected $table = 'authority_users';
    protected $fillable = ['name', 'code', 'email', 'password', 'approval_status'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
}
```

## `app/Models/BusinessUser.php`

```php
<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class BusinessUser extends Authenticatable
{
    use Notifiable;
    protected $table = 'business_users';
    protected $fillable = ['shop_id', 'user_id', 'name', 'code', 'email', 'password', 'approval_status', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function shop() { return $this->belongsTo(Shop::class); }
}
```

## `app/Models/ShopCustomer.php`

```php
<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class ShopCustomer extends Authenticatable
{
    use Notifiable;
    protected $table = 'shop_customers';
    protected $fillable = ['shop_id', 'name', 'code', 'email', 'password', 'approval_status'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function shop() { return $this->belongsTo(Shop::class); }
}
```

## `app/Models/Shop.php`

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

## `app/Models/ShopCategory.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShopCategory extends Model
{
    protected $fillable = ['name', 'code', 'approval_status'];
}
```

## `app/Http/Middleware/ResolveShop.php`

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

## `app/Http/Middleware/PortalAuth.php`

```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class PortalAuth
{
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $login = match ($guard) {
            'authority' => 'authority.login',
            'web' => 'saas.login',
            'shop_admin' => 'store.admin.login',
            'shop_customer' => 'store.customer.login',
            default => abort(403),
        };
        $params = in_array($guard, ['shop_admin', 'shop_customer']) ? ['shop' => $request->route('shop')] : [];
        $user = Auth::guard($guard)->user();
        if (!$user) return redirect()->route($login, $params);
        if ($user->approval_status !== 'Approved') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route($login, $params)->withErrors(['email' => 'Account is not active.']);
        }
        if (in_array($guard, ['shop_admin', 'shop_customer'])) {
            $shop = $request->attributes->get('current_shop');
            abort_unless($shop && (int) $user->shop_id === (int) $shop->id, 403);
        }
        Auth::shouldUse($guard);
        return $next($request);
    }
}
```

## `app/Http/Controllers/Saas/Auth/OwnerAuthController.php`

```php
<?php
namespace App\Http\Controllers\Saas\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class OwnerAuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::guard('web')->check()) return redirect()->route('saas.dashboard');
        return view('portals.login', ['title' => 'SaaS Account', 'action' => route('saas.login.post'), 'registerUrl' => route('saas.register')]);
    }
    public function registerForm()
    {
        if (Auth::guard('web')->check()) return redirect()->route('saas.dashboard');
        return view('portals.register', ['title' => 'Create SaaS Account', 'action' => route('saas.register.post'), 'loginUrl' => route('saas.login')]);
    }
    public function register(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create([
            'name' => trim($data['name']), 'email' => strtolower(trim($data['email'])),
            'code' => 'SU'.Str::upper(Str::random(12)), 'password' => $data['password'], 'approval_status' => 'Approved',
        ]);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        return redirect()->route('saas.dashboard');
    }
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $data['email'] = strtolower(trim($data['email']));
        if (Auth::guard('web')->attempt([...$data, 'approval_status' => 'Approved'])) {
            $request->session()->regenerate();
            return redirect()->route('saas.dashboard');
        }
        return back()->withErrors(['email' => 'Invalid email, password or inactive account.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('saas.login');
    }
}
```

## `app/Http/Controllers/Saas/Auth/AuthorityAuthController.php`

```php
<?php
namespace App\Http\Controllers\Saas\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthorityAuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::guard('authority')->check()) return redirect()->route('authority.dashboard');
        return view('portals.login', ['title' => 'SaaS Authority', 'action' => route('authority.login.post'), 'registerUrl' => null]);
    }
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $data['email'] = strtolower(trim($data['email']));
        if (Auth::guard('authority')->attempt([...$data, 'approval_status' => 'Approved'])) {
            $request->session()->regenerate();
            return redirect()->route('authority.dashboard');
        }
        return back()->withErrors(['email' => 'Invalid email, password or inactive account.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('authority')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('authority.login');
    }
}
```

## `app/Http/Controllers/Saas/OwnerPortal/ShopRegistration/ShopRegistrationController.php`

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

## `app/Http/Controllers/Store/Admin/Auth/AdminAuthController.php`

```php
<?php
namespace App\Http\Controllers\Store\Admin\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AdminAuthController extends Controller
{
    public function loginForm(Request $request)
    {
        $shop = $request->attributes->get('current_shop');
        if (Auth::guard('shop_admin')->check() && (int) Auth::guard('shop_admin')->user()->shop_id === (int) $shop->id) return redirect()->route('store.admin.dashboard', ['shop' => $shop->code]);
        return view('portals.login', ['title' => $shop->name.' · Admin', 'action' => route('store.admin.login.post', ['shop' => $shop->code]), 'registerUrl' => null]);
    }
    public function login(Request $request)
    {
        $shop = $request->attributes->get('current_shop');
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (Auth::guard('shop_admin')->attempt([
            'email' => strtolower(trim($data['email'])), 'password' => $data['password'],
            'shop_id' => $shop->id, 'approval_status' => 'Approved',
        ])) {
            $request->session()->regenerate();
            return redirect()->route('store.admin.dashboard', ['shop' => $shop->code]);
        }
        return back()->withErrors(['email' => 'Invalid credentials for this shop.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('shop_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('store.admin.login', ['shop' => $request->route('shop')]);
    }
}
```

## `app/Http/Controllers/Store/Public/Auth/CustomerAuthController.php`

```php
<?php
namespace App\Http\Controllers\Store\Public\Auth;
use App\Http\Controllers\Controller;
use App\Models\ShopCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class CustomerAuthController extends Controller
{
    public function loginForm(Request $request)
    {
        $shop = $request->attributes->get('current_shop');
        if (Auth::guard('shop_customer')->check() && (int) Auth::guard('shop_customer')->user()->shop_id === (int) $shop->id) return redirect()->route('store.customer.dashboard', ['shop' => $shop->code]);
        return view('portals.login', ['title' => $shop->name.' · Customer', 'action' => route('store.customer.login.post', ['shop' => $shop->code]), 'registerUrl' => route('store.customer.register', ['shop' => $shop->code])]);
    }
    public function registerForm(Request $request)
    {
        $shop = $request->attributes->get('current_shop');
        if (Auth::guard('shop_customer')->check() && (int) Auth::guard('shop_customer')->user()->shop_id === (int) $shop->id) return redirect()->route('store.customer.dashboard', ['shop' => $shop->code]);
        return view('portals.register', ['title' => $shop->name.' · Register', 'action' => route('store.customer.register.post', ['shop' => $shop->code]), 'loginUrl' => route('store.customer.login', ['shop' => $shop->code])]);
    }
    public function register(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $shop = $request->attributes->get('current_shop');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('shop_customers', 'email')->where('shop_id', $shop->id)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $customer = ShopCustomer::create([
            'shop_id' => $shop->id, 'name' => trim($data['name']), 'email' => strtolower(trim($data['email'])),
            'code' => 'SC'.Str::upper(Str::random(12)), 'password' => $data['password'], 'approval_status' => 'Approved',
        ]);
        Auth::guard('shop_customer')->login($customer);
        $request->session()->regenerate();
        return redirect()->route('store.customer.dashboard', ['shop' => $shop->code]);
    }
    public function login(Request $request)
    {
        $shop = $request->attributes->get('current_shop');
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (Auth::guard('shop_customer')->attempt([
            'email' => strtolower(trim($data['email'])), 'password' => $data['password'],
            'shop_id' => $shop->id, 'approval_status' => 'Approved',
        ])) {
            $request->session()->regenerate();
            return redirect()->route('store.customer.dashboard', ['shop' => $shop->code]);
        }
        return back()->withErrors(['email' => 'Invalid credentials for this shop.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('shop_customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('store.customer.login', ['shop' => $request->route('shop')]);
    }
}
```

## `routes/web.php`

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

## `routes/console.php`

```php
<?php
use App\Models\AuthorityUser;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
Artisan::command('authority:create', function () {
    $name = trim((string) $this->ask('Authority name'));
    $email = strtolower(trim((string) $this->ask('Authority email')));
    $password = (string) $this->secret('Authority password (min 8 characters)');
    $validation = Validator::make(compact('name', 'email', 'password'), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:authority_users,email'],
        'password' => ['required', 'string', 'min:8'],
    ]);
    if ($validation->fails()) { $this->error($validation->errors()->first()); return; }
    AuthorityUser::create([
        'name' => $name, 'email' => $email,
        'code' => 'AU'.Str::upper(Str::random(12)), 'password' => $password,
        'approval_status' => 'Approved',
    ]);
    $this->info('Authority user created.');
})->purpose('Create an authority login account interactively');

Artisan::command('shop:employee {shopCode}', function () {
    $shop = \App\Models\Shop::where('code', $this->argument('shopCode'))->first();
    if (!$shop) { $this->error('Shop not found.'); return; }
    $name = trim((string) $this->ask('Employee name'));
    $email = strtolower(trim((string) $this->ask('Employee email')));
    $password = (string) $this->secret('Employee password (min 8 characters)');
    $validation = Validator::make(compact('name', 'email', 'password'), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('business_users', 'email')->where('shop_id', $shop->id)],
        'password' => ['required', 'string', 'min:8'],
    ]);
    if ($validation->fails()) { $this->error($validation->errors()->first()); return; }
    \App\Models\BusinessUser::create([
        'shop_id' => $shop->id, 'role' => 'employee', 'name' => $name, 'email' => $email,
        'code' => 'BU'.Str::upper(Str::random(12)), 'password' => $password,
        'approval_status' => 'Approved',
    ]);
    $this->info('Employee login created for '.$shop->code.'.');
})->purpose('Create an employee login for an existing shop interactively');
```

## `database/migrations/2026_11_11_170000_finalize_auth_schema.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Existing SQLite database was migrated before users.code and users.approval_status were added to the original migration.
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'code')) Schema::table('users', fn (Blueprint $table) => $table->string('code')->nullable());
            if (!Schema::hasColumn('users', 'approval_status')) Schema::table('users', fn (Blueprint $table) => $table->string('approval_status')->default('Pending'));
        }
        Schema::table('business_users', function (Blueprint $table) {
            $table->string('role', 20)->default('employee');
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->unique('code');
            $table->index('owner_id');
        });
    }
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropIndex(['owner_id']);
        });
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn(['role', 'user_id']);
        });
        // Do not remove legacy users columns: other environments may have them from the original migration.
    }
};
```

## `database/seeders/DatabaseSeeder.php`

```php
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ShopCategorySeeder::class);
    }
}
```

## `database/seeders/ShopCategorySeeder.php`

```php
<?php
namespace Database\Seeders;
use App\Models\ShopCategory;
use Illuminate\Database\Seeder;
class ShopCategorySeeder extends Seeder
{
    public function run(): void
    {
        ShopCategory::firstOrCreate(['code' => 'general'], ['name' => 'General', 'approval_status' => 'Approved']);
    }
}
```

## `resources/views/layouts/portal.blade.php`

```php
<!doctype html>
<html lang="{{ session('portal_lang', 'en') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SaaS' }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,-apple-system,Arial,sans-serif;background:#f5f7fb;color:#17233b}a{color:#3157d5;text-decoration:none}a:hover{text-decoration:underline}.nav{background:white;border-bottom:1px solid #e8ecf4;padding:17px max(20px,calc((100% - 1000px)/2));display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:800;color:#19284d;font-size:20px}.nav-right{display:flex;gap:15px;align-items:center}.lang{background:#f2f5ff;color:#2846aa;border:0;border-radius:9px;padding:8px 11px;cursor:pointer;font-weight:700}.container{width:min(100% - 36px,1000px);margin:44px auto}.card{background:#fff;border:1px solid #e8ecf3;border-radius:18px;box-shadow:0 14px 45px #2432570a;padding:30px}.auth{max-width:450px;margin:0 auto}.title{font-weight:800;letter-spacing:-.5px;font-size:27px;margin:0 0 8px}.sub{color:#778198;margin:0 0 25px;line-height:1.5}label{font-size:13px;font-weight:700;display:block;margin:17px 0 7px}input,select{width:100%;padding:12px 13px;border:1px solid #d9dfeb;border-radius:10px;font:inherit;color:#17233b;background:#fff}input:focus,select:focus{outline:2px solid #b5c4ff;border-color:#5873d8}.btn{background:#2e53d2;color:white;border:0;border-radius:10px;font-weight:700;padding:12px 17px;cursor:pointer;display:inline-block;text-align:center}.btn:hover{background:#2144bd;color:#fff;text-decoration:none}.btn-outline{background:#fff;color:#3456c1;border:1px solid #c5cde0}.btn-outline:hover{background:#f1f4fc;color:#1e3d9a}.wide{width:100%;margin-top:22px}.hint{font-size:13px;color:#7d879b;margin-top:17px}.alert{border-radius:11px;padding:12px 14px;margin:17px 0;font-size:14px}.error{color:#9e2432;background:#fff0f1;border:1px solid #ffd6dc}.success{color:#14683b;background:#e8fff1;border:1px solid #bbefd1}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}.shop{padding:20px;border:1px solid #e6ebf2;border-radius:14px;margin-top:16px}.shop h3{margin:0 0 8px}.small{font-size:13px;color:#778198}.inline{display:inline-block}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.footer{margin-top:50px;text-align:center;font-size:12px;color:#8c94a7}@media(max-width:600px){.nav{padding:16px 18px}.container{margin:24px auto}.card{padding:23px}.title{font-size:24px}}
    </style>
</head>
<body>
<header class="nav"><a class="brand" href="{{ url('/') }}">NexaBiz</a><div class="nav-right"><form action="{{ url('/language') }}" method="post">@csrf<input type="hidden" name="lang" value="{{ session('portal_lang', 'en') === 'en' ? 'bn' : 'en' }}"><button class="lang" type="submit">{{ session('portal_lang', 'en') === 'en' ? 'বাংলা' : 'EN' }}</button></form></div></header>
<main class="container">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
    @yield('content')
    <div class="footer">© {{ date('Y') }} NexaBiz</div>
</main>
</body></html>
```

## `resources/views/portals/login.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card auth">
    <h1 class="title">{{ $title }}</h1>
    <p class="sub">{{ t('Log in with your portal account.', 'আপনার পোর্টাল অ্যাকাউন্টে লগইন করুন।') }}</p>
    <form action="{{ $action }}" method="post">
        @csrf
        <label for="email">{{ t('Email address', 'ইমেইল ঠিকানা') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <label for="password">{{ t('Password', 'পাসওয়ার্ড') }}</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit" class="btn wide">{{ t('Log in', 'লগইন করুন') }}</button>
    </form>
    @if($registerUrl)<p class="hint">{{ t('New here?', 'নতুন ব্যবহারকারী?') }} <a href="{{ $registerUrl }}">{{ t('Create account', 'অ্যাকাউন্ট তৈরি করুন') }}</a></p>@endif
</div>
@endsection
```

## `resources/views/portals/register.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card auth">
    <h1 class="title">{{ $title }}</h1>
    <p class="sub">{{ t('Create your account in this portal.', 'এই পোর্টালে নতুন অ্যাকাউন্ট তৈরি করুন।') }}</p>
    <form action="{{ $action }}" method="post">
        @csrf
        <label for="name">{{ t('Full name', 'পুরো নাম') }}</label><input id="name" name="name" value="{{ old('name') }}" maxlength="255" required>
        <label for="email">{{ t('Email address', 'ইমেইল ঠিকানা') }}</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
        <label for="password">{{ t('Password (minimum 8 characters)', 'পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)') }}</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        <label for="password_confirmation">{{ t('Confirm password', 'পাসওয়ার্ড নিশ্চিত করুন') }}</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
        <button class="btn wide" type="submit">{{ t('Register', 'রেজিস্ট্রেশন') }}</button>
    </form>
    <p class="hint">{{ t('Have an account?', 'আগে থেকেই অ্যাকাউন্ট আছে?') }} <a href="{{ $loginUrl }}">{{ t('Log in', 'লগইন করুন') }}</a></p>
</div>
@endsection
```

## `resources/views/portals/landing.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card" style="padding:45px 32px">
    <h1 class="title">{{ $title }}</h1><p class="sub">{{ $description }}</p>
    <div class="actions">
        @if(Auth::guard($guard)->check())
            <a class="btn" href="{{ $dashboardUrl }}">{{ t('My dashboard', 'আমার ড্যাশবোর্ড') }}</a>
        @else
            <a class="btn" href="{{ $loginUrl }}">{{ t('Log in', 'লগইন') }}</a>
            <a class="btn btn-outline" href="{{ $registerUrl }}">{{ t('Register', 'রেজিস্ট্রেশন') }}</a>
        @endif
    </div>
</div>
@endsection
```

## `resources/views/portals/dashboard.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card">
    <h1 class="title">{{ $title }}</h1><p class="sub">{{ $subtitle }}</p>
    <h3>{{ t('Welcome', 'স্বাগতম') }}, {{ $user->name }}</h3>
    <p class="small">{{ $user->email }}</p>
    @if(isset($user->role))<p class="small">{{ t('Role', 'পদবি') }}: {{ ucfirst($user->role) }}</p>@endif
    <div class="actions">
        @foreach($links as $link)<a href="{{ $link[1] }}" class="btn btn-outline">{{ $link[0] }}</a>@endforeach
        <form method="post" action="{{ $logoutUrl }}">@csrf<button class="btn" type="submit">{{ t('Log out', 'লগআউট') }}</button></form>
    </div>
</div>
@endsection
```

## `resources/views/portals/owner-dashboard.blade.php`

```php
@extends('layouts.portal')
@section('content')
<div class="card">
    <h1 class="title">{{ t('My SaaS Dashboard', 'আমার SaaS ড্যাশবোর্ড') }}</h1>
    <p class="sub">{{ t('Hello', 'স্বাগতম') }}, {{ $user->name }} · {{ $user->email }}</p>
    <div class="actions"><a href="{{ route('saas.shops.create') }}" class="btn">{{ t('Create new shop', 'নতুন শপ তৈরি করুন') }}</a><form method="post" action="{{ route('saas.logout') }}">@csrf<button type="submit" class="btn btn-outline">{{ t('Log out', 'লগআউট') }}</button></form></div>
    <h2 style="margin:35px 0 12px">{{ t('My shops', 'আমার শপগুলো') }}</h2>
    @forelse($shops as $shop)
        <div class="shop"><h3>{{ $shop->name }}</h3>
            <div class="small">{{ $shop->code }} · {{ $shop->approval_status }}</div>
            <div class="actions">
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.admin.login', ['shop' => $shop->code]) }}">{{ t('Shop admin login', 'শপ অ্যাডমিন লগইন') }}</a>
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.customer.home', ['shop' => $shop->code]) }}">{{ t('Customer storefront', 'কাস্টমার স্টোর') }}</a>
            </div>
        </div>
    @empty<p class="small">{{ t('No shops yet. Create your first shop.', 'এখনো কোনো শপ নেই। প্রথম শপ তৈরি করুন।') }}</p>
    @endforelse
</div>
@endsection
```

## `resources/views/portals/create-shop.blade.php`

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

## `tests/Feature/PortalAuthenticationTest.php`

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

## `.env.example`

```text
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# SaaS base host without scheme or port; authority is admin.<domain>, shop admin is admin.<shop>.<domain>.
SAAS_DOMAIN=saas.test
```
