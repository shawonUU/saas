<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Models\Business;
use Modules\Business\App\Models\BusinessCategory;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $businesses = Business::query()
            ->whereHas('members', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'Active');
            })
            ->with(['category', 'branches'])
            ->latest()
            ->get();

        return response()->json([
            'businesses' => $businesses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
            'slug' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::notIn(['www','admin','api','app','mail','support','localhost','saas']), 'unique:businesses,slug'],
            'category_id' => [
                'required',
                'integer',
                'exists:business_categories,id',
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'max:100'],
        ]);

        $category = BusinessCategory::query()
            ->where('id', $validated['category_id'])
            ->where('is_active', true)
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'category_id' => [
                    'The selected business category is inactive or unavailable.',
                ],
            ]);
        }

        $business = DB::transaction(function () use (
            $request,
            $validated,
            $category
        ) {
            $business = Business::create([
                'owner_id' => $request->user()->id,
                'category_id' => $category->id,
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'country' => $validated['country'] ?? 'Bangladesh',
                'currency' => strtoupper($validated['currency'] ?? 'BDT'),
                'timezone' => $validated['timezone'] ?? 'Asia/Dhaka',
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'status' => 'Active',
            ]);

            \Modules\Auth\App\Models\ShopAccount::create([
                'business_id' => $business->id,
                'central_user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'password' => $validated['admin_password'],
                'role' => 'Owner',
                'status' => 'Active',
            ]);

            $business->members()->create([
                'user_id' => $request->user()->id,
                'role' => 'Owner',
                'status' => 'Active',
            ]);

            $business->branches()->create([
                'name' => $business->name . ' Main Branch',
                'code' => 'MAIN',
                'phone' => $validated['phone'] ?? null,
                'address' => null,
                'is_default' => true,
                'status' => 'Active',
            ]);

            return $business;
        });

        return response()->json([
            'message' => 'Business created successfully.',
            'domains' => ['storefront' => $business->slug . '.' . env('SAAS_LOCAL_DOMAIN', 'localtest.me'), 'admin' => 'admin.' . $business->slug . '.' . env('SAAS_LOCAL_DOMAIN', 'localtest.me')],
            'business' => $business->load([
                'category',
                'branches',
            ]),
        ], 201);
    }
}
