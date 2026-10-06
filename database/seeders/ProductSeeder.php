<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['id' => 1, 'category_id' => 1, 'name' => 'Mineral Water', 'barcode' => '890123450001', 'sku' => 'P0001', 'cost_price' => 0.50, 'selling_price' => 1.00, 'stock_quantity' => 51, 'status' => 'active'],
            ['id' => 2, 'category_id' => 2, 'name' => 'Potato Chips', 'barcode' => '890123450002', 'sku' => 'P0002', 'cost_price' => 1.00, 'selling_price' => 1.75, 'stock_quantity' => 52, 'status' => 'active'],
            ['id' => 3, 'category_id' => 3, 'name' => 'Canned Tuna', 'barcode' => '890123450003', 'sku' => 'P0003', 'cost_price' => 2.00, 'selling_price' => 3.25, 'stock_quantity' => 53, 'status' => 'active'],
            ['id' => 4, 'category_id' => 4, 'name' => 'Fresh Milk', 'barcode' => '890123450004', 'sku' => 'P0004', 'cost_price' => 1.50, 'selling_price' => 2.25, 'stock_quantity' => 54, 'status' => 'active'],
            ['id' => 5, 'category_id' => 5, 'name' => 'White Bread', 'barcode' => '890123450005', 'sku' => 'P0005', 'cost_price' => 1.25, 'selling_price' => 2.00, 'stock_quantity' => 55, 'status' => 'active'],
            ['id' => 6, 'category_id' => 6, 'name' => 'Red Apple', 'barcode' => '890123450006', 'sku' => 'P0006', 'cost_price' => 0.75, 'selling_price' => 1.50, 'stock_quantity' => 56, 'status' => 'active'],
            ['id' => 7, 'category_id' => 7, 'name' => 'Frozen Pizza', 'barcode' => '890123450007', 'sku' => 'P0007', 'cost_price' => 3.50, 'selling_price' => 5.00, 'stock_quantity' => 57, 'status' => 'active'],
            ['id' => 8, 'category_id' => 8, 'name' => 'Shampoo', 'barcode' => '890123450008', 'sku' => 'P0008', 'cost_price' => 2.50, 'selling_price' => 3.75, 'stock_quantity' => 58, 'status' => 'active'],
            ['id' => 9, 'category_id' => 9, 'name' => 'Laundry Soap', 'barcode' => '890123450009', 'sku' => 'P0009', 'cost_price' => 2.00, 'selling_price' => 3.00, 'stock_quantity' => 59, 'status' => 'active'],
            ['id' => 10, 'category_id' => 10, 'name' => 'Ballpoint Pen', 'barcode' => '890123450010', 'sku' => 'P0010', 'cost_price' => 0.25, 'selling_price' => 0.75, 'stock_quantity' => 60, 'status' => 'active'],
            ['id' => 11, 'category_id' => 11, 'name' => 'Dog Food', 'barcode' => '890123450011', 'sku' => 'P0011', 'cost_price' => 4.00, 'selling_price' => 5.50, 'stock_quantity' => 61, 'status' => 'active'],
            ['id' => 12, 'category_id' => 12, 'name' => 'Baby Diapers', 'barcode' => '890123450012', 'sku' => 'P0012', 'cost_price' => 5.00, 'selling_price' => 7.00, 'stock_quantity' => 62, 'status' => 'active'],
            ['id' => 13, 'category_id' => 13, 'name' => 'Tomato Sauce', 'barcode' => '890123450013', 'sku' => 'P0013', 'cost_price' => 1.25, 'selling_price' => 2.00, 'stock_quantity' => 63, 'status' => 'active'],
            ['id' => 14, 'category_id' => 14, 'name' => 'Corn Flakes', 'barcode' => '890123450014', 'sku' => 'P0014', 'cost_price' => 2.50, 'selling_price' => 3.75, 'stock_quantity' => 64, 'status' => 'active'],
            ['id' => 15, 'category_id' => 15, 'name' => 'Jasmine Rice', 'barcode' => '890123450015', 'sku' => 'P0015', 'cost_price' => 6.00, 'selling_price' => 8.00, 'stock_quantity' => 65, 'status' => 'active'],
            ['id' => 16, 'category_id' => 16, 'name' => 'Chicken Breast', 'barcode' => '890123450016', 'sku' => 'P0016', 'cost_price' => 4.50, 'selling_price' => 6.50, 'stock_quantity' => 66, 'status' => 'active'],
            ['id' => 17, 'category_id' => 17, 'name' => 'Vitamin C', 'barcode' => '890123450017', 'sku' => 'P0017', 'cost_price' => 3.00, 'selling_price' => 4.50, 'stock_quantity' => 67, 'status' => 'active'],
            ['id' => 18, 'category_id' => 18, 'name' => 'Glass Cleaner', 'barcode' => '890123450018', 'sku' => 'P0018', 'cost_price' => 1.75, 'selling_price' => 2.75, 'stock_quantity' => 68, 'status' => 'active'],
            ['id' => 19, 'category_id' => 19, 'name' => 'USB Cable', 'barcode' => '890123450019', 'sku' => 'P0019', 'cost_price' => 3.50, 'selling_price' => 5.00, 'stock_quantity' => 69, 'status' => 'active'],
            ['id' => 20, 'category_id' => 20, 'name' => 'Reusable Bag', 'barcode' => '890123450020', 'sku' => 'P0020', 'cost_price' => 0.50, 'selling_price' => 1.00, 'stock_quantity' => 70, 'status' => 'active'],
            ['id' => 21, 'category_id' => 1, 'name' => 'Orange Juice', 'barcode' => '890123450021', 'sku' => 'P0021', 'cost_price' => 2.00, 'selling_price' => 3.00, 'stock_quantity' => 71, 'status' => 'active'],
            ['id' => 22, 'category_id' => 2, 'name' => 'Chocolate Bar', 'barcode' => '890123450022', 'sku' => 'P0022', 'cost_price' => 0.75, 'selling_price' => 1.50, 'stock_quantity' => 72, 'status' => 'active'],
            ['id' => 23, 'category_id' => 3, 'name' => 'Canned Corn', 'barcode' => '890123450023', 'sku' => 'P0023', 'cost_price' => 1.25, 'selling_price' => 2.00, 'stock_quantity' => 73, 'status' => 'active'],
            ['id' => 24, 'category_id' => 4, 'name' => 'Yogurt Cup', 'barcode' => '890123450024', 'sku' => 'P0024', 'cost_price' => 0.80, 'selling_price' => 1.50, 'stock_quantity' => 74, 'status' => 'active'],
            ['id' => 25, 'category_id' => 5, 'name' => 'Croissant', 'barcode' => '890123450025', 'sku' => 'P0025', 'cost_price' => 1.00, 'selling_price' => 1.75, 'stock_quantity' => 75, 'status' => 'active'],
            ['id' => 26, 'category_id' => 6, 'name' => 'Banana', 'barcode' => '890123450026', 'sku' => 'P0026', 'cost_price' => 0.50, 'selling_price' => 1.00, 'stock_quantity' => 76, 'status' => 'active'],
            ['id' => 27, 'category_id' => 7, 'name' => 'Frozen Fries', 'barcode' => '890123450027', 'sku' => 'P0027', 'cost_price' => 2.00, 'selling_price' => 3.25, 'stock_quantity' => 77, 'status' => 'active'],
            ['id' => 28, 'category_id' => 8, 'name' => 'Hand Lotion', 'barcode' => '890123450028', 'sku' => 'P0028', 'cost_price' => 2.25, 'selling_price' => 3.50, 'stock_quantity' => 78, 'status' => 'active'],
            ['id' => 29, 'category_id' => 9, 'name' => 'Dishwashing Liquid', 'barcode' => '890123450029', 'sku' => 'P0029', 'cost_price' => 1.50, 'selling_price' => 2.50, 'stock_quantity' => 79, 'status' => 'active'],
            ['id' => 30, 'category_id' => 10, 'name' => 'Notebook', 'barcode' => '890123450030', 'sku' => 'P0030', 'cost_price' => 1.00, 'selling_price' => 1.75, 'stock_quantity' => 80, 'status' => 'active'],
            ['id' => 31, 'category_id' => 11, 'name' => 'Cat Food', 'barcode' => '890123450031', 'sku' => 'P0031', 'cost_price' => 3.50, 'selling_price' => 5.00, 'stock_quantity' => 81, 'status' => 'active'],
            ['id' => 32, 'category_id' => 12, 'name' => 'Baby Wipes', 'barcode' => '890123450032', 'sku' => 'P0032', 'cost_price' => 1.75, 'selling_price' => 2.75, 'stock_quantity' => 82, 'status' => 'active'],
            ['id' => 33, 'category_id' => 13, 'name' => 'Soy Sauce', 'barcode' => '890123450033', 'sku' => 'P0033', 'cost_price' => 1.25, 'selling_price' => 2.00, 'stock_quantity' => 83, 'status' => 'active'],
            ['id' => 34, 'category_id' => 14, 'name' => 'Instant Oatmeal', 'barcode' => '890123450034', 'sku' => 'P0034', 'cost_price' => 2.00, 'selling_price' => 3.25, 'stock_quantity' => 84, 'status' => 'active'],
            ['id' => 35, 'category_id' => 15, 'name' => 'Spaghetti', 'barcode' => '890123450035', 'sku' => 'P0035', 'cost_price' => 1.50, 'selling_price' => 2.50, 'stock_quantity' => 85, 'status' => 'active'],
            ['id' => 36, 'category_id' => 16, 'name' => 'Beef Steak', 'barcode' => '890123450036', 'sku' => 'P0036', 'cost_price' => 7.00, 'selling_price' => 10.00, 'stock_quantity' => 86, 'status' => 'active'],
            ['id' => 37, 'category_id' => 17, 'name' => 'Pain Relief Tablets', 'barcode' => '890123450037', 'sku' => 'P0037', 'cost_price' => 2.00, 'selling_price' => 3.50, 'stock_quantity' => 87, 'status' => 'active'],
            ['id' => 38, 'category_id' => 18, 'name' => 'Disinfectant Spray', 'barcode' => '890123450038', 'sku' => 'P0038', 'cost_price' => 2.50, 'selling_price' => 4.00, 'stock_quantity' => 88, 'status' => 'active'],
            ['id' => 39, 'category_id' => 19, 'name' => 'Phone Charger', 'barcode' => '890123450039', 'sku' => 'P0039', 'cost_price' => 5.00, 'selling_price' => 7.50, 'stock_quantity' => 89, 'status' => 'active'],
            ['id' => 40, 'category_id' => 20, 'name' => 'Umbrella', 'barcode' => '890123450040', 'sku' => 'P0040', 'cost_price' => 4.00, 'selling_price' => 6.00, 'stock_quantity' => 90, 'status' => 'active'],
            ['id' => 41, 'category_id' => 1, 'name' => 'Sparkling Water', 'barcode' => '890123450041', 'sku' => 'P0041', 'cost_price' => 0.75, 'selling_price' => 1.50, 'stock_quantity' => 91, 'status' => 'active'],
            ['id' => 42, 'category_id' => 2, 'name' => 'Cheese Crackers', 'barcode' => '890123450042', 'sku' => 'P0042', 'cost_price' => 1.25, 'selling_price' => 2.25, 'stock_quantity' => 92, 'status' => 'active'],
            ['id' => 43, 'category_id' => 3, 'name' => 'Canned Beans', 'barcode' => '890123450043', 'sku' => 'P0043', 'cost_price' => 1.00, 'selling_price' => 1.75, 'stock_quantity' => 93, 'status' => 'active'],
            ['id' => 44, 'category_id' => 4, 'name' => 'Cheddar Cheese', 'barcode' => '890123450044', 'sku' => 'P0044', 'cost_price' => 2.50, 'selling_price' => 3.75, 'stock_quantity' => 94, 'status' => 'active'],
            ['id' => 45, 'category_id' => 5, 'name' => 'Muffin', 'barcode' => '890123450045', 'sku' => 'P0045', 'cost_price' => 1.00, 'selling_price' => 1.75, 'stock_quantity' => 95, 'status' => 'active'],
            ['id' => 46, 'category_id' => 6, 'name' => 'Green Grapes', 'barcode' => '890123450046', 'sku' => 'P0046', 'cost_price' => 1.50, 'selling_price' => 2.50, 'stock_quantity' => 96, 'status' => 'active'],
            ['id' => 47, 'category_id' => 7, 'name' => 'Frozen Vegetables', 'barcode' => '890123450047', 'sku' => 'P0047', 'cost_price' => 2.25, 'selling_price' => 3.50, 'stock_quantity' => 97, 'status' => 'active'],
            ['id' => 48, 'category_id' => 8, 'name' => 'Toothpaste', 'barcode' => '890123450048', 'sku' => 'P0048', 'cost_price' => 1.50, 'selling_price' => 2.50, 'stock_quantity' => 98, 'status' => 'active'],
            ['id' => 49, 'category_id' => 9, 'name' => 'Paper Towels', 'barcode' => '890123450049', 'sku' => 'P0049', 'cost_price' => 2.00, 'selling_price' => 3.25, 'stock_quantity' => 99, 'status' => 'active'],
            ['id' => 50, 'category_id' => 10, 'name' => 'Marker Set', 'barcode' => '890123450050', 'sku' => 'P0050', 'cost_price' => 1.75, 'selling_price' => 2.75, 'stock_quantity' => 100, 'status' => 'active'],
        ];

        Product::upsert($products, ['id'], ['category_id', 'name', 'barcode', 'sku', 'cost_price', 'selling_price', 'stock_quantity', 'status']);
    }
}
