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
        $logins = [
            'authority' => 'authority.login',
            'saas_user' => 'saas.login',
            'business_admin' => 'store.admin.login',
            'business_customer' => 'store.customer.login',
        ];
        abort_unless(isset($logins[$guard]), 403);
        $tenantGuard = in_array($guard, ['business_admin', 'business_customer'], true);
        $business = $tenantGuard ? $request->attributes->get('current_business') : null;
        if ($tenantGuard) abort_unless($business, 404);
        $params = $tenantGuard ? ['business' => $business->domain] : [];
        $request->attributes->set('auth_guard', $guard);
        Auth::shouldUse($guard);
        $user = Auth::guard($guard)->user();
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route($logins[$guard], $params);
        }
        if ($tenantGuard && (int) $user->business_id !== (int) $business->id) abort(403);
        if ($user->approval_status !== 'Approved' || !$user->is_active) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return $request->expectsJson()
                ? response()->json(['message' => 'Account is not active.'], 403)
                : redirect()->route($logins[$guard], $params)->withErrors(['email' => 'Account is not active.']);
        }
        return $next($request);
    }
}
