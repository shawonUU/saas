<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Business\App\Models\Business;

class BusinessDashboardController extends Controller
{
    public function show(Request $request, int $businessId): JsonResponse
    {
        $user = $request->user();

        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->whereHas('members', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', 'Active');
            })
            ->with([
                'category',
                'branches' => function ($query) {
                    $query->where('status', 'Active');
                },
            ])
            ->first();

        if (! $business) {
            return response()->json([
                'message' => 'Business not found or you do not have access to it.',
            ], 404);
        }

        $membership = $business->members()
            ->where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        return response()->json([
            'message' => 'Store dashboard loaded successfully.',
            'data' => [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'country' => $business->country,
                    'currency' => $business->currency,
                    'timezone' => $business->timezone,
                    'phone' => $business->phone,
                    'email' => $business->email,
                    'status' => $business->status,
                    'category' => $business->category,
                ],
                'membership' => [
                    'role' => $membership?->role,
                    'status' => $membership?->status,
                ],
                'branches' => $business->branches,
                'summary' => [
                    'branches_count' => $business->branches->count(),
                ],
            ],
        ]);
    }
}