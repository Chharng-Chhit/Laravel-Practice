<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return StockMovement::with('product')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(StockMovement::create($request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
            'type' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ])), 201);
    }

    public function show(string $id): StockMovement
    {
        return StockMovement::with('product')->findOrFail($id);
    }

    public function update(Request $request, string $id): StockMovement
    {
        $movement = StockMovement::findOrFail($id);
        $movement->update($request->validate([
            'product_id' => ['sometimes', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer'],
            'type' => ['sometimes', 'string', 'max:255'],
            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]));

        return $movement->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        StockMovement::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
