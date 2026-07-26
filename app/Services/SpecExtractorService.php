<?php

namespace App\Services;

use App\Models\Product;

class SpecExtractorService
{
    /**
     * Retrieve a product and return a normalized key-value dictionary array of its specifications.
     * e.g., ['socket' => 'AM5', 'ram_type' => 'DDR5', 'wattage' => '65W']
     *
     * @param Product|int $product
     * @return array
     */
    public function getProductSpecsDictionary(Product|int $product): array
    {
        if (is_int($product)) {
            $product = Product::with('specifications')->find($product);
        } else {
            $product->loadMissing('specifications');
        }

        if (!$product) {
            return [];
        }

        $dictionary = [];
        foreach ($product->specifications as $spec) {
            $dictionary[strtolower(trim($spec->spec_key))] = trim($spec->spec_value);
        }

        return $dictionary;
    }

    /**
     * Compile an entire build's technical specifications into a single structured array context.
     *
     * @param array $productIds List of product IDs in the PC build
     * @return array
     */
    public function getSystemSpecsContext(array $productIds): array
    {
        if (empty($productIds)) {
            return [
                'components' => [],
                'all_specs' => [],
            ];
        }

        $products = Product::with(['category', 'specifications'])
            ->whereIn('id', $productIds)
            ->get();

        $components = [];
        $allSpecs = [];

        foreach ($products as $product) {
            $categorySlug = $product->category->slug ?? 'other';
            $specsDict = $this->getProductSpecsDictionary($product);

            $components[$categorySlug] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'price' => $product->price,
                'specs' => $specsDict,
            ];

            foreach ($specsDict as $key => $val) {
                $allSpecs[$key] = $val;
                $allSpecs["{$categorySlug}_{$key}"] = $val;
            }
        }

        return [
            'components' => $components,
            'all_specs' => $allSpecs,
        ];
    }
}
