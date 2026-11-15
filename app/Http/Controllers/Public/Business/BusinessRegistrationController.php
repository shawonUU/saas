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
        $request->merge(['domain' => strtolower(trim((string) $request->input('domain')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('business_categories', 'id')->where('approval_status', Status::APPROVED)],
            'domain' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z][a-z0-9-]*$/', Rule::notIn(config('saas.reserved_subdomains', [])), 'unique:businesses,domain'],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $owner = Auth::guard('saas_user')->user();
        abort_unless($owner && $owner->user_id, 403);
        $business = DB::transaction(function () use ($data, $owner) {
            $business = Business::create([
                'owner_id' => $owner->user_id, 
                'category_id' => $data['category_id'],
                'name' => trim($data['name']),
                'domain' => $data['domain'],
                'code' => getGenerateCode(Business::class, 'code', 'BUSI'),
                'approval_status' => Status::APPROVED,
            ]);
            $role = BusinessRole::create(['business_id' => $business->id, 'name' => 'Owner', 'is_owner' => true, 'approval_status' => Status::APPROVED]);
            BusinessUser::create([
                'business_id' => $business->id, 'user_id' => $owner->user_id, 'role_id' => $role->id,
                'name' => $owner->name, 'email' => $owner->email,
                'code' => getGenerateCode(BusinessUser::class, 'code', 'BUSU'),
                'password' => filled($data['admin_password'] ?? null) ? $data['admin_password'] : $owner->password,
                'approval_status' => Status::APPROVED,
            ]);
            return $business;
        });
        return redirect()->route('saas.dashboard')->with('business_created', $business->domain)->with('success', 'Your business is ready. Open its storefront or admin portal below.');
    }
}
