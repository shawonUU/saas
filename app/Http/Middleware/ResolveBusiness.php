
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
        $code=strtolower((string)$request->route('business'));
        abort_if(!$code || in_array($code,config('saas.reserved_subdomains',[]),true),404);
        $business=Business::where('code',$code)->where('approval_status','Approved')->firstOrFail();
        $request->attributes->set('current_business',$business);
        URL::defaults(['business'=>$business->code]);
        View::share('business',$business);
        return $next($request);
    }
}
