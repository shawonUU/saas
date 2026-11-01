<?php
namespace Modules\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Modules\Auth\App\Models\ShopAccount;
use Modules\Auth\App\Models\ShopCustomer;
use Modules\Business\App\Models\Business;

class AuthController extends Controller
{
    private function context(Request $request): array
    {
        // Do not accept a caller-selected tenant as the security authority.
        // The browser Origin identifies the SPA, including when the API lives on another host.
        $origin = $request->header('Origin');
        $host = $origin ? parse_url($origin, PHP_URL_HOST) : $request->getHost();
        $host = strtolower((string)$host);
        $base = strtolower((string)config('app.saas_base_domain', env('SAAS_BASE_DOMAIN', 'saas.com')));
        $local = strtolower((string)env('SAAS_LOCAL_DOMAIN', 'localtest.me'));
        foreach (array_unique([$base, $local]) as $domain) {
            if ($host === $domain || $host === 'www.'.$domain) return ['main', null];
            if (str_ends_with($host, '.'.$domain)) {
                $sub = substr($host, 0, -(strlen($domain) + 1));
                if (preg_match('/^admin\.([a-z0-9-]+)$/', $sub, $m)) return ['admin', $m[1]];
                if (preg_match('/^[a-z0-9-]+$/', $sub)) return ['customer', $sub];
            }
        }
        if (in_array($host, ['localhost', '127.0.0.1'], true)) return ['main', null];
        abort(400, 'Unrecognized application host.');
    }
    private function shop(string $slug): Business
    {
        return Business::where('slug', $slug)->where('status', 'Active')->firstOrFail();
    }
    private function throttled(Request $request, string $scope): void
    {
        $key = 'auth:'.sha1($scope.'|'.strtolower((string)$request->input('email')).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many attempts. Try again later.']);
        }
    }
    public function register(Request $request)
    {
        [$mode, $slug] = $this->context($request);
        $data = $request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255','password'=>'required|string|min:8|confirmed']);
        $data['email'] = strtolower($data['email']);
        if ($mode === 'admin') abort(403, 'Shop staff accounts must be invited by shop management.');
        if ($mode === 'customer') {
            $shop = $this->shop($slug);
            if (ShopCustomer::where('business_id',$shop->id)->where('email',$data['email'])->exists()) throw ValidationException::withMessages(['email'=>'Email already registered for this shop.']);
            $user = ShopCustomer::create(['business_id'=>$shop->id,'name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password']]);
        } else {
            if (User::where('email',$data['email'])->exists()) throw ValidationException::withMessages(['email'=>'Email already registered.']);
            $user = User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'account_type'=>'general_user']);
        }
        return response()->json(['message'=>'Registration successful.','user'=>$user],201);
    }
    public function login(Request $request)
    {
        [$mode, $slug] = $this->context($request);
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
        $data['email'] = strtolower($data['email']);
        $this->throttled($request, $mode.':'.$slug);
        $shop = $mode === 'main' ? null : $this->shop($slug);
        $model = $mode === 'main' ? User::class : ($mode === 'admin' ? ShopAccount::class : ShopCustomer::class);
        $query = $model::where('email',$data['email']);
        if ($shop) $query->where('business_id',$shop->id)->where('status','Active');
        $user = $query->first();
        if (!$user || !Hash::check($data['password'],$user->password)) {
            $key = 'auth:'.sha1($mode.':'.$slug.'|'.$data['email'].'|'.$request->ip());
            RateLimiter::hit($key, 60);
            return response()->json(['message'=>'Invalid email or password.'],401);
        }
        RateLimiter::clear('auth:'.sha1($mode.':'.$slug.'|'.$data['email'].'|'.$request->ip()));
        $scope = $mode === 'main' ? 'main' : $mode.':'.$shop->id;
        $token = $user->createToken($scope,[$scope],now()->addHours(12))->plainTextToken;
        return response()->json(['message'=>'Login successful.','token'=>$token,'user'=>$user,'business'=>$shop,'scope'=>$scope,'redirect_to'=>$mode==='main' ? ($user->account_type==='authority_user'?'/admin':'/dashboard') : '/dashboard']);
    }
    public function me(Request $request) { return response()->json(['user'=>$request->user()]); }
    public function logout(Request $request) { $request->user()->currentAccessToken()?->delete(); return response()->json(['message'=>'Logout successful.']); }
}
