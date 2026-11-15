<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\AuthorityUser;
use App\Models\SaasUser;
use App\Models\BusinessUser;
use App\Models\BusinessCustomer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
class PortalPasswordController extends Controller
{
    private function portal(): array
    {
        $guard = appGuard();
        $mapping = [
            'authority' => ['authority', 'authority_users', AuthorityUser::class],
            'saas_user' => ['saas', 'saas_users', SaasUser::class],
            'business_admin' => ['store.admin', 'business_users', BusinessUser::class],
            'business_customer' => ['store.customer', 'business_customers', BusinessCustomer::class],
        ];
        abort_unless(isset($mapping[$guard]), 404);
        if (in_array($guard, ['business_admin', 'business_customer'], true)) abort_unless(currentBusiness(), 404);
        return [$guard, ...$mapping[$guard]];
    }
    private function params(string $guard): array
    {
        return in_array($guard, ['business_admin', 'business_customer'], true) ? ['business' => currentBusiness()->domain] : [];
    }
    private function lookup(string $guard, string $model, string $email)
    {
        $query = $model::query()->where('email', $email)->where('is_active', true)->where('approval_status', 'Approved');
        if (in_array($guard, ['business_admin', 'business_customer'], true)) $query->where('business_id', currentBusiness()->id);
        return $query->first();
    }
    public function forgotForm()
    {
        [$guard, $prefix] = $this->portal();
        return view('portals.password-forgot', [
            'layout' => $guard === 'saas_user' ? 'public.layout' : 'layouts.portal',
            'action' => route($prefix.'.password.email', $this->params($guard)),
        ]);
    }
    public function sendLink(Request $request)
    {
        [$guard, $prefix, $brokerName, $model] = $this->portal();
        $request->merge(['email' => strtolower(trim((string)$request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = $this->lookup($guard, $model, $data['email']);
        if ($user) {
            $token = Password::broker($brokerName)->createToken($user);
            $url = route($prefix.'.password.reset', [...$this->params($guard), 'token' => $token, 'email' => $user->email]);
            Mail::raw("You requested a password reset. Use this one-time link within 60 minutes:\n\n".$url."\n\nIf you did not request it, ignore this email.", function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your password');
            });
        }
        return back()->with('status', t('If the account exists, a reset link has been sent.', 'অ্যাকাউন্ট থাকলে রিসেট লিংক পাঠানো হয়েছে।'));
    }
    public function resetForm(Request $request, string $token)
    {
        [$guard, $prefix] = $this->portal();
        return view('portals.password-reset', [
            'layout' => $guard === 'saas_user' ? 'public.layout' : 'layouts.portal',
            'action' => route($prefix.'.password.update', $this->params($guard)),
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }
    public function update(Request $request)
    {
        [$guard, $prefix, $brokerName, $model] = $this->portal();
        $request->merge(['email' => strtolower(trim((string)$request->input('email')))]);
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = $this->lookup($guard, $model, $data['email']);
        $broker = Password::broker($brokerName);
        if (!$user || !$broker->tokenExists($user, $data['token'])) {
            return back()->withErrors(['email' => t('Invalid or expired reset link.', 'রিসেট লিংকটি ভুল অথবা মেয়াদ শেষ।')])->onlyInput('email');
        }
        DB::transaction(function () use ($user, $data, $broker) {
            $user->password = $data['password']; // Hashed cast on every auth model
            $user->setRememberToken(Str::random(60));
            $user->save();
            $broker->deleteToken($user);
        });
        event(new PasswordReset($user));
        return redirect()->route($prefix.'.login', $this->params($guard))
            ->with('success', t('Password changed. Please sign in.', 'পাসওয়ার্ড পরিবর্তন হয়েছে। লগইন করুন।'));
    }
}
