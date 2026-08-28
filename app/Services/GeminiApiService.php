<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = trim((string)(config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '')));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    }

    /**
     * Check if a valid API key is configured.
     */
    public function isConfigured(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        $placeholders = ['gemini_api_key_here', 'your_api_key_here', 'placeholder', 'none', 'null', 'your_key'];
        return !in_array(strtolower($this->apiKey), $placeholders);
    }

    /**
     * Send a payload to the Gemini API and force a structured JSON response.
     * 
     * @param array $payload The conversation payload (constructed by ChatSessionManager)
     * @return array|null Parsed JSON response from Gemini, or null on failure.
     */
    public function generateHardwareRecommendation(array $payload): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Enforce JSON schema response via generationConfig
        if (!isset($payload['generationConfig'])) {
            $payload['generationConfig'] = [];
        }
        
        $payload['generationConfig']['responseMimeType'] = 'application/json';
        $payload['generationConfig']['responseSchema'] = [
            'type' => 'OBJECT',
            'properties' => [
                'intent' => [
                    'type' => 'STRING',
                    'description' => 'Type of user query: build_recommendation, component_advice, compatibility_check, or general_chat'
                ],
                'explanation' => [
                    'type' => 'STRING',
                    'description' => 'A conversational explanation answering the user with hardware advice.'
                ],
                'components' => [
                    'type' => 'ARRAY',
                    'description' => 'List of recommended hardware components from the catalog (CPU, GPU, Motherboard, RAM, Storage, PSU, Case).',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'category' => ['type' => 'STRING', 'description' => 'E.g., CPU, GPU, Motherboard, RAM, Storage, Case, PSU'],
                            'recommended_specs' => ['type' => 'STRING', 'description' => 'Product name or key specs. E.g. "AMD Ryzen 5 7600", "NVIDIA GeForce RTX 4070"'],
                            'budget_allocation' => ['type' => 'NUMBER', 'description' => 'Estimated budget allocation in USD']
                        ],
                        'required' => ['category', 'recommended_specs']
                    ]
                ]
            ],
            'required' => ['explanation']
        ];

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
                    $decoded = json_decode($jsonText, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            } else {
                Log::warning('Gemini API returned error: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Gemini API Exception: ' . $e->getMessage());
        }

        return null;
    }
}
