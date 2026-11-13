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
    public function feature(Request $request, ChildMenu $childMenu, BusinessNavigation $nav)
    {
        $business = $request->attributes->get('current_business');
        $user = Auth::guard('business_admin')->user();
        abort_unless($nav->canView($business,$user,$childMenu->id),403);
        return view('public.business.admin.feature', ['business' => $business,'user' => $user,
            'modules' => $nav->forBusiness($business,$user),'child' => $childMenu->load('menu.module')]);
    }
}
