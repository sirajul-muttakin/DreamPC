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
            'message' => 'Can you build me a gaming PC for $1500?'
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'user_message' => ['id', 'sender', 'text'],
            'bot_message' => ['id', 'sender', 'text', 'payload']
        ]);

        $payload = $response->json('bot_message.payload');
        $this->assertEquals('build_recommendation', $payload['intent']);
        $this->assertArrayHasKey('suggested_products', $payload);
        
        // Assert 7 distinct components (CPU, GPU, Motherboard, RAM, Storage, PSU, Case)
        $this->assertCount(7, $payload['suggested_products']);
        $this->assertStringContainsString('100% Compatible', $payload['card_html']);
    }

    #[Test]
    public function asking_for_an_amd_build_selects_amd_processor_and_am5_motherboard(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'I want an AMD Ryzen build for around $1200'
        ]);

        $response->assertOk();
        $products = collect($response->json('bot_message.payload.suggested_products'));
        
        $cpu = $products->firstWhere('category', 'CPU');
        $this->assertNotNull($cpu);
        $this->assertStringContainsString('AMD', $cpu['brand']);

        $mobo = $products->firstWhere('category', 'Motherboard');
        $this->assertNotNull($mobo);
        $this->assertStringContainsString('B650', $mobo['name']);
    }

    #[Test]
    public function asking_for_an_intel_build_selects_intel_processor_and_lga1700_motherboard(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Give me an Intel Core i5 build'
        ]);

        $response->assertOk();
        $products = collect($response->json('bot_message.payload.suggested_products'));
        
        $cpu = $products->firstWhere('category', 'CPU');
        $this->assertNotNull($cpu);
        $this->assertStringContainsString('Intel', $cpu['brand']);

        $mobo = $products->firstWhere('category', 'Motherboard');
        $this->assertNotNull($mobo);
        $this->assertStringContainsString('B760', $mobo['name']);
    }

    #[Test]
    public function asking_specifically_about_gpus_returns_gpu_advice_and_gpu_products(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'What is the best GPU for 1440p gaming?'
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $this->assertEquals('gpu_advice', $payload['intent']);
        $this->assertStringContainsString('RTX 4070', $response->json('bot_message.text'));
    }

    #[Test]
    public function asking_about_compatibility_rules_returns_compatibility_info(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'How does the hardware compatibility check work?'
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $this->assertEquals('compatibility_info', $payload['intent']);
        $this->assertStringContainsString('CPU Socket Match', $response->json('bot_message.text'));
    }

    #[Test]
    public function asking_for_ultra_4k_build_selects_rtx_4090(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Build me the ultimate extreme 4K gaming PC with no budget limits'
        ]);

        $response->assertOk();
        $products = collect($response->json('bot_message.payload.suggested_products'));
        $gpu = $products->firstWhere('category', 'GPU');
        $this->assertNotNull($gpu);
        $this->assertStringContainsString('RTX 4090', $gpu['name']);
    }

    #[Test]
    public function asking_about_ram_returns_ram_advice(): void
    {
        $response = $this->postJson(route('chat.send'), [
            'message' => 'Is 16GB RAM enough or should I get DDR5 32GB?'
        ]);

        $response->assertOk();
        $payload = $response->json('bot_message.payload');
        $this->assertEquals('ram_advice', $payload['intent']);
        $this->assertStringContainsString('DDR4 vs DDR5', $response->json('bot_message.text'));
    }
}
