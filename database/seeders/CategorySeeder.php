<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Beverages', 'description' => 'Drinks and beverages'],
            ['id' => 2, 'name' => 'Snacks', 'description' => 'Packaged snacks'],
            ['id' => 3, 'name' => 'Canned Goods', 'description' => 'Canned food products'],
            ['id' => 4, 'name' => 'Dairy', 'description' => 'Milk and dairy products'],
            ['id' => 5, 'name' => 'Bakery', 'description' => 'Bread and bakery products'],
            ['id' => 6, 'name' => 'Produce', 'description' => 'Fresh fruits and vegetables'],
            ['id' => 7, 'name' => 'Frozen Food', 'description' => 'Frozen food products'],
            ['id' => 8, 'name' => 'Personal Care', 'description' => 'Personal care products'],
            ['id' => 9, 'name' => 'Household', 'description' => 'Household products'],
            ['id' => 10, 'name' => 'Stationery', 'description' => 'Stationery products'],
            ['id' => 11, 'name' => 'Pet Supplies', 'description' => 'Products for pets'],
            ['id' => 12, 'name' => 'Baby Care', 'description' => 'Baby care products'],
            ['id' => 13, 'name' => 'Condiments', 'description' => 'Sauces and condiments'],
            ['id' => 14, 'name' => 'Breakfast', 'description' => 'Breakfast products'],
            ['id' => 15, 'name' => 'Pasta and Rice', 'description' => 'Pasta and rice products'],
            ['id' => 16, 'name' => 'Meat and Seafood', 'description' => 'Meat and seafood products'],
            ['id' => 17, 'name' => 'Health Products', 'description' => 'Health and wellness products'],
            ['id' => 18, 'name' => 'Cleaning Supplies', 'description' => 'Cleaning products'],
            ['id' => 19, 'name' => 'Electronics', 'description' => 'Small electronic products'],
            ['id' => 20, 'name' => 'Other', 'description' => 'Other products'],
        ];

        Category::upsert($categories, ['id'], ['name', 'description']);
    }
}
