<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessRoleController extends Controller
{
    private function owner(Request $request, int $businessId): void
    {
        $business = DB::table('businesses')
            ->where('id', $businessId)
            ->first();

        abort_unless($business, 404, 'Business not found.');
        abort_unless(
            (int) $business->owner_id === (int) $request->user()->id,
            403,
            'Only the business owner can manage roles.'
        );
    }

    public function index(Request $request, int $businessId): JsonResponse
    {
        $this->owner($request, $businessId);

        $roles = DB::table('business_roles')
            ->where('business_id', $businessId)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $roles]);
    }

    public function store(Request $request, int $businessId): JsonResponse
    {
        $this->owner($request, $businessId);

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('business_roles', 'name')
                    ->where('business_id', $businessId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $name = trim($data['name']);
        $slug = Str::slug($name);

        if (!$slug || $slug === 'owner') {
            return response()->json([
                'message' => 'Invalid or reserved role name.'
            ], 422);
        }

        if (DB::table('business_roles')
            ->where('business_id', $businessId)
            ->where('slug', $slug)
            ->exists()) {
            return response()->json([
                'message' => 'A role with this slug already exists.'
            ], 422);
        }

        $id = DB::table('business_roles')->insertGetId([
            'business_id' => $businessId,
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => DB::table('business_roles')->where('id', $id)->first(),
        ], 201);
    }

    public function update(
        Request $request,
        int $businessId,
        int $roleId
    ): JsonResponse {
        $this->owner($request, $businessId);

        $role = DB::table('business_roles')
            ->where('business_id', $businessId)
            ->where('id', $roleId)
            ->first();

        abort_unless($role, 404, 'Role not found.');

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('business_roles', 'name')
                    ->where('business_id', $businessId)
                    ->ignore($roleId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $slug = Str::slug(trim($data['name']));

        if (!$slug || $slug === 'owner') {
            return response()->json([
                'message' => 'Invalid or reserved role name.'
            ], 422);
        }

        if (DB::table('business_roles')
            ->where('business_id', $businessId)
            ->where('slug', $slug)
            ->where('id', '<>', $roleId)
            ->exists()) {
            return response()->json([
                'message' => 'A role with this slug already exists.'
            ], 422);
        }

        DB::table('business_roles')
            ->where('id', $roleId)
            ->update([
                'name' => trim($data['name']),
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => DB::table('business_roles')->where('id', $roleId)->first(),
        ]);
    }

    public function permissions(
        Request $request,
        int $businessId,
        int $roleId
    ): JsonResponse {
        $this->owner($request, $businessId);

        $role = DB::table('business_roles')
            ->where('business_id', $businessId)
            ->where('id', $roleId)
            ->first();

        abort_unless($role, 404, 'Role not found.');

        return response()->json([
            'data' => [
                'role' => $role,
                'permissions' => DB::table('business_role_permissions')
                    ->where('business_role_id', $roleId)
                    ->get(),
            ],
        ]);
    }

    public function savePermissions(
        Request $request,
        int $businessId,
        int $roleId
    ): JsonResponse {
        $this->owner($request, $businessId);

        $role = DB::table('business_roles')
            ->where('business_id', $businessId)
            ->where('id', $roleId)
            ->first();

        abort_unless($role, 404, 'Role not found.');

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*.permission_type' => [
                'required', Rule::in(['module', 'menu', 'child_menu'])
            ],
            'permissions.*.permission_item_id' => [
                'required', 'integer', 'min:1'
            ],
            'permissions.*.can_view' => ['required', 'boolean'],
            'permissions.*.can_create' => ['required', 'boolean'],
            'permissions.*.can_update' => ['required', 'boolean'],
            'permissions.*.can_delete' => ['required', 'boolean'],
        ]);

        $allowedModules = DB::table('business_roles as br')
            ->join('businesses as b', 'b.id', '=', 'br.business_id')
            ->join('business_category_modules as bcm',
                'bcm.business_category_id', '=', 'b.category_id')
            ->where('br.id', $roleId)
            ->where('b.id', $businessId)
            ->pluck('bcm.app_module_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowedMenus = DB::table('app_menus')
            ->whereIn('app_module_id', $allowedModules)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowedChildren = DB::table('app_child_menus')
            ->whereIn('app_menu_id', $allowedMenus)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowed = [
            'module' => $allowedModules,
            'menu' => $allowedMenus,
            'child_menu' => $allowedChildren,
        ];

        $rows = [];
        $seen = [];

        foreach ($data['permissions'] as $permission) {
            $type = $permission['permission_type'];
            $itemId = (int) $permission['permission_item_id'];

            if (!in_array($itemId, $allowed[$type], true)) {
                return response()->json([
                    'message' => 'Invalid permission item for this business.'
                ], 422);
            }

            $key = $type . ':' . $itemId;

            if (isset($seen[$key])) {
                return response()->json([
                    'message' => 'Duplicate permission item.'
                ], 422);
            }

            $seen[$key] = true;

            $view = (bool) $permission['can_view'];

            $rows[] = [
                'business_role_id' => $roleId,
                'permission_type' => $type,
                'permission_item_id' => $itemId,
                'can_view' => $view,
                'can_create' => $view && (bool) $permission['can_create'],
                'can_update' => $view && (bool) $permission['can_update'],
                'can_delete' => $view && (bool) $permission['can_delete'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($roleId, $rows) {
            DB::table('business_role_permissions')
                ->where('business_role_id', $roleId)
                ->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('business_role_permissions')->insert($chunk);
            }
        });

        return response()->json([
            'message' => 'Role permissions saved successfully.'
        ]);
    }
}
