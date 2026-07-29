<?php

namespace App\Services;

use App\Models\Product;

class BuildCostCalculator
{
    /**
     * Calculate subtotal of products by their IDs, supporting duplicate IDs.
     *
     * @param array $productIds
     * @return float
     */
    public function calculateSubtotal(array $productIds): float
    {
        if (empty($productIds)) {
            return 0.0;
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $subtotal = 0.0;

        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $subtotal += (float) $products[$id]->price;
            }
        }

        return $subtotal;
    }

    /**
     * Calculate tax for a given subtotal. Default tax rate is 5% (0.05).
     *
     * @param float $subtotal
     * @param float $rate
     * @return float
     */
    public function calculateTax(float $subtotal, float $rate = 0.05): float
    {
        return round($subtotal * $rate, 2);
    }

    /**
     * Calculate shipping cost based on the number of items.
     * Base shipping is $15.00, plus $2.00 for each additional item.
     * If no items, shipping is $0.00.
     *
     * @param array $productIds
     * @return float
     */
    public function calculateShipping(array $productIds): float
    {
        $count = count($productIds);
        if ($count === 0) {
            return 0.0;
        }

        $baseShipping = 15.00;
        $perItemAdditional = 2.00;

        return round($baseShipping + (($count - 1) * $perItemAdditional), 2);
    }

    /**
     * Compute item percentage cost contributions relative to the subtotal.
     * Supports duplicate products by calculating individual contribution.
     *
     * @param array $productIds
     * @param array $products Map of ID -> Product
     * @param float $subtotal
     * @return array Array of item breakdowns with percentage contribution
     */
    public function calculateItemBreakdown(array $productIds, array $products, float $subtotal): array
    {
        $breakdown = [];
        if ($subtotal <= 0.0) {
            foreach ($productIds as $id) {
                if (isset($products[$id])) {
                    $breakdown[] = [
                        'product_id' => $id,
                        'name' => $products[$id]->name,
                        'price' => (float) $products[$id]->price,
                        'percentage_contribution' => 0.0,
                    ];
                }
            }
            return $breakdown;
        }

        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $price = (float) $products[$id]->price;
                $percentage = round(($price / $subtotal) * 100, 2);

                $breakdown[] = [
                    'product_id' => $id,
                    'name' => $products[$id]->name,
                    'price' => $price,
                    'percentage_contribution' => $percentage,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Calculate full price breakdown for a PC build.
     *
     * @param array $productIds
     * @return array
     */
    public function calculateFullBreakdown(array $productIds): array
    {
        if (empty($productIds)) {
            return [
                'subtotal' => 0.0,
                'tax' => 0.0,
                'shipping' => 0.0,
                'total' => 0.0,
                'items' => [],
            ];
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        
        $subtotal = 0.0;
        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $subtotal += (float) $products[$id]->price;
            }
        }

        $tax = $this->calculateTax($subtotal);
        $shipping = $this->calculateShipping($productIds);
        $total = round($subtotal + $tax + $shipping, 2);

        $items = $this->calculateItemBreakdown($productIds, $products->all(), $subtotal);

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'items' => $items,
        ];
    }
}
