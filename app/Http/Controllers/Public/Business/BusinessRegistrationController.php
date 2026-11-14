<?php
namespace App\Http\Controllers\Public\Business;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessRole;
use App\Models\BusinessUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessRegistrationController extends Controller
{
    public function create()
    {
        return view('public.business.business-register', ['categories' => BusinessCategory::where('approval_status', Status::APPROVED)->orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('business_categories', 'id')->where('approval_status', Status::APPROVED)],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $owner = Auth::guard('web')->user();
        $business = DB::transaction(function () use ($data, $owner) {
            $business = Business::create([
                'owner_id' => $owner->id, 
                'category_id' => $data['category_id'],
                'name' => trim($data['name']), 
                'code' => getGenerateCode(Business::class, 'code', 'BUSI'),
                'approval_status' => Status::APPROVED,
            ]);
            $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => Status::APPROVED]);
            BusinessUser::create([
                'business_id' => $business->id, 'user_id' => $owner->id, 'role_id' => $role->id,
                'name' => $owner->name, 'email' => $owner->email,
                'code' => getGenerateCode(BusinessUser::class, 'code', 'BUSU'),
                'password' => filled($data['admin_password'] ?? null) ? $data['admin_password'] : $owner->password,
                'approval_status' => Status::APPROVED,
            ]);
            return $business;
        });
        return redirect()->route('saas.dashboard')->with('business_created', $business->code)->with('success', 'Your business is ready. Open its storefront or admin portal below.');
    }
}
