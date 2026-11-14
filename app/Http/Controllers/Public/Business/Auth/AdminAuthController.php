<?php
namespace App\Http\Controllers\Public\Business\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function loginForm(Request $request)
    {
        $business = $request->attributes->get('current_business');
        if (Auth::guard('business_admin')->check() && (int) Auth::guard('business_admin')->user()->business_id === (int) $business->id) return redirect()->route('store.admin.dashboard', ['business' => $business->domain]);
        return view('portals.login', ['title' => $business->name.' · Admin', 'action' => route('store.admin.login.post', ['business' => $business->domain]), 'registerUrl' => null]);
    }
    public function login(Request $request)
    {
        $business = $request->attributes->get('current_business');
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (Auth::guard('business_admin')->attempt([
            'email' => strtolower(trim($data['email'])), 
            'password' => $data['password'],
            'business_id' => $business->id, 'approval_status' => 'Approved',
        ])) {
            $request->session()->regenerate();
            return redirect()->route('store.admin.dashboard', ['business' => $business->domain]);
        }
        return back()->withErrors(['email' => 'Invalid credentials for this business.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('business_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('store.admin.login', ['business' => $request->route('business')]);
    }
}
