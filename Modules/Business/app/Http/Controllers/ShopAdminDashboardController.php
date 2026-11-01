<?php
namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Business\App\Models\AppModule;
use Modules\Auth\App\Models\ShopAccount;
use Modules\Business\App\Models\Business;

class ShopAdminDashboardController extends Controller
{
    public function show(Request $request)
    {
        $account = $request->user();
        abort_unless($account instanceof ShopAccount, 403);
        $token = $account->currentAccessToken();
        abort_unless($token && $token->can('admin:'.$account->business_id), 403);
        $business = Business::with(['category','branches'])->whereKey($account->business_id)
            ->where('status', 'Active')->firstOrFail();
        $modules = AppModule::query()->where('is_active', true)
            ->whereHas('categoryModules', fn($q) => $q->where('business_category_id', $business->category_id))
            ->with(['menus' => fn($q) => $q->where('is_active',true)->with(['childMenus' => fn($c) => $c->where('is_active',true)])])
            ->orderBy('sort_order')->get();
        return response()->json([
            'modules' => $modules,
            'business' => $business,
            'account' => $account->only(['id','name','email','role']),
            'branches_count' => $business->branches->count(),
        ]);
    }
}
