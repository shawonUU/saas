<?php
namespace App\Http\Controllers\Public\Business\Admin;
use App\Http\Controllers\Controller;
use App\Models\ChildMenu;
use App\Services\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function dashboard(Request $request, BusinessNavigation $nav)
    {
        $business = $request->attributes->get('current_business');
        $user = Auth::guard('business_admin')->user();
        return view('public.business.admin.dashboard', ['business' => $business,'user' => $user,
            'modules' => $nav->forBusiness($business,$user)]);
    }


    public function feature(Request $request,BusinessNavigation $nav)
    {
        $business=$request->attributes->get('current_business');
        $user=Auth::guard('business_admin')->user();
        abort_unless($business && $user,403);

        $moduleSlug=(string)$request->route('moduleSlug');
        $menuSlug=(string)$request->route('menuSlug');
        $childSlug=$request->route('childSlug');

        $modules=$nav->forBusiness($business,$user);
        $module=$modules->first(fn($item)=>(string)$item->slug===$moduleSlug);
        abort_unless($module,404);

        $menu=$module->menus->first(fn($item)=>(string)$item->slug===$menuSlug);
        abort_unless($menu,404);

        $child=null;
        if($childSlug!==null){
            $child=$menu->children->first(fn($item)=>(string)$item->slug===(string)$childSlug);
            abort_unless($child,404);
            abort_unless($nav->canView($business,$user,$child->id),403);
            $child->loadMissing('menu.module');
        }else{
            abort_if($menu->children->isNotEmpty(),404);
        }

        return view('public.business.admin.feature',[
            'business'=>$business,
            'user'=>$user,
            'modules'=>$modules,
            'module'=>$module,
            'menu'=>$menu,
            'child'=>$child
        ]);
    }

}
