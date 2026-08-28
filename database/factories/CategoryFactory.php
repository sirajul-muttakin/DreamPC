<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'Case', 'PSU', 'Cooler',
        ]) . ' ' . fake()->unique()->numberBetween(1, 10000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null,
        ];
    }
}