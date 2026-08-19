<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class RecommendationMapperService
{
    /**
     * Map category names to standard database slugs.
     */
    protected array $categorySynonyms = [
        'processor' => 'cpu',
        'processors' => 'cpu',
        'cpu' => 'cpu',
        'graphics' => 'gpu',
        'graphics card' => 'gpu',
        'video card' => 'gpu',
        'gpu' => 'gpu',
        'motherboard' => 'motherboard',
        'mainboard' => 'motherboard',
        'mobo' => 'motherboard',
        'memory' => 'ram',
        'ram' => 'ram',
        'storage' => 'storage',
        'ssd' => 'storage',
        'nvme' => 'storage',
        'hard drive' => 'storage',
        'hdd' => 'storage',
        'power supply' => 'psu',
        'power supply unit' => 'psu',
        'psu' => 'psu',
        'case' => 'case',
        'chassis' => 'case',
        'cabinet' => 'case',
        'pc case' => 'case',
    ];

    /**
     * Take the structured JSON from Gemini and map it to actual database products.
     * 
     * @param array $geminiData The JSON array returned by Gemini
     * @return array The original data augmented with actual product matches.
     */
    public function mapRecommendationsToProducts(array $geminiData): array
    {
        $mappedComponents = [];
        $usedCategorySlugs = [];
        $usedProductIds = [];

        if (!isset($geminiData['components']) || !is_array($geminiData['components'])) {
            return [
                'explanation' => $geminiData['explanation'] ?? 'Here is my hardware advice:',
                'components' => []
            ];
        }

        foreach ($geminiData['components'] as $component) {
            $rawCategory = trim($component['category'] ?? '');
            $categorySlug = $this->normalizeCategory($rawCategory);
            $specs = strtolower($component['recommended_specs'] ?? '');
            $budget = $component['budget_allocation'] ?? 9999;

            // Enforce single component per category in build list
            if (isset($usedCategorySlugs[$categorySlug])) {
                continue;
            }

            $query = Product::with(['category', 'specifications'])
                ->where('stock_quantity', '>', 0)
                ->whereNotIn('id', $usedProductIds)
                ->whereHas('category', function($q) use ($categorySlug, $rawCategory) {
                    $q->where('slug', $categorySlug)
                      ->orWhere('slug', 'like', '%' . strtolower($rawCategory) . '%')
                      ->orWhere('name', 'like', '%' . $rawCategory . '%');
                });

            // Extract potential keywords from specs
            $cleanSpecs = str_replace([',', '.', '"', "'", '-', '/', '(', ')'], ' ', $specs);
            $words = array_filter(explode(' ', $cleanSpecs), function($word) {
                $w = trim(strtolower($word));
                return strlen($w) >= 3 && !in_array($w, ['and', 'or', 'for', 'the', 'with', 'gb', 'tb', 'mhz', 'edition', 'gaming', 'series']);
            });

            // 1. Try exact or multi-keyword matching
            $matchedProduct = null;
            if (!empty($words)) {
                $keywordQuery = clone $query;
                $keywordQuery->where(function($q) use ($words) {
                    foreach ($words as $word) {
                        $q->orWhere('name', 'like', '%' . $word . '%')
                          ->orWhere('brand', 'like', '%' . $word . '%');
                    }
                });
                $matchedProduct = $keywordQuery->orderBy('price', 'desc')->first();
            }

            // 2. Fallback to category best match within budget
            if (!$matchedProduct) {
                $matchedProduct = $query->where('price', '<=', $budget * 1.25)->orderBy('price', 'desc')->first()
                               ?? $query->orderBy('price', 'asc')->first();
            }

            if ($matchedProduct) {
                $usedCategorySlugs[$categorySlug] = true;
                $usedProductIds[] = $matchedProduct->id;

                $component['matched_product'] = [
                    'id' => $matchedProduct->id,
                    'name' => $matchedProduct->name,
                    'price' => '$' . number_format($matchedProduct->price, 2),
                    'image_path' => $matchedProduct->image_path,
                    'brand' => $matchedProduct->brand,
                    'category' => $matchedProduct->category->name ?? $rawCategory,
                    'category_slug' => $categorySlug,
                ];
            } else {
                $component['matched_product'] = null;
            }

            $mappedComponents[] = $component;
        }

        return [
            'explanation' => $geminiData['explanation'] ?? '',
            'components' => $mappedComponents,
        ];
    }

    protected function normalizeCategory(string $raw): string
    {
        $cleaned = strtolower(trim($raw));
        return $this->categorySynonyms[$cleaned] ?? $cleaned;
    }
}
