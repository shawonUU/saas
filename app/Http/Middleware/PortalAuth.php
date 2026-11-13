<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class PortalAuth
{
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $login = match ($guard) {
            'authority' => 'authority.login',
            'web' => 'saas.login',
            'business_admin' => 'store.admin.login',
            'business_customer' => 'store.customer.login',
            default => abort(403),
        };
        $params = in_array($guard, ['business_admin', 'business_customer']) ? ['business' => $request->route('business')] : [];
        $user = Auth::guard($guard)->user();
        if (!$user) return redirect()->route($login, $params);
        if ($user->approval_status !== 'Approved') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route($login, $params)->withErrors(['email' => 'Account is not active.']);
        }
        if (in_array($guard, ['business_admin', 'business_customer'])) {
            $business = $request->attributes->get('current_business');
            abort_unless($business && (int) $user->business_id === (int) $business->id, 403);
        }
        Auth::shouldUse($guard);
        return $next($request);
    }
}
