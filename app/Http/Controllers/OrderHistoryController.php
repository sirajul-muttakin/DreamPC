<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    /**
     * Display the authenticated user's order history.
     */
    public function index()
    {
        // This route is behind the 'auth' middleware, so auth()->id() is
        // always present here.
        $userId = auth()->id();

        $orders = Order::with(['items.product.category'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * One-Click Reordering Engine for Saved PC Builds.
     * Re-verifies stock availability and price changes before transferring items into cart session.
     */
    public function reorder(int $id, \App\Services\CartService $cartService)
    {
        $userId = auth()->id();
        $order = Order::with('items.product')->where('user_id', $userId)->findOrFail($id);

        $notifications = [];
        $addedCount = 0;

        foreach ($order->items as $item) {
            $product = $item->product;

            // 1. Check if product exists in catalog
            if (!$product) {
                $notifications[] = "A component from this past order is no longer in our catalog.";
                continue;
            }

            // 2. Check current stock availability
            if ($product->stock_quantity < $item->quantity) {
                if ($product->stock_quantity > 0) {
                    $notifications[] = "Product '{$product->name}' stock is limited. Added {$product->stock_quantity} available unit(s) instead of {$item->quantity}.";
                    $qtyToAdd = $product->stock_quantity;
                } else {
                    $notifications[] = "Product '{$product->name}' is currently out of stock and could not be reordered.";
                    continue;
                }
            } else {
                $qtyToAdd = $item->quantity;
            }

            // 3. Detect Price Changes
            if ((float)$product->price !== (float)$item->price_at_purchase) {
                $diff = (float)$product->price - (float)$item->price_at_purchase;
                $direction = $diff > 0 ? "increased" : "decreased";
                $absDiff = number_format(abs($diff), 2);
                $notifications[] = "Price for '{$product->name}' has {$direction} by \${$absDiff} (Now \$" . number_format($product->price, 2) . ").";
            }

            // 4. Add to active cart session
            $cartService->addProductsToCart([$product->id], $qtyToAdd);
            $addedCount++;
        }

        if ($addedCount === 0) {
            return redirect()->route('cart.index')->with('error', 'None of the components from this order could be reordered due to stock limits.');
        }

        $message = "Saved build reordered! {$addedCount} component(s) transferred to your cart.";
        if (!empty($notifications)) {
            $message .= " Notice: " . implode(" ", $notifications);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}