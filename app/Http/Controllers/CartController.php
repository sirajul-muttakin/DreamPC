<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\CompatibilityEngine;
use App\Services\CouponService;
use App\Services\StockCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected CartService $cartService;
    protected CompatibilityEngine $compatibilityEngine;

    public function __construct(
        CartService $cartService,
        CompatibilityEngine $compatibilityEngine
    ) {
        $this->cartService = $cartService;
        $this->compatibilityEngine = $compatibilityEngine;
    }

    public function index()
    {
        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product.category']);

        $productIds = $cart->items->pluck('product_id')->toArray();
        $compatCheck = $this->compatibilityEngine->checkCompatibility($productIds);

        $subtotal = $cart->items->sum(
            fn ($item) => $item->quantity * $item->unit_price
        );

        $appliedCoupon = session('applied_coupon');
        $discount = 0.0;

        if ($appliedCoupon) {
            $couponCheck = app(CouponService::class)->validateAndCalculate(
                $appliedCoupon['code'],
                $subtotal
            );

            if ($couponCheck['valid']) {
                $discount = $couponCheck['discount'];
            } else {
                session()->forget('applied_coupon');
                $appliedCoupon = null;
            }
        }

        $discountedSubtotal = max(0, $subtotal - $discount);
        $tax = round($discountedSubtotal * 0.05, 2);
        $shipping = count($productIds) > 0
            ? 15.00 + (count($productIds) - 1) * 2.00
            : 0.00;
        $total = $discountedSubtotal + $tax + $shipping;

        $alternativesMap = [];

        foreach ($cart->items as $item) {
            $alternativesMap[$item->id] =
                $this->cartService->getCompatibleAlternatives($item->product_id);
        }

        return view('cart.index', compact(
            'cart',
            'compatCheck',
            'subtotal',
            'appliedCoupon',
            'discount',
            'discountedSubtotal',
            'tax',
            'shipping',
            'total',
            'alternativesMap'
        ));
    }

    public function add(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (is_numeric($productIds)) {
            $productIds = [$productIds];
        }

        if (empty($productIds) && $request->has('product_id')) {
            $productIds = [$request->input('product_id')];
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        $this->cartService->addProductsToCart($productIds, $quantity);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Added to cart successfully.',
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Build added to cart successfully!');
    }

    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $updatedItem = $this->cartService->updateQuantity(
            $itemId,
            (int) $request->quantity
        );

        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product']);

        $subtotal = $cart->items->sum(
            fn ($item) => $item->quantity * $item->unit_price
        );

        $appliedCoupon = session('applied_coupon');
        $discount = 0.0;

        if ($appliedCoupon) {
            $couponCheck = app(CouponService::class)->validateAndCalculate(
                $appliedCoupon['code'],
                $subtotal
            );

            if ($couponCheck['valid']) {
                $discount = $couponCheck['discount'];
            } else {
                session()->forget('applied_coupon');
            }
        }

        $discountedSubtotal = max(0, $subtotal - $discount);
        $tax = round($discountedSubtotal * 0.05, 2);
        $shipping = count($cart->items) > 0
            ? 15.00 + (count($cart->items) - 1) * 2.00
            : 0.00;
        $total = $discountedSubtotal + $tax + $shipping;

        return response()->json([
            'status' => 'success',
            'item_quantity' => $updatedItem ? $updatedItem->quantity : 0,
            'item_subtotal' => $updatedItem
                ? '$' . number_format(
                    $updatedItem->quantity * $updatedItem->unit_price,
                    2
                )
                : '$0.00',
            'cart_subtotal' => '$' . number_format($subtotal, 2),
            'discount' => '$' . number_format($discount, 2),
            'discounted_subtotal' => '$' . number_format($discountedSubtotal, 2),
            'cart_tax' => '$' . number_format($tax, 2),
            'cart_shipping' => '$' . number_format($shipping, 2),
            'cart_total' => '$' . number_format($total, 2),
        ]);
    }

    public function destroy(int $itemId)
    {
        $this->cartService->removeItem($itemId);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }

    public function swap(Request $request, int $itemId)
    {
        $request->validate([
            'new_product_id' => 'required|integer|exists:products,id',
        ]);

        $this->cartService->swapItem(
            $itemId,
            (int) $request->new_product_id
        );

        return redirect()
            ->route('cart.index')
            ->with('success', 'Component swapped with compatible alternative!');
    }

    public function batchAdd(
        Request $request,
        StockCheckerService $stockChecker
    ) {
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }

        $productIds = array_values(
            array_filter(array_map('intval', (array) $productIds))
        );

        if (empty($productIds)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'No products selected to add to cart.');
        }

        $productQuantities = array_count_values($productIds);

        try {
            DB::transaction(function () use ($productQuantities, $stockChecker) {
                $report = $stockChecker->verifyBatchStock($productQuantities);

                $insufficient = [];

                foreach ($report as $productId => $info) {
                    if (!$info['has_enough']) {
                        $product = Product::find($productId);
                        $productName = $product->name ?? "Product #{$productId}";

                        $insufficient[] =
                            "'{$productName}' has insufficient stock "
                            . "(Available: {$info['available']}).";
                    }
                }

                if (!empty($insufficient)) {
                    throw new \Exception(implode(' ', $insufficient));
                }

                foreach ($productQuantities as $productId => $qty) {
                    $this->cartService->addProductsToCart([$productId], $qty);
                }
            });
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to add build to cart: ' . $e->getMessage());
        }

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                'AI-recommended build batch added to cart successfully!'
            );
    }
}