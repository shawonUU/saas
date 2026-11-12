<?php
namespace App\Http\Controllers\Saas\Authority\Auth;
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
