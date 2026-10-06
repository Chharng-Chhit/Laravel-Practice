<?php

namespace Database\Seeders;

use App\Models\SaleItem;
use Illuminate\Database\Seeder;

class SaleItemSeeder extends Seeder
{
    public function run(): void
    {
        $saleItems = [];

        for ($id = 1; $id <= 20; $id++) {
            $quantity = (($id - 1) % 3) + 1;
            $unitPrice = 1 + ($id * 0.50);

            $saleItems[] = [
                'id' => $id,
                'sale_id' => $id,
                'product_id' => $id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $quantity,
            ];
        }

        SaleItem::upsert($saleItems, ['id'], ['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal']);
    }
}
