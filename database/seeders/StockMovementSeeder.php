<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $stockMovements = [];

        for ($id = 1; $id <= 20; $id++) {
            $stockMovements[] = [
                'id' => $id,
                'product_id' => $id,
                'quantity' => 50 + $id,
                'type' => 'in',
                'reference' => 'OPENING-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                'note' => 'Opening stock for example data.',
            ];
        }

        StockMovement::upsert($stockMovements, ['id'], ['product_id', 'quantity', 'type', 'reference', 'note']);
    }
}
