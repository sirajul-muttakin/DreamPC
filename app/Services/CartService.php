<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get or create a cart for the current authenticated user or guest session token.
     */
    public function getOrCreateCart(): Cart
    {
        $userId = auth()->id();
        $sessionId = session('cart_session_id');

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            session(['cart_session_id' => $sessionId]);
        }

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            if ($sessionId && $cart->session_id !== $sessionId) {
                $cart->update(['session_id' => $sessionId]);
            }
            return $cart;
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add single item or multiple products to the cart.
     */
    public function addProductsToCart(array $productIds, int $quantity = 1): Cart
    {
        $cart = $this->getOrCreateCart();
        $products = Product::whereIn('id', $productIds)->get();

        foreach ($products as $product) {
            $cartItem = $cart->items()->where('product_id', $product->id)->first();
            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $cartItem->quantity + $quantity,
                    'unit_price' => $product->price,
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
            }
        }

        return $cart->fresh(['items.product.category']);
    }

    /**
     * Update item quantity in cart.
     */
    public function updateQuantity(int $itemId, int $quantity): ?CartItem
    {
        $cart = $this->getOrCreateCart();
        $item = $cart->items()->find($itemId);

        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $quantity]);
        return $item->fresh('product');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $itemId): bool
    {
        $cart = $this->getOrCreateCart();
        $item = $cart->items()->find($itemId);

        if ($item) {
            return $item->delete();
        }

        return false;
    }

    /**
     * Swap a cart item with an alternative product.
     */
    public function swapItem(int $itemId, int $newProductId): ?CartItem
    {
        $cart = $this->getOrCreateCart();
        $item = $cart->items()->find($itemId);
        $newProduct = Product::find($newProductId);

        if (!$item || !$newProduct) {
            return null;
        }

        $item->update([
            'product_id' => $newProduct->id,
            'unit_price' => $newProduct->price,
        ]);

        return $item->fresh(['product.category']);
    }

    /**
     * Get compatible alternatives for a cart item's product.
     */
    public function getCompatibleAlternatives(int $productId): array
    {
        $product = Product::with('category')->find($productId);
        if (!$product) {
            return [];
        }

        return Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('stock_quantity', '>', 0)
            ->take(3)
            ->get()
            ->toArray();
    }
}
