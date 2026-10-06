<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cashier_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return Sale::with(['user', 'saleItems', 'payments'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('invoice_no', 'like', "%{$search}%"))
            ->when($filters['cashier_id'] ?? null, fn ($query, $cashierId) => $query->where('cashier_id', $cashierId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Sale::create($request->validate([
            'cashier_id' => ['required', 'exists:users,id'],
            'invoice_no' => ['required', 'string', 'max:255'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'discounts' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'max:255'],
        ])), 201);
    }

    public function show(string $id): Sale
    {
        return Sale::with(['user', 'saleItems', 'payments'])->findOrFail($id);
    }

    public function update(Request $request, string $id): Sale
    {
        $sale = Sale::findOrFail($id);
        $sale->update($request->validate([
            'cashier_id' => ['sometimes', 'exists:users,id'],
            'invoice_no' => ['sometimes', 'string', 'max:255'],
            'total' => ['sometimes', 'numeric', 'min:0'],
            'discounts' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'max:255'],
        ]));

        return $sale->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        Sale::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
