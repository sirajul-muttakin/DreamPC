<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildCostCalculator;

class BuildController extends Controller
{
    protected BuildCostCalculator $calculator;

    public function __construct(BuildCostCalculator $calculator)
    {
        $this->calculator = $calculator;
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

        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getOrCreateCart();
        $isFromCart = false;

        // If no explicit product IDs provided in request, load products from active cart
        if (empty($productIds)) {
            $cart->load(['items.product.category', 'items.product.specifications']);
            $productIds = $cart->items->pluck('product_id')->toArray();
            $isFromCart = true;
        }

        if (!empty($productIds)) {
            $products = \App\Models\Product::with(['category', 'specifications'])->whereIn('id', $productIds)->get();

            // 1. Cost Breakdown & Percentages
            $costBreakdown = $this->calculator->calculateFullBreakdown($productIds);

            // 2. Compatibility & Bottleneck Checks
            $compatibilityEngine = app(\App\Services\CompatibilityEngine::class);
            $compatResult = $compatibilityEngine->checkCompatibility($productIds);
            $warningsResult = $compatibilityEngine->detectBottlenecksAndConflicts($productIds);

            // 3. System Power TDP
            $specExtractor = app(\App\Services\SpecExtractorService::class);
            $context = $specExtractor->getSystemSpecsContext($productIds);
            
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
            $recommendedPsuWattage = $totalTdp > 0 ? (int)ceil($totalTdp * 1.25) : 0;

            // Check if PSU is present in build
            $psuComponent = $context['components']['psu'] ?? null;
            $installedPsuWattage = null;
            if ($psuComponent) {
                $psuWattageStr = $psuComponent['specs']['wattage'] ?? null;
                if ($psuWattageStr) {
                    preg_match('/(\d+)/', $psuWattageStr, $psuMatches);
                    if (!empty($psuMatches[1])) {
                        $installedPsuWattage = (int)$psuMatches[1];
                    }
                }
            }
        } else {
            $products = collect();
            $costBreakdown = [
                'subtotal' => 0.0,
                'tax' => 0.0,
                'shipping' => 0.0,
                'total' => 0.0,
                'items' => [],
            ];
            $compatResult = [
                'is_compatible' => true,
                'incompatibilities' => [],
            ];
            $warningsResult = [
                'clearance_conflicts' => [],
                'bottlenecks' => [],
                'all_warnings' => [],
            ];
            $totalTdp = 0;
            $recommendedPsuWattage = 0;
            $installedPsuWattage = null;
        }

        return view('build.summary', [
            'products' => $products,
            'costBreakdown' => $costBreakdown,
            'compatResult' => $compatResult,
            'warningsResult' => $warningsResult,
            'totalTdp' => $totalTdp,
            'recommendedPsuWattage' => $recommendedPsuWattage,
            'installedPsuWattage' => $installedPsuWattage,
            'isFromCart' => $isFromCart,
            'cartItemProductIds' => $cart->items->pluck('product_id')->toArray(),
        ]);
    }
}
