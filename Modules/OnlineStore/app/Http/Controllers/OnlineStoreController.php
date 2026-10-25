<?php

namespace Modules\OnlineStore\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Business\App\Models\Business;
use Modules\OnlineStore\App\Models\OnlineStore;

class OnlineStoreController extends Controller
{
    public function index(Request $request, int $businessId): JsonResponse
    {
        $business = $this->ownedBusiness($request, $businessId);

        if (!$business) {
            return response()->json([
                'message' => 'Business not found or access denied.',
            ], 404);
        }

        $stores = OnlineStore::where('business_id', $business->id)
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'data' => $stores->items(),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
                'last_page' => $stores->lastPage(),
            ],
        ]);
    }

    public function store(Request $request, int $businessId): JsonResponse
    {
        $business = $this->ownedBusiness($request, $businessId);

        if (!$business) {
            return response()->json([
                'message' => 'Business not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:online_stores,slug',
            ],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'banner' => ['nullable', 'string', 'max:2048'],
            'theme_settings' => ['nullable', 'array'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        if ($slug === '') {
            return response()->json([
                'message' => 'A valid store slug could not be generated.',
                'errors' => [
                    'slug' => ['Please provide a valid store slug.'],
                ],
            ], 422);
        }

        if (OnlineStore::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => 'This store slug is already taken.',
                'errors' => [
                    'slug' => ['Please choose another store slug.'],
                ],
            ], 422);
        }

        $domainSuffix = trim(
            config('services.nexabiz.store_domain', 'nexabiz.com'),
            ". \t\n\r\0\x0B"
        );

        $domain = $slug . '.' . $domainSuffix;

        if (OnlineStore::where('domain', $domain)->exists()) {
            return response()->json([
                'message' => 'This store domain is already taken.',
                'errors' => [
                    'slug' => ['Please choose another store slug.'],
                ],
            ], 422);
        }

        $onlineStore = DB::transaction(function () use (
            $business,
            $validated,
            $slug,
            $domain
        ) {
            return OnlineStore::create([
                'business_id' => $business->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'domain' => $domain,
                'description' => $validated['description'] ?? null,
                'logo' => $validated['logo'] ?? null,
                'banner' => $validated['banner'] ?? null,
                'theme_settings' => $validated['theme_settings'] ?? null,
                'status' => 'draft',
            ]);
        });

        return response()->json([
            'message' => 'Online store created successfully.',
            'data' => $onlineStore,
            'store_url' => 'https://' . $onlineStore->domain,
        ], 201);
    }

    public function show(Request $request, int $storeId): JsonResponse
    {
        $onlineStore = $this->ownedStore($request, $storeId);

        if (!$onlineStore) {
            return response()->json([
                'message' => 'Online store not found or access denied.',
            ], 404);
        }

        return response()->json([
            'data' => $onlineStore->load('business'),
        ]);
    }

    public function update(Request $request, int $storeId): JsonResponse
    {
        $onlineStore = $this->ownedStore($request, $storeId);

        if (!$onlineStore) {
            return response()->json([
                'message' => 'Online store not found or access denied.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('online_stores', 'slug')
                    ->ignore($onlineStore->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'banner' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'theme_settings' => ['sometimes', 'nullable', 'array'],
        ]);

        if (
            isset($validated['slug']) &&
            $validated['slug'] !== $onlineStore->slug &&
            $onlineStore->status === 'published'
        ) {
            return response()->json([
                'message' => 'The slug of a published store cannot be changed.',
                'errors' => [
                    'slug' => [
                        'Unpublish or use another migration process before changing this slug.',
                    ],
                ],
            ], 422);
        }

        if (isset($validated['slug'])) {
            $domainSuffix = trim(
                config('services.nexabiz.store_domain', 'nexabiz.com'),
                ". \t\n\r\0\x0B"
            );

            $domain = $validated['slug'] . '.' . $domainSuffix;

            $domainExists = OnlineStore::where('domain', $domain)
                ->where('id', '!=', $onlineStore->id)
                ->exists();

            if ($domainExists) {
                return response()->json([
                    'message' => 'This store domain is already taken.',
                    'errors' => [
                        'slug' => ['Please choose another store slug.'],
                    ],
                ], 422);
            }

            $validated['domain'] = $domain;
        }

        $onlineStore->update($validated);

        return response()->json([
            'message' => 'Online store updated successfully.',
            'data' => $onlineStore->fresh(),
            'store_url' => 'https://' . $onlineStore->domain,
        ]);
    }

    public function publish(Request $request, int $storeId): JsonResponse
    {
        $onlineStore = $this->ownedStore($request, $storeId);

        if (!$onlineStore) {
            return response()->json([
                'message' => 'Online store not found or access denied.',
            ], 404);
        }

        if ($onlineStore->status === 'published') {
            return response()->json([
                'message' => 'Online store is already published.',
                'data' => $onlineStore,
                'store_url' => 'https://' . $onlineStore->domain,
            ]);
        }

        $onlineStore->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Online store published successfully.',
            'data' => $onlineStore->fresh(),
            'store_url' => 'https://' . $onlineStore->domain,
        ]);
    }

    private function ownedBusiness(
        Request $request,
        int $businessId
    ): ?Business {
        return Business::query()
            ->where('id', $businessId)
            ->where('owner_id', $request->user()->id)
            ->first();
    }

    private function ownedStore(
        Request $request,
        int $storeId
    ): ?OnlineStore {
        return OnlineStore::query()
            ->where('id', $storeId)
            ->whereHas('business', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->first();
    }
}
