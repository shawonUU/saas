<?php
namespace App\Http\Middleware;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class ResolveShop
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = strtolower((string) $request->route('shop'));
        abort_if(in_array($code, config('saas.reserved_subdomains', []), true), 404);
        $shop = Shop::where('code', $code)->where('approval_status', 'Approved')->firstOrFail();
        $request->attributes->set('current_shop', $shop);
        return $next($request);
    }
}
