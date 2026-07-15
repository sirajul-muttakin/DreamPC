<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed initial Admin User
        User::updateOrCreate(
            ['email' => 'admin@dreampc.com'],
            [
                'name' => 'DreamPC Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Seed initial Customer User
        User::updateOrCreate(
            ['email' => 'customer@dreampc.com'],
            [
                'name' => 'John Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        // Seed Default Hardware Categories
        $categories = [
            'CPU' => 'cpu',
            'GPU' => 'gpu',
            'RAM' => 'ram',
            'Motherboard' => 'motherboard',
            'Storage' => 'storage',
            'PSU' => 'psu',
            'Case' => 'case',
        ];

        foreach ($categories as $name => $slug) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'parent_id' => null,
                ]
            );
        }

        // Seed Product Inventory (must run after categories exist)
        $this->call(ProductSeeder::class);

        // Seed Sample Promotional Coupons
        \App\Models\Coupon::updateOrCreate(
            ['code' => 'SAVE10'],
            [
                'discount_type' => 'percent',
                'value' => 10.00,
                'min_spend' => 100.00,
                'expires_at' => now()->addDays(60),
            ]
        );

        \App\Models\Coupon::updateOrCreate(
            ['code' => 'BUILD50'],
            [
                'discount_type' => 'fixed',
                'value' => 50.00,
                'min_spend' => 500.00,
                'expires_at' => now()->addDays(60),
            ]
        );
    }
}