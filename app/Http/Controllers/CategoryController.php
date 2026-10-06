<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $sortBy = $request->input('sortBy', 'id');
        $order = $request->input('order', 'desc');

        return Category::withCount('products')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Category::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ])), 201);
    }

    public function show(string $id): Category
    {
        return Category::with('products')->findOrFail($id);
    }

    public function update(Request $request, string $id): Category
    {
        $category = Category::findOrFail($id);
        $category->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]));

        return $category->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        Category::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
