<?php
namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveBusiness
{
    public function handle(Request $request,Closure $next):Response
    {
        $domain=strtolower((string)$request->route('business'));
        abort_if(!$domain || in_array($domain,config('saas.reserved_subdomains',[]),true),404);
        $business=Business::where('domain',$domain)->where('approval_status','Approved')->firstOrFail();
        $request->attributes->set('current_business',$business);
        URL::defaults(['business'=>$business->domain]);
        View::share('business',$business);
        return $next($request);
    }
}
