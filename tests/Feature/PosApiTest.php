<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('POS API supports CRUD for each business resource', function () {
    $userResponse = $this->postJson('/api/users', [
        'name' => 'Test Cashier',
        'email' => 'cashier@example.com',
        'password' => 'password123',
        'role' => 'cashier',
        'status' => 'active',
    ])->assertCreated()->assertJsonMissingPath('password');
    $userId = $userResponse->json('id');
    $this->getJson('/api/users?search=cashier&role=cashier&status=active&per_page=1')
        ->assertOk()->assertJsonFragment(['email' => 'cashier@example.com'])->assertJsonPath('per_page', 1);
    $this->getJson("/api/users/{$userId}")->assertOk()->assertJsonPath('name', 'Test Cashier');
    $this->putJson("/api/users/{$userId}", ['name' => 'Updated Cashier'])
        ->assertOk()->assertJsonPath('name', 'Updated Cashier');
    expect(User::findOrFail($userId)->password)->not->toBe('password123');

    $categoryId = $this->postJson('/api/categories', ['name' => 'Drinks', 'description' => 'Beverages'])
        ->assertCreated()->json('id');
    $this->getJson('/api/categories?search=Drinks&per_page=1')
        ->assertOk()->assertJsonFragment(['name' => 'Drinks'])->assertJsonPath('total', 1);
    $this->getJson("/api/categories/{$categoryId}")->assertOk()->assertJsonPath('name', 'Drinks');
    $this->putJson("/api/categories/{$categoryId}", ['name' => 'Cold Drinks'])
        ->assertOk()->assertJsonPath('name', 'Cold Drinks');

    $productId = $this->postJson('/api/products', [
        'category_id' => $categoryId,
        'name' => 'Water',
        'barcode' => '12345',
        'sku' => 'WATER',
        'cost_price' => 1.25,
        'selling_price' => 2.50,
        'stock_quantity' => 10,
        'status' => 'active',
    ])->assertCreated()->json('id');
    $this->getJson("/api/products?search=12345&category_id={$categoryId}&status=active&per_page=1")
        ->assertOk()->assertJsonFragment(['name' => 'Water'])->assertJsonPath('total', 1);
    $this->getJson("/api/products/{$productId}")->assertOk()->assertJsonPath('category.id', $categoryId);
    $this->putJson("/api/products/{$productId}", ['selling_price' => 3])->assertOk();

    $movementId = $this->postJson('/api/stock-movements', [
        'product_id' => $productId,
        'quantity' => 10,
        'type' => 'in',
        'reference' => 'opening',
    ])->assertCreated()->json('id');
    $this->getJson("/api/stock-movements?search=opening&product_id={$productId}&type=in&per_page=1")
        ->assertOk()->assertJsonFragment(['type' => 'in'])->assertJsonPath('total', 1);
    $this->getJson("/api/stock-movements/{$movementId}")->assertOk()->assertJsonPath('product.id', $productId);
    $this->putJson("/api/stock-movements/{$movementId}", ['quantity' => 12])
        ->assertOk()->assertJsonPath('quantity', 12);

    $saleId = $this->postJson('/api/sales', [
        'cashier_id' => $userId,
        'invoice_no' => 'INV-001',
        'total' => 6,
        'discounts' => 0,
        'status' => 'completed',
    ])->assertCreated()->json('id');
    $this->getJson("/api/sales?search=INV-001&cashier_id={$userId}&status=completed&per_page=1")
        ->assertOk()->assertJsonFragment(['invoice_no' => 'INV-001'])->assertJsonPath('total', 1);
    $this->getJson("/api/sales/{$saleId}")->assertOk()->assertJsonPath('user.id', $userId);
    $this->getJson('/api/orders')->assertOk()->assertJsonFragment(['invoice_no' => 'INV-001']);
    $this->putJson("/api/sales/{$saleId}", ['status' => 'refunded'])
        ->assertOk()->assertJsonPath('status', 'refunded');

    $saleItemId = $this->postJson('/api/sale-items', [
        'sale_id' => $saleId,
        'product_id' => $productId,
        'quantity' => 2,
        'unit_price' => 3,
        'subtotal' => 6,
    ])->assertCreated()->json('id');
    $this->getJson("/api/sale-items?sale_id={$saleId}&product_id={$productId}&per_page=1")
        ->assertOk()->assertJsonFragment(['quantity' => 2])->assertJsonPath('total', 1);
    $this->getJson("/api/sale-items/{$saleItemId}")->assertOk()->assertJsonPath('product.id', $productId);
    $this->putJson("/api/sale-items/{$saleItemId}", ['quantity' => 3])
        ->assertOk()->assertJsonPath('quantity', 3);

    $paymentId = $this->postJson('/api/payments', [
        'sale_id' => $saleId,
        'payment_method' => 'cash',
        'amount' => 6,
        'reference_no' => 'PAY-001',
        'paid_at' => '2026-09-30',
    ])->assertCreated()->json('id');
    $this->getJson("/api/payments?search=PAY-001&sale_id={$saleId}&payment_method=cash&per_page=1")
        ->assertOk()->assertJsonFragment(['reference_no' => 'PAY-001'])->assertJsonPath('total', 1);
    $this->getJson("/api/payments/{$paymentId}")->assertOk()->assertJsonPath('sale.id', $saleId);
    $this->putJson("/api/payments/{$paymentId}", ['amount' => 7])
        ->assertOk()->assertJsonPath('amount', 7);

    $this->deleteJson("/api/payments/{$paymentId}")->assertNoContent();
    $this->deleteJson("/api/sale-items/{$saleItemId}")->assertNoContent();
    $this->deleteJson("/api/sales/{$saleId}")->assertNoContent();
    $this->deleteJson("/api/stock-movements/{$movementId}")->assertNoContent();
    $this->deleteJson("/api/products/{$productId}")->assertNoContent();
    $this->deleteJson("/api/categories/{$categoryId}")->assertNoContent();
    $this->deleteJson("/api/users/{$userId}")->assertNoContent();
});

test('POS API endpoints handle empty query parameters gracefully', function () {
    $this->getJson('/api/products?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/categories?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/sales?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/payments?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/stock-movements?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/users?page=1&per_page=15&search=')->assertOk();
    $this->getJson('/api/sale-items?page=1&per_page=15&search=')->assertOk();
});
