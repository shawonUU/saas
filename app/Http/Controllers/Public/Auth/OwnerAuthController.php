<?php
namespace App\Http\Controllers\Public\Auth;
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
        return view('public.auth.login', ['title' => 'SaaS Account', 'action' => route('saas.login.post'), 'registerUrl' => route('saas.register')]);
    }
    public function registerForm()
    {
        if (Auth::guard('web')->check()) return redirect()->route('saas.dashboard');
        return view('public.auth.register', ['title' => 'Create SaaS Account', 'action' => route('saas.register.post'), 'loginUrl' => route('saas.login')]);
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
