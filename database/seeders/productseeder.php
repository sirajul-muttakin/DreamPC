<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        $products = [
            'cpu' => [
                ['name' => 'AMD Ryzen 5 7600', 'brand' => 'AMD', 'price' => 229.99, 'stock' => 15,
                    'specs' => ['socket' => 'AM5', 'tdp' => '65W']],
                ['name' => 'AMD Ryzen 7 7800X3D', 'brand' => 'AMD', 'price' => 399.99, 'stock' => 10,
                    'specs' => ['socket' => 'AM5', 'tdp' => '120W']],
                ['name' => 'Intel Core i5-13400F', 'brand' => 'Intel', 'price' => 199.99, 'stock' => 20,
                    'specs' => ['socket' => 'LGA1700', 'tdp' => '65W']],
                ['name' => 'Intel Core i7-13700K', 'brand' => 'Intel', 'price' => 409.99, 'stock' => 8,
                    'specs' => ['socket' => 'LGA1700', 'tdp' => '125W']],
            ],
            'gpu' => [
                ['name' => 'NVIDIA GeForce RTX 4060', 'brand' => 'NVIDIA', 'price' => 299.99, 'stock' => 12,
                    'specs' => ['tdp' => '115W', 'gpu_length' => '200mm', 'pcie_version' => '4.0']],
                ['name' => 'NVIDIA GeForce RTX 4070', 'brand' => 'NVIDIA', 'price' => 549.99, 'stock' => 9,
                    'specs' => ['tdp' => '200W', 'gpu_length' => '244mm', 'pcie_version' => '4.0']],
                ['name' => 'AMD Radeon RX 7800 XT', 'brand' => 'AMD', 'price' => 499.99, 'stock' => 7,
                    'specs' => ['tdp' => '263W', 'gpu_length' => '267mm', 'pcie_version' => '4.0']],
                ['name' => 'NVIDIA GeForce RTX 4090', 'brand' => 'NVIDIA', 'price' => 1599.99, 'stock' => 3,
                    'specs' => ['tdp' => '450W', 'gpu_length' => '304mm', 'pcie_version' => '4.0']],
            ],
            'motherboard' => [
                ['name' => 'ASUS TUF Gaming B650-Plus', 'brand' => 'ASUS', 'price' => 179.99, 'stock' => 15,
                    'specs' => ['socket' => 'AM5', 'memory_type' => 'DDR5', 'form_factor' => 'ATX', 'pcie_version' => '4.0']],
                ['name' => 'MSI PRO B760M-A', 'brand' => 'MSI', 'price' => 129.99, 'stock' => 18,
                    'specs' => ['socket' => 'LGA1700', 'memory_type' => 'DDR4', 'form_factor' => 'mATX', 'pcie_version' => '4.0']],
                ['name' => 'Gigabyte Z790 AORUS Elite', 'brand' => 'Gigabyte', 'price' => 259.99, 'stock' => 10,
                    'specs' => ['socket' => 'LGA1700', 'memory_type' => 'DDR5', 'form_factor' => 'ATX', 'pcie_version' => '5.0']],
                ['name' => 'ASRock B650M Pro RS', 'brand' => 'ASRock', 'price' => 139.99, 'stock' => 14,
                    'specs' => ['socket' => 'AM5', 'memory_type' => 'DDR5', 'form_factor' => 'mATX', 'pcie_version' => '4.0']],
            ],
            'ram' => [
                ['name' => 'Corsair Vengeance 16GB DDR5-5600', 'brand' => 'Corsair', 'price' => 89.99, 'stock' => 30,
                    'specs' => ['ram_type' => 'DDR5', 'capacity' => '16GB']],
                ['name' => 'Corsair Vengeance 32GB DDR5-6000', 'brand' => 'Corsair', 'price' => 149.99, 'stock' => 20,
                    'specs' => ['ram_type' => 'DDR5', 'capacity' => '32GB']],
                ['name' => 'G.Skill Ripjaws 16GB DDR4-3200', 'brand' => 'G.Skill', 'price' => 54.99, 'stock' => 25,
                    'specs' => ['ram_type' => 'DDR4', 'capacity' => '16GB']],
                ['name' => 'Kingston Fury 32GB DDR4-3600', 'brand' => 'Kingston', 'price' => 109.99, 'stock' => 15,
                    'specs' => ['ram_type' => 'DDR4', 'capacity' => '32GB']],
            ],
            'storage' => [
                ['name' => 'Samsung 980 Pro 1TB NVMe SSD', 'brand' => 'Samsung', 'price' => 89.99, 'stock' => 40,
                    'specs' => ['capacity' => '1TB', 'interface' => 'NVMe']],
                ['name' => 'WD Black SN850X 2TB NVMe SSD', 'brand' => 'Western Digital', 'price' => 159.99, 'stock' => 25,
                    'specs' => ['capacity' => '2TB', 'interface' => 'NVMe']],
                ['name' => 'Crucial MX500 1TB SATA SSD', 'brand' => 'Crucial', 'price' => 59.99, 'stock' => 35,
                    'specs' => ['capacity' => '1TB', 'interface' => 'SATA']],
                ['name' => 'Seagate Barracuda 2TB HDD', 'brand' => 'Seagate', 'price' => 54.99, 'stock' => 20,
                    'specs' => ['capacity' => '2TB', 'interface' => 'SATA']],
            ],
            'psu' => [
                ['name' => 'Corsair RM750e 750W 80+ Gold', 'brand' => 'Corsair', 'price' => 99.99, 'stock' => 20,
                    'specs' => ['wattage' => '750W']],
                ['name' => 'EVGA SuperNOVA 850 GT 850W 80+ Gold', 'brand' => 'EVGA', 'price' => 129.99, 'stock' => 15,
                    'specs' => ['wattage' => '850W']],
                ['name' => 'Cooler Master MWE 650 650W 80+ Bronze', 'brand' => 'Cooler Master', 'price' => 69.99, 'stock' => 25,
                    'specs' => ['wattage' => '650W']],
                ['name' => 'be quiet! Straight Power 12 1000W', 'brand' => 'be quiet!', 'price' => 189.99, 'stock' => 8,
                    'specs' => ['wattage' => '1000W']],
            ],
            'case' => [
                ['name' => 'NZXT H510', 'brand' => 'NZXT', 'price' => 79.99, 'stock' => 20,
                    'specs' => ['supported_form_factors' => 'ATX, mATX, ITX', 'max_gpu_clearance' => '381mm', 'max_cooler_height' => '165mm']],
                ['name' => 'Corsair 4000D Airflow', 'brand' => 'Corsair', 'price' => 104.99, 'stock' => 18,
                    'specs' => ['supported_form_factors' => 'ATX, mATX, ITX', 'max_gpu_clearance' => '360mm', 'max_cooler_height' => '170mm']],
                ['name' => 'Fractal Design Meshify C', 'brand' => 'Fractal Design', 'price' => 99.99, 'stock' => 12,
                    'specs' => ['supported_form_factors' => 'ATX, mATX, ITX', 'max_gpu_clearance' => '315mm', 'max_cooler_height' => '172mm']],
                ['name' => 'Cooler Master MasterBox Q300L', 'brand' => 'Cooler Master', 'price' => 49.99, 'stock' => 15,
                    'specs' => ['supported_form_factors' => 'mATX, ITX', 'max_gpu_clearance' => '360mm', 'max_cooler_height' => '159mm']],
            ],
        ];

        foreach ($products as $categorySlug => $items) {
            if (!isset($categories[$categorySlug])) {
                continue; // category wasn't seeded — skip rather than fail the whole run
            }

            foreach ($items as $item) {
                $product = Product::updateOrCreate(
                    ['sku' => 'DPC-' . strtoupper($categorySlug) . '-' . \Illuminate\Support\Str::slug($item['name'])],
                    [
                        'category_id' => $categories[$categorySlug],
                        'name' => $item['name'],
                        'brand' => $item['brand'],
                        'price' => $item['price'],
                        'stock_quantity' => $item['stock'],
                        'description' => $item['name'] . ' — ' . ucfirst($categorySlug) . ' component.',
                    ]
                );

                foreach ($item['specs'] as $key => $value) {
                    $product->specifications()->updateOrCreate(
                        ['spec_key' => $key],
                        ['spec_value' => $value]
                    );
                }
            }
        }
    }
}