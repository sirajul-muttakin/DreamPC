<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected CouponService $couponService;
    protected CartService $cartService;

    public function __construct(CouponService $couponService, CartService $cartService)
    {
        $this->couponService = $couponService;
        $this->cartService = $cartService;
    }

    /**
     * Apply a promotional coupon to the current cart.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cart->load('items');

        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);

        $result = $this->couponService->validateAndCalculate($request->code, $subtotal);

        if (!$result['valid']) {
            return redirect()->back()->with('error', $result['message']);
        }

        session([
            'applied_coupon' => [
                'code' => $result['coupon']->code,
                'discount_type' => $result['coupon']->discount_type,
                'value' => $result['coupon']->value,
                'discount_amount' => $result['discount'],
            ]
        ]);

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Remove the currently applied promotional coupon.
     */
    public function remove()
    {
        session()->forget('applied_coupon');

        return redirect()->route('cart.index')->with('success', 'Coupon code removed.');
    }
}
