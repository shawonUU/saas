<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('appContext')) {
    function appContext(): array
    {
        $saasDomain = config('app.saas_domain', 'saas.test');
        $request = request();
        $business = $request->attributes->get('current_business');
        $guards = ['authority','saas_user','business_admin','business_customer',];
        $guard = $request->attributes->get('auth_guard');

        if (!$guard) {
            $host = $request->getHost();
            if ($host === 'admin.' . $saasDomain) {$guard = 'authority';} 
            elseif ($host === $saasDomain) {$guard = 'saas_user';} 
            elseif ($business) {$guard = str_starts_with($host, 'admin.')? 'business_admin':'business_customer';} 
            else {
                foreach ($guards as $item) {
                    if (Auth::guard($item)->check()) {$guard = $item;break;}
                }
            }
        }

        $guard = in_array($guard, $guards, true) ? $guard : null;
        $authUser = $guard ? Auth::guard($guard)->user() : null;
        $userId = $authUser?->user_id;
        $globalUser = $userId? User::find($userId) : null;

        $portal = match ($guard) {
            'authority' => 'authority',
            'saas_user' => 'saas_owner',
            'business_admin' => 'business_admin',
            'business_customer' => 'business_customer',
            default => 'guest',
        };

        $businessData = $business ? [
            'id' => $business->id,
            'name' => $business->name ?? null,
            'code' => $business->code ?? null,
        ] : null;

        $authData = $authUser ? [
            'id' => $authUser->id,
            'user_id' => $authUser->user_id ?? null,
            'name' => $authUser->name ?? null,
            'email' => $authUser->email ?? null,
            'business_id' => $authUser->business_id ?? null,
            'role_id' => $authUser->role_id ?? null,
            'approval_status' => $authUser->approval_status ?? null,
            'global_user' => $globalUser ? [
                'id' => $globalUser->id,
                'name' => $globalUser->name,
                'email' => $globalUser->email,
            ] : null,
        ] : null;

        $route = $request->route();

        $routeParameters = [];

        if ($route) {
            foreach ($route->parameters() as $key => $value) {
                $routeParameters[$key] = is_object($value)
                    ? (method_exists($value, 'getRouteKey') ? $value->getRouteKey() : null)
                    : $value;
            }
        }

        return [
            'app_mode' => $portal,
            'actor_type' => $guard,
            'guard' => $guard,
            'is_authenticated' => $authUser !== null,

            'auth' => $authData,

            'user' => $globalUser ? [
                'id' => $globalUser->id,
                'name' => $globalUser->name,
                'email' => $globalUser->email,
            ] : null,

            'business' => $businessData,

            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'host' => $request->getHost(),
                'ip' => $request->ip(),
                'route_name' => $route?->getName(),
                'route_parameters' => $routeParameters,
            ],
        ];
    }
}

if (!function_exists('authUser')) {
    function authUser(?string $guard = null)
    {
        $saasDomain = config('app.saas_domain', 'saas.test');
        $request = request();
        if (!$guard) {
            $guard = $request->attributes->get('auth_guard');
            if (!$guard) {
                $host = $request->getHost();
                $business = $request->attributes->get('current_business');
                if ($host === 'admin.' . $saasDomain) {$guard = 'authority';} 
                elseif ($host === $saasDomain) {$guard = 'saas_user';} 
                elseif ($business) {
                    $guard = str_starts_with($host, 'admin.') ? 'business_admin' : 'business_customer';
                }
            }
        }
        $guards = ['authority','saas_user','business_admin','business_customer', ];
        if ($guard && in_array($guard, $guards, true)) {
            return Auth::guard($guard)->user();
        }
        foreach ($guards as $item) {
            if (Auth::guard($item)->check()) {return Auth::guard($item)->user();}
        }
        return null;
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

if(!function_exists('currentBusiness')){
    function currentBusiness():?Business{
        return request()->attributes->get('currentBusiness');
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


if(!function_exists('currentBusiness')){
    function currentBusiness():?Business{
        return request()->attributes->get('currentBusiness');
    }
}
