<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return Product::with('category')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:5'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'max:255'],
        ]);

        return response()->json(Product::create($validated), 201);
    }

    public function show(string $id): Product
    {
        return Product::with(['category', 'stockMovements', 'sellItems'])->findOrFail($id);
    }

    public function update(Request $request, string $id): Product
    {
        $product = Product::findOrFail($id);
        $product->update($request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'barcode' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:5'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'max:255'],
        ]));

        return $product->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        Product::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
