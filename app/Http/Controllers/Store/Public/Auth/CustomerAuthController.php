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
