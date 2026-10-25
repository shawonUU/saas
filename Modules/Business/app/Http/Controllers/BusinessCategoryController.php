<?php

namespace Modules\Business\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Models\BusinessCategory;

class BusinessCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusinessCategory::query();

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $isActive = filter_var(
                $request->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            100
        );

        $categories = $query
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:business_categories,slug'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $slug = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        $category = BusinessCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Business category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = BusinessCategory::findOrFail($id);

        return response()->json([
            'data' => $category,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = BusinessCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('business_categories', 'slug')
                    ->ignore($category->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (
            array_key_exists('name', $validated) &&
            !array_key_exists('slug', $validated)
        ) {
            $validated['slug'] = $this->generateUniqueSlug(
                $validated['name'],
                $category->id
            );
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Business category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = BusinessCategory::findOrFail($id);

        try {
            $category->delete();
        } catch (\Illuminate\Database\QueryException $exception) {
            if (
                in_array($exception->getCode(), ['23000', '23503'], true)
            ) {
                return response()->json([
                    'message' => 'This category cannot be deleted because it is associated with one or more businesses.',
                ], 409);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Business category deleted successfully.',
        ]);
    }

    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'A valid slug could not be generated. Please provide a valid name or slug.',
            ]);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            BusinessCategory::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
