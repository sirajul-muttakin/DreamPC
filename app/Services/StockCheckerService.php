<?php

namespace App\Services;

use App\Models\Product;

class StockCheckerService
{
    /**
     * Check if a single product has enough stock.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasStock(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        return $product->stock_quantity >= $quantity;
    }

    /**
     * Verify stock levels for a batch of products and quantities.
     *
     * @param array $productQuantities Array of [product_id => quantity] or [[product_id => X, quantity => Y], ...]
     * @return array
     */
    public function verifyBatchStock(array $productQuantities): array
    {
        $normalized = [];
        foreach ($productQuantities as $key => $value) {
            if (is_array($value) && isset($value['product_id']) && isset($value['quantity'])) {
                $normalized[(int)$value['product_id']] = (int)$value['quantity'];
            } else {
                $normalized[(int)$key] = (int)$value;
            }
        }

        if (empty($normalized)) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($normalized))->get()->keyBy('id');
        $report = [];

        foreach ($normalized as $productId => $requestedQty) {
            $product = $products->get($productId);

            if (!$product) {
                $report[$productId] = [
                    'product_id' => $productId,
                    'requested' => $requestedQty,
                    'available' => 0,
                    'has_enough' => false,
                    'status' => 'out_of_stock',
                    'message' => 'Product not found in inventory.',
                ];
                continue;
            }

            $availableQty = $product->stock_quantity;
            $hasEnough = $availableQty >= $requestedQty;
            
            $status = 'in_stock';
            if ($availableQty === 0) {
                $status = 'out_of_stock';
            } elseif (!$hasEnough) {
                $status = 'low_stock';
            }

            $report[$productId] = [
                'product_id' => $productId,
                'requested' => $requestedQty,
                'available' => $availableQty,
                'has_enough' => $hasEnough,
                'status' => $status,
            ];
        }

        return $report;
    }
}
