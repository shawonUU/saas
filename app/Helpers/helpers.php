<?php
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
if (!function_exists('appGuard')) {
    function appGuard(): ?string
    {
        $request = request();
        $guard = $request->attributes->get('auth_guard');
        $allowed = ['authority', 'saas_user', 'business_admin', 'business_customer'];
        if (in_array($guard, $allowed, true)) return $guard;
        $host = strtolower($request->getHost());
        $domain = strtolower(config('saas.domain'));
        if ($host === 'admin.'.$domain) return 'authority';
        if ($host === $domain) return 'saas_user';
        if ($request->attributes->get('current_business')) {
            return str_starts_with($host, 'admin.') ? 'business_admin' : 'business_customer';
        }
        return null;
    }
}
if (!function_exists('authUser')) {
    function authUser(?string $guard = null)
    {
        $guard ??= appGuard();
        return $guard && in_array($guard, ['authority','saas_user','business_admin','business_customer'], true)
            ? Auth::guard($guard)->user() : null;
    }
}
if (!function_exists('appContext')) {
    function appContext(): array
    {
        $request = request();
        if ($request->attributes->has('app_context_cache')) return $request->attributes->get('app_context_cache');
        $guard = appGuard();
        $actor = authUser($guard);
        $business = $request->attributes->get('current_business');
        $globalUser = $actor?->user_id ? User::find($actor->user_id) : null;
        $actorType = $actor ? match ($guard) {
            'authority' => 'authority',
            'saas_user' => 'saas_user',
            'business_admin' => $actor instanceof BusinessUser && $business && $actor->isOwnerOf($business) ? 'business_owner' : 'business_admin',
            'business_customer' => 'shop_customer',
            default => 'guest',
        } : 'guest';
        $route = $request->route();
        $params = [];
        if ($route) foreach ($route->parameters() as $key => $value) {
            $params[$key] = is_object($value) ? (method_exists($value, 'getRouteKey') ? $value->getRouteKey() : null) : $value;
        }
        $result = [
            'app_mode' => match ($guard) {
                'authority' => 'authority',
                'saas_user' => 'saas_owner',
                'business_admin' => 'business_admin',
                'business_customer' => 'storefront',
                default => 'guest',
            },
            'actor_type' => $actorType,
            'guard' => $guard,
            'is_authenticated' => $actor !== null,
            'auth' => $actor ? [
                'table' => $actor->getTable(),
                'id' => $actor->getAuthIdentifier(),
                'user_id' => $actor->user_id ?? null,
                'name' => $actor->name ?? null,
                'email' => $actor->email ?? null,
                'business_id' => $actor->business_id ?? null,
                'role_id' => $actor->role_id ?? null,
                'approval_status' => $actor->approval_status ?? null,
            ] : null,
            'user' => $globalUser ? ['id' => $globalUser->id, 'name' => $globalUser->name, 'email' => $globalUser->email] : null,
            'business' => $business ? ['id' => $business->id, 'name' => $business->name, 'code' => $business->code, 'domain' => $business->domain] : null,
            'request' => ['method' => $request->method(), 'url' => $request->fullUrl(), 'path' => $request->path(), 'host' => $request->getHost(), 'ip' => $request->ip(), 'route_name' => $route?->getName(), 'route_parameters' => $params],
        ];
        $request->attributes->set('app_context_cache', $result);
        return $result;
    }
}
if (!function_exists('approvalStatus')) {
    function approvalStatus(): array { return ['Approved', 'Pending', 'Rejected', 'Cancelled']; }
}
if (!function_exists('t')) {
    function t(string $english, string $bangla): string {
        return session('portal_lang', 'en') === 'bn' ? $bangla : $english;
    }
}

if(!function_exists('getGenerateCode')){
    function getGenerateCode($model, $column,  $prefix, $numbers = 8,$noTrash = null){
        $lastRecord = $model::orderBy('id', 'desc')->first();

        if (!$lastRecord) {return $prefix . '-1';}
        $lastCode = $lastRecord->{$column};
        $number = (int) str_replace($prefix . '-', '', $lastCode);
        return $prefix . '-' . ($number + 1);
    }
}

if (!function_exists('navName')) {
    function navName($item): string {
        return session('portal_lang','en') === 'bn' && filled($item->name_bn) ? $item->name_bn : $item->name;
    }
}



if (!function_exists('currentBusiness')) {
    function currentBusiness(): ?Business { return request()->attributes->get('current_business'); }
}
