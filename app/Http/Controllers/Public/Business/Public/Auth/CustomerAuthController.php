<?php
namespace App\Http\Controllers\Business\Public\Auth;
use App\Http\Controllers\Controller;
use App\Models\BusinessCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    public function loginForm(Request $request)
    {
        $business = $request->attributes->get('current_business');
        if (Auth::guard('business_customer')->check() && (int) Auth::guard('business_customer')->user()->business_id === (int) $business->id) return redirect()->route('store.customer.dashboard', ['business' => $business->code]);
        return view('portals.login', ['title' => $business->name.' · Customer', 'action' => route('store.customer.login.post', ['business' => $business->code]), 'registerUrl' => route('store.customer.register', ['business' => $business->code])]);
    }
    public function registerForm(Request $request)
    {
        $business = $request->attributes->get('current_business');
        if (Auth::guard('business_customer')->check() && (int) Auth::guard('business_customer')->user()->business_id === (int) $business->id) return redirect()->route('store.customer.dashboard', ['business' => $business->code]);
        return view('portals.register', ['title' => $business->name.' · Register', 'action' => route('store.customer.register.post', ['business' => $business->code]), 'loginUrl' => route('store.customer.login', ['business' => $business->code])]);
    }
    public function register(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $business = $request->attributes->get('current_business');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('business_customers', 'email')->where('business_id', $business->id)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $customer = BusinessCustomer::create([
            'business_id' => $business->id, 'name' => trim($data['name']), 'email' => strtolower(trim($data['email'])),
            'code' => 'SC'.Str::upper(Str::random(12)), 'password' => $data['password'], 'approval_status' => 'Approved',
        ]);
        Auth::guard('business_customer')->login($customer);
        $request->session()->regenerate();
        return redirect()->route('store.customer.dashboard', ['business' => $business->code]);
    }
    public function login(Request $request)
    {
        $business = $request->attributes->get('current_business');
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (Auth::guard('business_customer')->attempt([
            'email' => strtolower(trim($data['email'])), 'password' => $data['password'],
            'business_id' => $business->id, 'approval_status' => 'Approved',
        ])) {
            $request->session()->regenerate();
            return redirect()->route('store.customer.dashboard', ['business' => $business->code]);
        }
        return back()->withErrors(['email' => 'Invalid credentials for this business.'])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::guard('business_customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('store.customer.login', ['business' => $request->route('business')]);
    }
}
