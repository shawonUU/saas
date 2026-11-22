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
use Illuminate\Validation\Rule;
use App\Services\BusinessSubscriptionService;
class BusinessRegistrationController extends Controller
{
    public function create()
    {
        return view('public.business.business-register', [
            'categories' => BusinessCategory::where('approval_status', Status::APPROVED)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        ]);
    }

    public function store(Request $request, BusinessSubscriptionService $subscriptions)
    {
        $request->merge([
            'domain' => strtolower(trim((string) $request->input('domain')))
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('business_categories', 'id')
                    ->where('approval_status', Status::APPROVED)
                    ->where('is_active', true)
            ],
            'domain' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[a-z][a-z0-9-]*$/',
                Rule::notIn(config('saas.reserved_subdomains', [])),
                'unique:businesses,domain'
            ],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $owner = Auth::guard('saas_user')->user();

        abort_unless($owner && $owner->user_id, 403);

        $business = DB::transaction(function () use ($data, $owner, $subscriptions) {

            $business = Business::create([
                'owner_id' => $owner->user_id,
                'category_id' => $data['category_id'],
                'name' => trim($data['name']),
                'domain' => $data['domain'],
                'code' => getGenerateCode(Business::class, 'code', 'BUSI'),
                'approval_status' => Status::APPROVED,
            ]);

            $role = BusinessRole::create([
                'business_id' => $business->id,
                'name' => 'Owner',
                'is_owner' => true,
                'approval_status' => Status::APPROVED
            ]);

            BusinessUser::create([
                'business_id' => $business->id,
                'user_id' => $owner->user_id,
                'role_id' => $role->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'code' => getGenerateCode(BusinessUser::class, 'code', 'BUSU'),
                'password' => filled($data['admin_password'] ?? null)
                    ? $data['admin_password']
                    : $owner->password,
                'approval_status' => Status::APPROVED,
            ]);

            // Activation and feature snapshot happen in this same transaction.
            // A failed activation rolls back business, role and owner creation.
            $subscriptions->activateBasic($business);
            if (in_array($business->category?->slug, ['retail','grocery','ecommerce','online-store'], true)) {
                app(Accounting::class)->ensure($business);
            }

            return $business;
        });

        return redirect()
            ->route('saas.dashboard')
            ->with('business_created', $business->domain)
            ->with('success', 'Your business is ready. Open its storefront or admin portal below.');
    }
}
