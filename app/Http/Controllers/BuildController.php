<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildCostCalculator;
use App\Services\CartService;
use App\Services\CompatibilityEngine;
use App\Services\SpecExtractorService;

class BuildController extends Controller
{
    protected BuildCostCalculator $calculator;
    protected CartService $cartService;
    protected CompatibilityEngine $compatibilityEngine;
    protected SpecExtractorService $specExtractor;

    public function __construct(
        BuildCostCalculator $calculator,
        CartService $cartService,
        CompatibilityEngine $compatibilityEngine,
        SpecExtractorService $specExtractor
    ) {
        $this->calculator = $calculator;
        $this->cartService = $cartService;
        $this->compatibilityEngine = $compatibilityEngine;
        $this->specExtractor = $specExtractor;
    }

    /**
     * Handle the real-time build cost calculation endpoint.
     * Accepts POST or GET requests with a 'product_ids' array or comma-separated string.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateCost(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }

        if (!is_array($productIds)) {
            $productIds = [];
        }

        $productIds = array_values(array_filter(array_map('intval', $productIds)));

        $breakdown = $this->calculator->calculateFullBreakdown($productIds);

        return response()->json($breakdown);
    }

    /**
     * Display the dynamic visual PC build summary generator page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showSummary(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }
        $productIds = array_values(array_filter(array_map('intval', (array)$productIds)));

        $products = collect();

        // 1. If explicit product_ids provided
        if (!empty($productIds)) {
            $products = \App\Models\Product::with(['category', 'specifications'])->whereIn('id', $productIds)->get();
        } else {
            // 2. Otherwise load from active Cart
            $cart = $this->cartService->getOrCreateCart();
            $cart->loadMissing('items.product.category', 'items.product.specifications');
            $cartProducts = $cart->items->pluck('product')->filter()->values();
            if ($cartProducts->isNotEmpty()) {
                $products = $cartProducts;
                $productIds = $products->pluck('id')->toArray();
            }
        }

        // 1. Cost Breakdown & Percentages
        $costBreakdown = $this->calculator->calculateFullBreakdown($productIds);

        // 2. Compatibility & Bottleneck Checks
        $compatResult = $this->compatibilityEngine->checkCompatibility($productIds);
        $warningsResult = $this->compatibilityEngine->detectBottlenecksAndConflicts($productIds);

        // 3. System Power TDP
        $context = $this->specExtractor->getSystemSpecsContext($productIds);
        
        $totalTdp = 0;
        foreach ($context['components'] as $component) {
            $tdpStr = $component['specs']['tdp'] ?? null;
            if ($tdpStr) {
                preg_match('/(\d+)/', $tdpStr, $matches);
                if (!empty($matches[1])) {
                    $totalTdp += (int)$matches[1];
                }
            }
        }
        
        $recommendedPsuWattage = (int)ceil($totalTdp * 1.25);
        if ($totalTdp === 0) {
            $recommendedPsuWattage = 0;
        }

        return view('build.summary', [
            'products' => $products,
            'costBreakdown' => $costBreakdown,
            'compatResult' => $compatResult,
            'warningsResult' => $warningsResult,
            'totalTdp' => $totalTdp,
            'recommendedPsuWattage' => $recommendedPsuWattage,
        ]);
    }
}
