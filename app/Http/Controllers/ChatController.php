<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ChatSessionManager;
use App\Services\CompatibilityEngine;
use App\Services\GeminiApiService;
use App\Services\HardwareRecommendationEngine;
use App\Services\RecommendationMapperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected ChatSessionManager $sessionManager;
    protected GeminiApiService $geminiApi;
    protected RecommendationMapperService $recommendationMapper;
    protected HardwareRecommendationEngine $hardwareEngine;
<<<<<<< Updated upstream
=======
    protected CompatibilityEngine $compatibilityEngine;
>>>>>>> Stashed changes

    public function __construct(
        ChatSessionManager $sessionManager,
        GeminiApiService $geminiApi,
        RecommendationMapperService $recommendationMapper,
<<<<<<< Updated upstream
        HardwareRecommendationEngine $hardwareEngine
=======
        HardwareRecommendationEngine $hardwareEngine,
        CompatibilityEngine $compatibilityEngine
>>>>>>> Stashed changes
    ) {
        $this->sessionManager = $sessionManager;
        $this->geminiApi = $geminiApi;
        $this->recommendationMapper = $recommendationMapper;
        $this->hardwareEngine = $hardwareEngine;
<<<<<<< Updated upstream
=======
        $this->compatibilityEngine = $compatibilityEngine;
>>>>>>> Stashed changes
    }

    public function index(Request $request)
    {
        $chatSession = $this->sessionManager->getOrCreateSession();
        $messages = $chatSession->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.index', compact('chatSession', 'messages'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessageText = trim($request->input('message'));
        $chatSession = $this->sessionManager->getOrCreateSession();

        $geminiPayload = $this->sessionManager->buildGeminiPayload(
            $chatSession,
            $userMessageText
        );

        $userMessage = $this->sessionManager->storeMessage(
            $chatSession,
            'user',
            $userMessageText
        );

        $nlpResult = $this->processWithAI($userMessageText, $geminiPayload);

        $botMessage = $this->sessionManager->storeMessage(
            $chatSession,
            'assistant',
            $nlpResult['text'],
            $nlpResult['payload'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'user_message' => [
                'id' => $userMessage->id,
                'sender' => 'user',
                'text' => $userMessage->message_text,
                'created_at' => $userMessage->created_at->format('H:i'),
            ],
            'bot_message' => [
                'id' => $botMessage->id,
                'sender' => 'assistant',
                'text' => $botMessage->message_text,
                'payload' => $botMessage->json_payload,
                'created_at' => $botMessage->created_at->format('H:i'),
            ],
        ]);
    }

    protected function processWithAI(string $input, array $payloadContext = []): array
    {
<<<<<<< Updated upstream
        try {
            $geminiResponse = $this->geminiApi->generateHardwareRecommendation($payloadContext);

            if ($geminiResponse && !empty($geminiResponse['components'])) {
                $handled = $this->handleGeminiResponse($geminiResponse);
=======
        // 1. If Gemini API is configured with a valid key, attempt cloud AI generation
        if ($this->geminiApi->isConfigured()) {
            $geminiResponse = $this->geminiApi->generateHardwareRecommendation($payloadContext);

            if ($geminiResponse) {
                $handled = $this->handleGeminiResponse($input, $geminiResponse);
>>>>>>> Stashed changes
                if ($handled) {
                    return $handled;
                }
            }
<<<<<<< Updated upstream
        } catch (\Throwable $e) {
            Log::warning('Gemini API call failed, delegating to HardwareRecommendationEngine: ' . $e->getMessage());
        }

        // Delegate to the intelligent Hardware Recommendation Engine
        return $this->hardwareEngine->processRequest($input);
    }

    protected function handleGeminiResponse(array $geminiResponse): ?array
    {
        try {
            $mapped = $this->recommendationMapper->mapRecommendationsToProducts($geminiResponse);
            $text = $mapped['explanation'] ?? 'Here is my hardware recommendation:';
=======
        }

        // 2. Local Intelligent Hardware Recommendation Engine (Fast, 100% Compatible, Full 7-part builds)
        return $this->hardwareEngine->processRequest($input);
    }

    protected function handleGeminiResponse(string $input, array $geminiResponse): ?array
    {
        try {
            $mapped = $this->recommendationMapper->mapRecommendationsToProducts($geminiResponse);
            $text = trim($mapped['explanation'] ?? ($geminiResponse['explanation'] ?? ''));

>>>>>>> Stashed changes
            $matchedProducts = [];
            if (!empty($mapped['components'])) {
                foreach ($mapped['components'] as $component) {
                    if (!empty($component['matched_product'])) {
                        $matchedProducts[] = (object) $component['matched_product'];
                    }
                }
            }

<<<<<<< Updated upstream
            if (!empty($matchedProducts)) {
=======
            // If Gemini returned a full build (4 or more parts), check and render build card
            if (count($matchedProducts) >= 4) {
>>>>>>> Stashed changes
                $productIds = collect($matchedProducts)->pluck('id')->toArray();
                $compatCheck = $this->compatibilityEngine->checkCompatibility($productIds);

<<<<<<< Updated upstream
                $fullProducts = Product::with(['category', 'specifications'])->whereIn('id', $productIds)->get();
                $totalPrice = $fullProducts->sum('price');
=======
                $totalPrice = collect($matchedProducts)->sum(function ($p) {
                    return (float) str_replace(['$', ','], '', $p->price);
                });
>>>>>>> Stashed changes

                $cardHtml = view('chat.partials.build-card', [
                    'products' => $fullProducts,
                    'isCompatible' => $compatCheck['is_compatible'],
                    'incompatibilities' => $compatCheck['incompatibilities'],
                    'totalPrice' => $totalPrice,
                ])->render();

                return [
<<<<<<< Updated upstream
                    'text' => $text,
                    'payload' => [
                        'intent' => 'build_recommendation',
                        'card_html' => $cardHtml,
                        'suggested_products' => $fullProducts->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'category' => $p->category->name ?? 'Part',
                            'price' => '$' . number_format($p->price, 2),
=======
                    'text' => $text ?: "Here is my custom build recommendation:",
                    'payload' => [
                        'intent' => 'build_recommendation',
                        'card_html' => $cardHtml,
                        'suggested_products' => collect($matchedProducts)->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'price' => $p->price,
>>>>>>> Stashed changes
                            'brand' => $p->brand,
                        ])->toArray(),
                    ],
                ];
            }
<<<<<<< Updated upstream
        } catch (\Throwable $e) {
            Log::error('Error in handleGeminiResponse: ' . $e->getMessage());
        }

        return null;
=======

            // If Gemini returned specific component suggestions (1 to 3 parts)
            if (!empty($matchedProducts)) {
                return [
                    'text' => $text ?: "Here are my component recommendations:",
                    'payload' => [
                        'intent' => 'component_advice',
                        'suggested_products' => collect($matchedProducts)->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'price' => $p->price,
                            'brand' => $p->brand,
                        ])->toArray(),
                    ],
                ];
            }

            // If Gemini returned text explanation without parts (e.g. general compatibility question)
            if (!empty($text)) {
                return [
                    'text' => $text,
                    'payload' => ['intent' => 'general_chat'],
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error handling Gemini response: ' . $e->getMessage());
            return null;
        }
>>>>>>> Stashed changes
    }
}