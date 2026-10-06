<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [];
        $paymentMethods = ['cash', 'card', 'mobile'];

        for ($id = 1; $id <= 20; $id++) {
            $total = 10 + ($id * 2.50);
            $discount = $id % 4 === 0 ? 2.00 : 0.00;

            $payments[] = [
                'id' => $id,
                'sale_id' => $id,
                'payment_method' => $paymentMethods[($id - 1) % count($paymentMethods)],
                'amount' => $total - $discount,
                'reference_no' => 'PAY-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT),
                'paid_at' => now()->toDateString(),
            ];
        }

        Payment::upsert($payments, ['id'], ['sale_id', 'payment_method', 'amount', 'reference_no', 'paid_at']);
    }
}
