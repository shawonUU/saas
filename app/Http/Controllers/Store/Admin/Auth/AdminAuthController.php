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
