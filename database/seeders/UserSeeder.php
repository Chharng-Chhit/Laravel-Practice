<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['id' => 1, 'name' => 'POS Administrator', 'email' => 'cashier1@example.com', 'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'active'],
            ['id' => 2, 'name' => 'Cashier 2', 'email' => 'cashier2@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 3, 'name' => 'Cashier 3', 'email' => 'cashier3@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 4, 'name' => 'Cashier 4', 'email' => 'cashier4@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 5, 'name' => 'Cashier 5', 'email' => 'cashier5@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 6, 'name' => 'Cashier 6', 'email' => 'cashier6@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 7, 'name' => 'Cashier 7', 'email' => 'cashier7@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 8, 'name' => 'Cashier 8', 'email' => 'cashier8@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 9, 'name' => 'Cashier 9', 'email' => 'cashier9@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 10, 'name' => 'Cashier 10', 'email' => 'cashier10@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 11, 'name' => 'Cashier 11', 'email' => 'cashier11@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 12, 'name' => 'Cashier 12', 'email' => 'cashier12@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 13, 'name' => 'Cashier 13', 'email' => 'cashier13@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 14, 'name' => 'Cashier 14', 'email' => 'cashier14@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 15, 'name' => 'Cashier 15', 'email' => 'cashier15@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 16, 'name' => 'Cashier 16', 'email' => 'cashier16@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 17, 'name' => 'Cashier 17', 'email' => 'cashier17@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 18, 'name' => 'Cashier 18', 'email' => 'cashier18@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 19, 'name' => 'Cashier 19', 'email' => 'cashier19@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
            ['id' => 20, 'name' => 'Cashier 20', 'email' => 'cashier20@example.com', 'password' => Hash::make('password'), 'role' => 'cashier', 'status' => 'active'],
        ];

        User::upsert($users, ['id'], ['name', 'email', 'password', 'role', 'status']);
    }
}
