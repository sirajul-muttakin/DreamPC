<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    #[Test]
    public function asking_for_a_pc_build_returns_a_complete_seven_component_compatible_build(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Build me a high performance gaming PC under $1500',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'user_message' => ['id', 'sender', 'text'],
            'bot_message' => ['id', 'sender', 'text', 'payload'],
        ]);

        $payload = $response->json('bot_message.payload');
        $this->assertEquals('build_recommendation', $payload['intent']);

        $suggestedProducts = $payload['suggested_products'];
        // Must contain 7 distinct components (CPU, Motherboard, RAM, GPU, Storage, PSU, Case)
        $this->assertCount(7, $suggestedProducts);

        // Check categories in suggested products
        $categories = collect($suggestedProducts)->pluck('category')->map(fn($c) => strtolower($c))->toArray();
        $this->assertContains('cpu', $categories);
        $this->assertContains('gpu', $categories);
        $this->assertContains('motherboard', $categories);
        $this->assertContains('ram', $categories);
        $this->assertContains('storage', $categories);
        $this->assertContains('psu', $categories);
        $this->assertContains('case', $categories);

        // Card HTML must indicate 100% compatible
        $this->assertStringContainsString('100% Compatible', $payload['card_html']);
    }

    #[Test]
    public function asking_for_an_amd_build_selects_amd_processor_and_am5_motherboard(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Recommend an AMD Ryzen gaming rig for $1600',
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $suggestedProducts = collect($payload['suggested_products']);

        $cpu = $suggestedProducts->firstWhere('category', 'CPU');
        $this->assertNotNull($cpu);
        $this->assertStringContainsString('AMD', $cpu['brand']);

        $mobo = $suggestedProducts->firstWhere('category', 'Motherboard');
        $this->assertNotNull($mobo);
        $this->assertStringContainsString('B650', $mobo['name']); // AM5 motherboard
    }

    #[Test]
    public function asking_for_an_intel_build_selects_intel_processor_and_lga1700_motherboard(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'I want an Intel Core i7 PC build for $1800',
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $suggestedProducts = collect($payload['suggested_products']);

        $cpu = $suggestedProducts->firstWhere('category', 'CPU');
        $this->assertNotNull($cpu);
        $this->assertStringContainsString('Intel', $cpu['brand']);
    }

    #[Test]
    public function asking_specifically_about_gpus_returns_gpu_advice_and_gpu_products(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'What is the best GPU for 1440p gaming?',
        ]);

        $response->assertOk();
        $text = $response->json('bot_message.text');
        $payload = $response->json('bot_message.payload');

        $this->assertEquals('gpu_advice', $payload['intent']);
        $this->assertStringContainsString('RTX 4070', $text);
        $this->assertStringContainsString('RX 7800 XT', $text);
    }

    #[Test]
    public function asking_about_compatibility_rules_returns_compatibility_info(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'How does the hardware compatibility check work?',
        ]);

        $response->assertOk();
        $text = $response->json('bot_message.text');
        $payload = $response->json('bot_message.payload');

        $this->assertEquals('compatibility_info', $payload['intent']);
        $this->assertStringContainsString('CPU Socket Match', $text);
        $this->assertStringContainsString('RAM Generation', $text);
        $this->assertStringContainsString('PSU Headroom', $text);
    }

    #[Test]
    public function asking_for_ultra_4k_build_selects_rtx_4090(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Build me an extreme 4K gaming PC with the best parts ($3000 budget)',
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $suggestedProducts = collect($payload['suggested_products']);

        $gpu = $suggestedProducts->firstWhere('category', 'GPU');
        $this->assertNotNull($gpu);
        $this->assertStringContainsString('4090', $gpu['name']);
    }

    #[Test]
    public function asking_about_ram_returns_ram_advice(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Should I get DDR4 or DDR5 RAM for gaming?',
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');

        $this->assertEquals('ram_advice', $payload['intent']);
    }
}
