<?php

namespace App\Http\Controllers\Public\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SaasUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerAuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::guard('saas_user')->check()) return redirect()->route('saas.dashboard');

        return view('public.auth.login', [
            'title' => 'SaaS Account',
            'action' => route('saas.login.post'),
            'registerUrl' => route('saas.register'),
            'forgotUrl' => route('saas.password.request'),
        ]);
    }

    public function registerForm()
    {
        if (Auth::guard('saas_user')->check()) return redirect()->route('saas.dashboard');

        return view('public.auth.register', [
            'title' => 'Create SaaS Account',
            'action' => route('saas.register.post'),
            'loginUrl' => route('saas.login'),
        ]);
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:saas_users,email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $saasUser = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'code' => getGenerateCode(User::class, 'code', 'USR'),
                'approval_status' => 'Approved',
            ]);

            return SaasUser::create([
                'user_id' => $user->id,
                'name' => trim($data['name']),
                'email' => $data['email'],
                'code' => getGenerateCode(SaasUser::class, 'code', 'SASUSR'),
                'password' => $data['password'],
                'approval_status' => 'Approved',
            ]);
        });

        Auth::guard('saas_user')->login($saasUser);

        $request->session()->regenerate();

        return redirect()->route('saas.dashboard');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $data['email'] = strtolower(trim($data['email']));

        if (Auth::guard('saas_user')->attempt([...$data, 'approval_status' => 'Approved', 'is_active' => true])) {
            $request->session()->regenerate();

            return redirect()->route('saas.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('saas_user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('saas.login');
    }
}
