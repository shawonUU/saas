<?php
namespace Modules\Auth\App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\Auth\App\Models\ShopAccount;
use Modules\Auth\App\Models\ShopCustomer;
class CheckAuthDomain {
    public function handle(Request $request, Closure $next) {
        $origin = $request->header('Origin');
        $host = strtolower((string)($origin ? parse_url($origin, PHP_URL_HOST) : $request->getHost()));
        $base = strtolower((string)env('SAAS_BASE_DOMAIN', 'saas.com'));
        $local = strtolower((string)env('SAAS_LOCAL_DOMAIN', 'localtest.me'));
        $mode = 'main'; $slug = null; $valid = false;
        foreach (array_unique([$base, $local]) as $domain) {
            if ($host === $domain || $host === 'www.'.$domain) { $valid = true; break; }
            if (str_ends_with($host, '.'.$domain)) {
                $sub = substr($host, 0, -(strlen($domain)+1));
                if (preg_match('/^admin\.([a-z0-9-]+)$/',$sub,$m)) { $mode='admin'; $slug=$m[1]; $valid=true; break; }
                if (preg_match('/^[a-z0-9-]+$/',$sub)) { $mode='customer'; $slug=$sub; $valid=true; break; }
            }
        }
        if (in_array($host,['localhost','127.0.0.1'],true)) $valid=true;
        abort_unless($valid, 403);
        $user=$request->user(); $token=$user?->currentAccessToken();
        $id=$slug ? \Modules\Business\App\Models\Business::where('slug',$slug)->where('status','Active')->value('id') : null;
        $scope=$mode==='main' ? 'main' : $mode.':'.$id;
        $type=$mode==='main' ? User::class : ($mode==='admin' ? ShopAccount::class : ShopCustomer::class);
        abort_unless($user instanceof $type && $token && $token->can($scope) && ($mode==='main' || ($id && (int)$user->business_id===(int)$id)),403);
        return $next($request);
    }
}
