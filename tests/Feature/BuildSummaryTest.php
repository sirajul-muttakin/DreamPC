<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BuildSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    #[Test]
    public function build_summary_shows_empty_state_when_cart_is_empty_and_no_products_passed(): void
    {
        $response = $this->get(route('build.summary'));
        $response->assertOk();
        $response->assertSee('No PC Build Selected');
    }

    #[Test]
    public function build_summary_shows_components_from_user_cart(): void
    {
        $product = Product::first();
        $this->post(route('cart.add'), ['product_ids' => [$product->id]]);

        $response = $this->get(route('build.summary'));
        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee('Configured Hardware Roster');
    }

    #[Test]
    public function build_summary_can_accept_explicit_product_ids(): void
    {
        $products = Product::take(3)->get();
        $ids = $products->pluck('id')->implode(',');

        $response = $this->get(route('build.summary', ['product_ids' => $ids]));
        $response->assertOk();
        foreach ($products as $p) {
            $response->assertSee($p->name);
        }
    }
}
