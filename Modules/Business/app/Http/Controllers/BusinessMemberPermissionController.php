<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Models\Business;
use Modules\Business\App\Models\BusinessMember;
use Modules\Business\App\Models\BusinessMemberPermission;
use Modules\Business\App\Models\BusinessCategoryModule;

class BusinessMemberPermissionController extends Controller
{
    public function update(
        Request $request,
        int $businessId,
        int $memberId
    ): JsonResponse {
        $validated = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*.app_module_id' => [
                'required',
                'integer',
                'distinct',
                'exists:app_modules,id',
            ],
            'permissions.*.can_view' => [
                'required',
                'boolean',
            ],
            'permissions.*.can_create' => [
                'required',
                'boolean',
            ],
            'permissions.*.can_update' => [
                'required',
                'boolean',
            ],
            'permissions.*.can_delete' => [
                'required',
                'boolean',
            ],
        ]);

        $user = $request->user();

        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('role', 'Owner')
                    ->where('status', 'Active');
            })
            ->with('category')
            ->first();

        if (!$business || !$business->category?->is_active) {
            return response()->json([
                'message' => 'Business not found or access denied.',
            ], 404);
        }

        $member = BusinessMember::query()
            ->whereKey($memberId)
            ->where('business_id', $business->id)
            ->where('status', 'Active')
            ->first();

        if (!$member) {
            return response()->json([
                'message' => 'Business member not found.',
            ], 404);
        }

        if ($member->role === 'Owner') {
            throw ValidationException::withMessages([
                'member' => [
                    'Owner permissions cannot be changed here.',
                ],
            ]);
        }

        $moduleIds = collect($validated['permissions'])
            ->pluck('app_module_id')
            ->unique()
            ->values();

        $allowedModuleIds = BusinessCategoryModule::query()
            ->where('business_category_id', $business->category_id)
            ->whereIn('app_module_id', $moduleIds)
            ->whereHas('module', function ($query) {
                $query->where('is_active', true);
            })
            ->pluck('app_module_id');

        if ($allowedModuleIds->count() !== $moduleIds->count()) {
            throw ValidationException::withMessages([
                'permissions' => [
                    'One or more modules are not enabled for this business category.',
                ],
            ]);
        }

        $savedPermissions = DB::transaction(function () use (
            $validated,
            $member,
            $business
        ) {
            foreach ($validated['permissions'] as $permission) {
                $canView = (bool) $permission['can_view'];

                BusinessMemberPermission::updateOrCreate(
                    [
                        'business_member_id' => $member->id,
                        'app_module_id' => $permission['app_module_id'],
                    ],
                    [
                        'can_view' => $canView,
                        'can_create' => $canView
                            && (bool) $permission['can_create'],
                        'can_update' => $canView
                            && (bool) $permission['can_update'],
                        'can_delete' => $canView
                            && (bool) $permission['can_delete'],
                    ]
                );
            }

            return BusinessMemberPermission::query()
                ->where('business_member_id', $member->id)
                ->with('module:id,name,slug')
                ->get();
        });

        return response()->json([
            'message' => 'Member permissions updated successfully.',
            'business_id' => $business->id,
            'member_id' => $member->id,
            'permissions' => $savedPermissions,
        ]);
    }
}
