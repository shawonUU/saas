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
use RuntimeException;

use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPlanFeature;
use App\Models\BusinessSubscriptionFeature;
class BusinessRegistrationController extends Controller
{
    public function create()
    {
        return view('public.business.business-register', [
            'categories' => BusinessCategory::where('approval_status', Status::APPROVED)
                ->orderBy('name')
                ->get()
        ]);
    }

    public function store(Request $request)
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

        $business = DB::transaction(function () use ($data, $owner) {

            $category = BusinessCategory::findOrFail($data['category_id']);

            // Find category-wise Basic Plan
            $plan = SubscriptionPlan::where('business_category_id', $category->id)
                ->where('slug', $category->slug . '-basic')
                ->first();

            if (!$plan) {
                throw new RuntimeException(
                    "Basic subscription plan not found for {$category->name} category."
                );
            }

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

            // Create Active Basic Subscription
            $now = now();

            // Free plan will not expire
            $endsAt = null;

            if ((float) $plan->price > 0) {
                $interval = max(1, (int) $plan->billing_interval);

                $endsAt = match (strtolower($plan->billing_cycle)) {
                    'yearly' => $now->copy()->addYears($interval),
                    default => $now->copy()->addMonths($interval),
                };
            }

            $subscriptionId = BusinessSubscription::insertGetId([
                'code' => getGenerateCode(BusinessSubscription::class, 'code', 'BUSS'),
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'subscription_status' => 'active',
                'starts_at' => $now,
                'ends_at' => $endsAt,
                'trial_ends_at' => null,
                'canceled_at' => null,
                'auto_renew' => false,
                'external_reference' => null,
                'notes' => 'Automatically assigned Basic Plan during business registration.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Copy Plan Features to Business Subscription Features
            $planFeatures = SubscriptionPlanFeature::where('plan_id', $plan->id)->get();

            $subscriptionFeatures = [];

            foreach ($planFeatures as $feature) {
                $subscriptionFeatures[] = [
                    'code' => uniqid(),
                    'business_subscription_id' => $subscriptionId,
                    'module_id' => $feature->module_id,
                    'menu_id' => $feature->menu_id,
                    'child_menu_id' => $feature->child_menu_id,
                    'feature_key' => $feature->feature_key,
                    'source' => 'plan',
                    'is_enabled' => true,
                    'starts_at' => $now,
                    'ends_at' => $endsAt,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($subscriptionFeatures)) {
                BusinessSubscriptionFeature::insert($subscriptionFeatures);
            }

            return $business;
        });

        return redirect()
            ->route('saas.dashboard')
            ->with('business_created', $business->domain)
            ->with('success', 'Your business is ready. Open its storefront or admin portal below.');
    }
}
