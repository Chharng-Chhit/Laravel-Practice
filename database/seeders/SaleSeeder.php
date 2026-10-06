<?php

namespace Database\Seeders;

use App\Models\Sale;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $sales = [];

        for ($id = 1; $id <= 20; $id++) {
            $sales[] = [
                'id' => $id,
                'cashier_id' => $id === 20 ? 2 : $id + 1,
                'invoice_no' => 'INV-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT),
                'total' => 10 + ($id * 2.50),
                'discounts' => $id % 4 === 0 ? 2.00 : 0.00,
                'status' => 'completed',
            ];
        }

        Sale::upsert($sales, ['id'], ['cashier_id', 'invoice_no', 'total', 'discounts', 'status']);
    }
}
