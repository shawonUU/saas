<?php
namespace Modules\Auth\App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\Auth\App\Models\ShopAccount;
use Modules\Auth\App\Models\ShopCustomer;
class RequireAuthScope {
    public function handle(Request $request, Closure $next, string $mode = 'main') {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $businessId = (int) ($request->route('businessId') ?: $request->route('business'));
        $scope = $mode === 'main' ? 'main' : $mode.':'.$businessId;
        $type = $mode === 'main' ? User::class : ($mode === 'admin' ? ShopAccount::class : ShopCustomer::class);
        abort_unless($user instanceof $type && $token && $token->can($scope) && ($mode === 'main' || ($businessId > 0 && (int)$user->business_id === $businessId)),403,'Invalid login context.');
        return $next($request);
    }
}
