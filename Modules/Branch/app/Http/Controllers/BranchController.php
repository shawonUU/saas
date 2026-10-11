<?php

namespace Modules\Branch\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Branch\App\Models\Branch;
use Modules\Business\App\Models\Business;

class BranchController extends Controller
{
    private function getMembership(Request $request, int $businessId)
    {
        return DB::table('business_members')
            ->where('business_id', $businessId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'Active')
            ->first();
    }

    private function getBusinessBranch(int $businessId, int $branchId): Branch
    {
        return Branch::query()
            ->where('business_id', $businessId)
            ->findOrFail($branchId);
    }

    public function index(Request $request, int $businessId)
    {
        if (!$this->getMembership($request, $businessId)) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        $branches = Branch::query()
            ->where('business_id', $businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return response()->json([
            'branches' => $branches,
        ]);
    }

    public function store(Request $request, int $businessId)
    {
        $membership = $this->getMembership($request, $businessId);

        if (!$membership) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        if ($membership->role !== 'Owner') {
            return response()->json([
                'message' => 'Only the business owner can manage branches.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('business_id', $businessId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $business = Business::findOrFail($businessId);

        $branch = $business->branches()->create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_default' => false,
            'status' => 'Active',
        ]);

        return response()->json([
            'message' => 'Branch created successfully.',
            'branch' => $branch,
        ], 201);
    }

    public function show(Request $request, int $businessId, int $branchId)
    {
        if (!$this->getMembership($request, $businessId)) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        $branch = $this->getBusinessBranch($businessId, $branchId);

        return response()->json([
            'branch' => $branch,
        ]);
    }

    public function update(
        Request $request,
        int $businessId,
        int $branchId
    ) {
        $membership = $this->getMembership($request, $businessId);

        if (!$membership) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        if ($membership->role !== 'Owner') {
            return response()->json([
                'message' => 'Only the business owner can manage branches.',
            ], 403);
        }

        $branch = $this->getBusinessBranch($businessId, $branchId);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('business_id', $businessId)
                    ->ignore($branch->id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in(['Active', 'Inactive'])],
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'branch' => $branch->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        int $businessId,
        int $branchId
    ) {
        $membership = $this->getMembership($request, $businessId);

        if (!$membership) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], 403);
        }

        if ($membership->role !== 'Owner') {
            return response()->json([
                'message' => 'Only the business owner can manage branches.',
            ], 403);
        }

        $branch = $this->getBusinessBranch($businessId, $branchId);

        if ($branch->is_default) {
            return response()->json([
                'message' => 'The default branch cannot be deleted.',
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
