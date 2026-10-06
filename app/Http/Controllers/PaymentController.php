<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sale_id' => ['sometimes', 'nullable', 'integer', 'exists:sales,id'],
            'payment_method' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return Payment::with('sale')
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%");
            }))
            ->when($filters['sale_id'] ?? null, fn($query, $saleId) => $query->where('sale_id', $saleId))
            ->when($filters['payment_method'] ?? null, fn($query, $method) => $query->where('payment_method', $method))
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Payment::create($request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'payment_method' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['sometimes', 'date'],
        ])), 201);
    }

    public function show(string $id): Payment
    {
        return Payment::with('sale')->findOrFail($id);
    }

    public function update(Request $request, string $id): Payment
    {
        $payment = Payment::findOrFail($id);
        $payment->update($request->validate([
            'sale_id' => ['sometimes', 'exists:sales,id'],
            'payment_method' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'reference_no' => ['sometimes', 'nullable', 'string', 'max:255'],
            'paid_at' => ['sometimes', 'date'],
        ]));

        return $payment->refresh();
    }

    public function destroy(string $id): JsonResponse
    {
        Payment::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
