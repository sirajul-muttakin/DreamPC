<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Specification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BuildSummaryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function build_summary_shows_empty_state_when_cart_is_empty_and_no_products_passed(): void
    {
        $response = $this->get(route('build.summary'));

        $response->assertOk();
        $response->assertSee('No Components in Your Build Yet');
        $response->assertSee('Browse Hardware Catalog');
    }

    #[Test]
    public function build_summary_shows_components_from_user_cart(): void
    {
        $cpuCat = Category::factory()->create(['name' => 'Processor', 'slug' => 'cpu']);
        $gpuCat = Category::factory()->create(['name' => 'Graphics Card', 'slug' => 'gpu']);

        $cpu = Product::factory()->create([
            'category_id' => $cpuCat->id,
            'name' => 'AMD Ryzen 7 7800X3D',
            'brand' => 'AMD',
            'price' => 449.00,
        ]);
        Specification::create([
            'product_id' => $cpu->id,
            'spec_key' => 'tdp',
            'spec_value' => '120W',
        ]);
        Specification::create([
            'product_id' => $cpu->id,
            'spec_key' => 'socket',
            'spec_value' => 'AM5',
        ]);

        $gpu = Product::factory()->create([
            'category_id' => $gpuCat->id,
            'name' => 'NVIDIA RTX 4080 Super',
            'brand' => 'NVIDIA',
            'price' => 999.00,
        ]);
        Specification::create([
            'product_id' => $gpu->id,
            'spec_key' => 'tdp',
            'spec_value' => '320W',
        ]);

        // Add CPU and GPU to cart
        $this->post(route('cart.add'), [
            'product_id' => $cpu->id,
            'quantity' => 1,
        ]);
        $this->post(route('cart.add'), [
            'product_id' => $gpu->id,
            'quantity' => 1,
        ]);

        // Visit build summary without parameters
        $response = $this->get(route('build.summary'));

        $response->assertOk();
        $response->assertSeeText('AMD Ryzen 7 7800X3D', false);
        $response->assertSeeText('NVIDIA RTX 4080 Super', false);
        $response->assertSeeText('Configured Components Roster', false);
        $response->assertSeeText('440', false); // 120W + 320W TDP
        $response->assertSeeText('Price Allocation & Cost Breakdown', false);
    }

    #[Test]
    public function build_summary_can_accept_explicit_product_ids(): void
    {
        $category = Category::factory()->create(['name' => 'Processor', 'slug' => 'cpu']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Intel Core i9-14900K',
            'price' => 589.00,
        ]);

        $response = $this->get(route('build.summary', ['product_ids' => $product->id]));

        $response->assertOk();
        $response->assertSee('Intel Core i9-14900K');
        $response->assertSee('589.00');
    }

    #[Test]
    public function build_summary_evaluates_installed_psu_capacity(): void
    {
        $psuCat = Category::factory()->create(['name' => 'Power Supply', 'slug' => 'psu']);
        $cpuCat = Category::factory()->create(['name' => 'Processor', 'slug' => 'cpu']);

        $cpu = Product::factory()->create([
            'category_id' => $cpuCat->id,
            'name' => 'Intel Core i7-14700K',
            'price' => 400.00,
        ]);
        Specification::create([
            'product_id' => $cpu->id,
            'spec_key' => 'tdp',
            'spec_value' => '250W',
        ]);

        $psu = Product::factory()->create([
            'category_id' => $psuCat->id,
            'name' => 'Corsair RM850x 850W',
            'price' => 139.99,
        ]);
        Specification::create([
            'product_id' => $psu->id,
            'spec_key' => 'wattage',
            'spec_value' => '850W',
        ]);

        $this->post(route('cart.add'), ['product_id' => $cpu->id]);
        $this->post(route('cart.add'), ['product_id' => $psu->id]);

        $response = $this->get(route('build.summary'));

        $response->assertOk();
        $response->assertSeeText('850W', false);
        $response->assertSeeText('Installed PSU Capacity', false);
        $response->assertSeeText('Sufficient', false);
    }
}
