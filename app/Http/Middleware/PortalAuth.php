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
            'shop_admin' => 'store.admin.login',
            'shop_customer' => 'store.customer.login',
            default => abort(403),
        };
        $params = in_array($guard, ['shop_admin', 'shop_customer']) ? ['shop' => $request->route('shop')] : [];
        $user = Auth::guard($guard)->user();
        if (!$user) return redirect()->route($login, $params);
        if ($user->approval_status !== 'Approved') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route($login, $params)->withErrors(['email' => 'Account is not active.']);
        }
        if (in_array($guard, ['shop_admin', 'shop_customer'])) {
            $shop = $request->attributes->get('current_shop');
            abort_unless($shop && (int) $user->shop_id === (int) $shop->id, 403);
        }
        Auth::shouldUse($guard);
        return $next($request);
    }
}
