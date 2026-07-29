<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class BudgetOptimizerService
{
    /**
     * Categories where it is generally safer to downgrade to save budget 
     * without catastrophic impacts on core performance (unlike CPU/GPU).
     */
    protected array $flexibleCategories = ['storage', 'case', 'cooler', 'ram', 'psu'];

    /**
     * Optimize the build to fit the target budget by substituting non-critical components.
     *
     * @param Collection|Product[] $currentBuild Array or Collection of Product models
     * @param float $targetBudget
     * @return array
     */
    public function optimizeBuild($currentBuild, float $targetBudget): array
    {
        // Ensure we are working with a collection of models
        $build = is_array($currentBuild) ? collect($currentBuild) : clone $currentBuild;
        
        $currentTotal = $build->sum('price');
        $delta = $currentTotal - $targetBudget;

        $alternativesUsed = [];
        $totalSavings = 0;

        if ($delta <= 0) {
            return [
                'optimized_build' => $build,
                'total_savings' => 0,
                'alternatives_used' => [],
                'status' => 'within_budget',
                'new_total' => $currentTotal
            ];
        }

        // Identify flexible components and sort them by highest price first 
        // to find the largest potential savings quickly.
        $flexibleComponents = $build->filter(function ($product) {
            $catSlug = strtolower(optional($product->category)->slug ?? '');
            $catName = strtolower(optional($product->category)->name ?? '');
            
            foreach ($this->flexibleCategories as $flexCat) {
                if (str_contains($catSlug, $flexCat) || str_contains($catName, $flexCat)) {
                    return true;
                }
            }
            return false;
        })->sortByDesc('price');

        foreach ($flexibleComponents as $product) {
            if ($delta <= 0) {
                break; // Target budget achieved
            }

            // Find a cheaper alternative in the same category that is in stock.
            // Order by price descending to get the closest next-best component.
            $cheaperAlternative = Product::with('category')
                ->where('category_id', $product->category_id)
                ->where('price', '<', $product->price)
                ->where('stock_quantity', '>', 0)
                ->orderBy('price', 'desc')
                ->first();

            if ($cheaperAlternative) {
                $savings = $product->price - $cheaperAlternative->price;
                
                // Replace the component in the build collection
                $build = $build->map(function ($item) use ($product, $cheaperAlternative) {
                    if ($item->id === $product->id) {
                        return $cheaperAlternative;
                    }
                    return $item;
                });

                $alternativesUsed[] = [
                    'original' => [
                        'name' => $product->name,
                        'price' => $product->price,
                    ],
                    'replacement' => [
                        'name' => $cheaperAlternative->name,
                        'price' => $cheaperAlternative->price,
                    ],
                    'category' => optional($product->category)->name,
                    'savings' => $savings
                ];

                $delta -= $savings;
                $totalSavings += $savings;
            }
        }

        $newTotal = $build->sum('price');

        return [
            'optimized_build' => $build,
            'total_savings' => $totalSavings,
            'alternatives_used' => $alternativesUsed,
            'status' => $newTotal <= $targetBudget ? 'optimized_successfully' : 'partially_optimized',
            'new_total' => $newTotal
        ];
    }
}
