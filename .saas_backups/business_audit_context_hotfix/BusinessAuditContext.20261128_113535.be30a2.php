<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Services\Public\Business\Admin\Settings\Business\BusinessDate\BusinessDateService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class BusinessAuditContext
{
    public function handle(Request $request, Closure $next, BusinessDateService $days): Response
    {
        $business = $request->attributes->get('current_business');
        $date = null;
        if ($business instanceof Business && Schema::hasTable('business_day_settings')) {
            $state = $days->current($business);
            $date = $state->open_date->toDateString();
            $request->attributes->set('current_business_date', $date);
            $request->attributes->set('business_day_state', $state);
            $request->attributes->set('business_day_status', $days->status($state));
        }
        if (DB::getDriverName() !== 'mysql') return $next($request);
        $host = strtolower($request->getHost());
        $root = strtolower((string) config('saas.domain'));
        $guard = $host === 'admin.'.$root ? 'authority' : ($host === $root ? 'saas_user'
            : (str_starts_with($host, 'admin.') ? 'business_admin' : 'business_customer'));
        $actor = Auth::guard($guard)->user();
        if ($business instanceof Business && $actor && (int) ($actor->business_id ?? 0) !== (int) $business->id) $actor = null;
        $values = [
            $date, $actor ? (string) $actor->getAuthIdentifier() : null,
            $actor ? get_class($actor) : null, $actor?->name,
            $actor ? $guard : null, $request->ip(), mb_substr((string)$request->userAgent(),0,2000),
            $request->method(), $request->route()?->getName(), mb_substr($request->path(),0,1024),
        ];
        DB::statement('SET @saas_business_date=?, @saas_actor_id=?, @saas_actor_type=?, '
            .'@saas_actor_name=?, @saas_actor_guard=?, @saas_request_ip=?, '
            .'@saas_request_ua=?, @saas_request_method=?, @saas_route_name=?, @saas_request_path=?', $values);
        try {
            return $next($request);
        } finally {
            // Prevent session-variable leakage in long-lived connections/workers.
            DB::statement('SET @saas_business_date=NULL, @saas_actor_id=NULL, @saas_actor_type=NULL, '
                .'@saas_actor_name=NULL, @saas_actor_guard=NULL, @saas_request_ip=NULL, '
                .'@saas_request_ua=NULL, @saas_request_method=NULL, @saas_route_name=NULL, @saas_request_path=NULL');
        }
    }
}
