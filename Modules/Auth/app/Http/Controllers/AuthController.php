<?php

namespace Modules\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Business\App\Models\Business;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'account_type' => 'shop_owner',
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $tenantSlug = $request->header('X-Tenant-Slug', 'main');

        if ($tenantSlug === 'main') {
            return $this->mainLogin($user);
        }

        return $this->businessLogin($user, $tenantSlug);
    }

    private function mainLogin(User $user)
    {
        $token = $user->createToken(
            'main-account',
            ['main']
        )->plainTextToken;

        $redirectTo = $user->account_type === 'platform_admin'
            ? '/admin'
            : '/dashboard';

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
            'redirect_to' => $redirectTo,
        ]);
    }

    private function businessLogin(User $user, string $tenantSlug)
    {
        $business = Business::query()
            ->where('slug', $tenantSlug)
            ->where('status', 'Active')
            ->first();

        if (!$business) {
            return response()->json([
                'message' => 'Business not found or inactive.',
            ], 404);
        }

        $membership = $business->members()
            ->where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        $isOwner = (int) $business->owner_id === (int) $user->id;

        if (!$isOwner && !$membership) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        $businessRole = $isOwner
            ? 'Owner'
            : $membership->role;

        $token = $user->createToken(
            'business-' . $business->id,
            ['business:' . $business->id]
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
            'business' => $business,
            'business_role' => $businessRole,
            'redirect_to' => '/dashboard',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}