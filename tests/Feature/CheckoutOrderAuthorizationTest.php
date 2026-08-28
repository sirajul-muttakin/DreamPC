<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutOrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_cannot_view_another_users_order_confirmation(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'customer_name' => 'Order Owner',
            'customer_phone' => '555-0100',
            'address' => '1 Owner Street',
            'total_amount' => 500,
            'discount_amount' => 0,
            'fulfillment_type' => 'delivery',
            'delivery_date' => now()->addDay()->toDateString(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($attacker)
            ->get(route('checkout.confirmation', $order->id));

        $response->assertNotFound();
    }

    #[Test]
    public function a_user_can_view_their_own_order_confirmation(): void
    {
        $owner = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'customer_name' => 'Order Owner',
            'customer_phone' => '555-0100',
            'address' => '1 Owner Street',
            'total_amount' => 500,
            'discount_amount' => 0,
            'fulfillment_type' => 'delivery',
            'delivery_date' => now()->addDay()->toDateString(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('checkout.confirmation', $order->id));

        $response->assertOk();
        $response->assertSee('ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT));
    }
}