<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'brand' => fake()->company(),
            'price' => fake()->randomFloat(2, 20, 2000),
            'stock_quantity' => fake()->numberBetween(0, 50),
            'description' => fake()->sentence(),
            'image_path' => null,
        ];
    }
}