<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Models\Business;
use Illuminate\Support\Facades\Password;

class BusinessUserRegistrationController extends Controller
{
    public function store(Request $request, int $businessId): JsonResponse
    {
        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->firstOrFail();

        $actor = DB::table('business_members')
            ->where('business_id', $business->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'Active')
            ->first();

        abort_unless($actor, 403, 'You are not a member of this business.');

        $isOwner = $actor->role === 'Owner'
            && (int) $business->owner_id === (int) $request->user()->id;

        $isAdmin = $actor->role === 'Admin';

        abort_unless($isOwner || $isAdmin, 403, 'Only Owner or Admin can create users.');

        $roles = $isOwner
            ? ['Admin', 'Manager', 'Cashier', 'Accountant', 'Staff']
            : ['Manager', 'Cashier', 'Accountant', 'Staff'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
        ]);

        $result = DB::transaction(function () use ($business, $validated) {
            DB::table('businesses')
                ->where('id', $business->id)
                ->lockForUpdate()
                ->first();

            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->save();

            $memberId = DB::table('business_members')->insertGetId([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'user_id' => $user->id,
                'member_id' => $memberId,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
            ];
        });

        return response()->json([
            'message' => 'User created and assigned to the business successfully.',
            'data' => $result,
        ], 201);
    }
}
