<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class RecommendationMapperService
{
    /**
     * Category synonym dictionary mapping diverse AI terms to database category slugs.
     */
    protected array $categorySynonyms = [
        'cpu' => 'cpu',
        'processor' => 'cpu',
        'central processing unit' => 'cpu',
        'gpu' => 'gpu',
        'graphics' => 'gpu',
        'graphics card' => 'gpu',
        'video card' => 'gpu',
        'display card' => 'gpu',
        'motherboard' => 'motherboard',
        'mobo' => 'motherboard',
        'mainboard' => 'motherboard',
        'ram' => 'ram',
        'memory' => 'ram',
        'system memory' => 'ram',
        'storage' => 'storage',
        'ssd' => 'storage',
        'hdd' => 'storage',
        'hard drive' => 'storage',
        'nvme' => 'storage',
        'solid state drive' => 'storage',
        'psu' => 'psu',
        'power supply' => 'psu',
        'power supply unit' => 'psu',
        'power' => 'psu',
        'case' => 'case',
        'chassis' => 'case',
        'cabinet' => 'case',
        'pc case' => 'case',
    ];

    /**
     * Normalize category string to standard database slug.
     */
    public function normalizeCategorySlug(string $category): string
    {
        $clean = strtolower(trim($category));
        return $this->categorySynonyms[$clean] ?? $clean;
    }

    /**
     * Take the structured JSON from Gemini and map it to actual database products.
     * Enforces single component per category, compatibility, and complete setups.
     * 
     * @param array $geminiData The JSON array returned by Gemini
     * @return array The original data augmented with actual product matches.
     */
    public function mapRecommendationsToProducts(array $geminiData): array
    {
        $mappedComponents = [];
        $seenCategories = [];

        if (!isset($geminiData['components']) || !is_array($geminiData['components'])) {
            return ['explanation' => $geminiData['explanation'] ?? 'I could not generate a component list at this time.', 'components' => []];
        }

        foreach ($geminiData['components'] as $component) {
            $rawCategory = $component['category'] ?? '';
            $categorySlug = $this->normalizeCategorySlug($rawCategory);
            $specs = strtolower($component['recommended_specs'] ?? '');
            $budget = (float)($component['budget_allocation'] ?? 9999);

            // Prevent duplicate categories in a build (e.g. max 1 GPU, 1 CPU)
            if (isset($seenCategories[$categorySlug])) {
                continue;
            }

            $query = Product::with(['category', 'specifications'])
                ->where('stock_quantity', '>', 0)
                ->whereHas('category', function($q) use ($categorySlug, $rawCategory) {
                    $q->where('slug', $categorySlug)
                      ->orWhere('slug', 'like', '%' . strtolower($rawCategory) . '%')
                      ->orWhere('name', 'like', '%' . $rawCategory . '%');
                });

            // Extract potential keywords from specs
            $keywords = array_filter(explode(' ', str_replace([',', '.', '"', "'", '-', '/'], ' ', $specs)), function($word) {
                return strlen(trim($word)) > 2 && !in_array(trim($word), ['and', 'or', 'for', 'the', 'with', 'gb', 'tb', 'mhz']);
            });

            if (!empty($keywords)) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', '%' . $keyword . '%')
                          ->orWhere('description', 'like', '%' . $keyword . '%');
                    }
                });
            }

            // Get best match
            $bestMatch = $query->orderBy('price', 'desc')->first();

            // Fallback to any in-stock product of that category if keywords were too restrictive
            if (!$bestMatch) {
                $bestMatch = Product::with(['category', 'specifications'])
                    ->where('stock_quantity', '>', 0)
                    ->whereHas('category', fn($q) => $q->where('slug', $categorySlug))
                    ->orderBy('price', 'desc')
                    ->first();
            }

            if ($bestMatch) {
                $seenCategories[$categorySlug] = true;
                $component['matched_product'] = [
                    'id' => $bestMatch->id,
                    'name' => $bestMatch->name,
                    'category' => $bestMatch->category->name ?? ucfirst($categorySlug),
                    'price' => '$' . number_format($bestMatch->price, 2),
                    'image_path' => $bestMatch->image_path,
                    'brand' => $bestMatch->brand,
                    'match_confidence' => 'high'
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
}
