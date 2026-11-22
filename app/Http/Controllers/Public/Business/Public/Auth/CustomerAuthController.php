<?php

namespace App\Http\Controllers\Public\Business\Public\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Models\SaasUser;
class CustomerAuthController extends Controller
{
    public function loginForm(Request $request){
        $business = $request->attributes->get('current_business');
        if (Auth::guard('business_customer')->check() && (int) Auth::guard('business_customer')->user()->business_id === (int) $business->id) {
            return redirect()->route('business.customer.dashboard', ['business' => $business->domain]);
        }
        return view('public.business.public.auth.login', [
            'title' => $business->name.' · Customer',
            'action' => route('business.customer.login.post', ['business' => $business->domain]),
            'registerUrl' => route('business.customer.register', ['business' => $business->domain]),
            'forgotUrl' => route('business.customer.password.request', ['business' => $business->domain]),
        ]);
    }
    public function registerForm(Request $request){
        $business = $request->attributes->get('current_business');
        if (Auth::guard('business_customer')->check() && (int) Auth::guard('business_customer')->user()->business_id === (int) $business->id) {
            return redirect()->route('business.customer.dashboard', ['business' => $business->domain]);
        }
        return view('public.business.public.auth.register', [
            'title' => $business->name.' · Register',
            'action' => route('business.customer.register.post', ['business' => $business->domain]),
            'loginUrl' => route('business.customer.login', ['business' => $business->domain]),
        ]);
    }
    public function register(Request $request){
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $business = $request->attributes->get('current_business');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('business_customers', 'email')->where('business_id', $business->id), Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $customer = DB::transaction(function () use ($data, $business) {
            $user = User::create([
                'name' => trim($data['name']),
                'code' => getGenerateCode(User::class, 'code', 'USR'),
                'email' => $data['email'],
                'approval_status' => 'Approved',
            ]);
            return BusinessCustomer::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'name' => trim($data['name']),
                'email' => $data['email'],
                'code' => getGenerateCode(BusinessCustomer::class, 'code', 'BCUST'),
                'password' => $data['password'],
                'approval_status' => 'Approved',
            ]);
        });
        Auth::guard('business_customer')->login($customer);
        $request->session()->regenerate();
        return redirect()->route('business.customer.dashboard', ['business' => $business->domain]);
    }
    public function login(Request $request){
        $business = $request->attributes->get('current_business');
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (Auth::guard('business_customer')->attempt([
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'business_id' => $business->id,
            'approval_status' => 'Approved',
            'is_active' => true,
        ])) {
            $request->session()->regenerate();
            return redirect()->route('business.customer.dashboard', ['business' => $business->domain]);
        }
        return back()->withErrors([
            'email' => 'Invalid credentials for this business.',
        ])->onlyInput('email');
    }
    public function logout(Request $request){
        Auth::guard('business_customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('business.customer.login', [
            'business' => $request->route('business'),
        ]);
    }
}
