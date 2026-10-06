<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'sale_id' => ['sometimes', 'nullable', 'integer', 'exists:sales,id'],
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return SaleItem::with(['sale', 'product'])
            ->when($filters['sale_id'] ?? null, fn ($query, $saleId) => $query->where('sale_id', $saleId))
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(SaleItem::create($request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ])), 201);
    }

    public function show(string $id): SaleItem
    {
        return SaleItem::with(['sale', 'product'])->findOrFail($id);
    }

    public function update(Request $request, string $id): SaleItem
    {
        $item = SaleItem::findOrFail($id);
        $item->update($request->validate([
            'sale_id' => ['sometimes', 'exists:sales,id'],
            'product_id' => ['sometimes', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
        ]));

        return $item->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        SaleItem::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
