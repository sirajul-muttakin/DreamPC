<?php

namespace App\Services;

use App\Models\Coupon;

class CouponService
{
    /**
     * Validate a coupon code against a given cart subtotal and calculate discount.
     * 
     * @param string $code
     * @param float $subtotal
     * @return array
     */
    public function validateAndCalculate(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount' => 0.0,
                'message' => "Invalid promotional coupon code '{$code}'.",
            ];
        }

        // Check Expiration Date
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => "Coupon code '{$code}' has expired on {$coupon->expires_at->format('Y-m-d')}.",
            ];
        }

        // Check Minimum Spend Requirement
        if ($subtotal < (float)$coupon->min_spend) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => "Minimum spend requirement of $" . number_format($coupon->min_spend, 2) . " not met. Current subtotal: $" . number_format($subtotal, 2) . ".",
            ];
        }

        // Calculate Discount Amount
        $discount = 0.0;
        if ($coupon->discount_type === 'percent') {
            $discount = round(($subtotal * ((float)$coupon->value / 100)), 2);
        } else {
            $discount = min($subtotal, (float)$coupon->value);
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => "Coupon '{$code}' applied successfully! Saved $" . number_format($discount, 2) . ".",
        ];
    }
}
