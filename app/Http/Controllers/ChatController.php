<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ChatSessionManager;
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

    public function __construct(
        ChatSessionManager $sessionManager,
        GeminiApiService $geminiApi,
        RecommendationMapperService $recommendationMapper,
        HardwareRecommendationEngine $hardwareEngine
    ) {
        $this->sessionManager = $sessionManager;
        $this->geminiApi = $geminiApi;
        $this->recommendationMapper = $recommendationMapper;
        $this->hardwareEngine = $hardwareEngine;
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
        try {
            $geminiResponse = $this->geminiApi->generateHardwareRecommendation($payloadContext);

            if ($geminiResponse && !empty($geminiResponse['components'])) {
                $handled = $this->handleGeminiResponse($geminiResponse);
                if ($handled) {
                    return $handled;
                }
            }
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
            $matchedProducts = [];

            if (!empty($mapped['components'])) {
                foreach ($mapped['components'] as $component) {
                    if (!empty($component['matched_product'])) {
                        $matchedProducts[] = (object) $component['matched_product'];
                    }
                }
            }

            if (!empty($matchedProducts)) {
                $productIds = collect($matchedProducts)->pluck('id')->toArray();
                $compatibilityEngine = app(\App\Services\CompatibilityEngine::class);
                $compatCheck = $compatibilityEngine->checkCompatibility($productIds);

                $fullProducts = Product::with(['category', 'specifications'])->whereIn('id', $productIds)->get();
                $totalPrice = $fullProducts->sum('price');

                $cardHtml = view('chat.partials.build-card', [
                    'products' => $fullProducts,
                    'isCompatible' => $compatCheck['is_compatible'],
                    'incompatibilities' => $compatCheck['incompatibilities'],
                    'totalPrice' => $totalPrice,
                ])->render();

                return [
                    'text' => $text,
                    'payload' => [
                        'intent' => 'build_recommendation',
                        'card_html' => $cardHtml,
                        'suggested_products' => $fullProducts->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'category' => $p->category->name ?? 'Part',
                            'price' => '$' . number_format($p->price, 2),
                            'brand' => $p->brand,
                        ])->toArray(),
                    ],
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error in handleGeminiResponse: ' . $e->getMessage());
        }

        return null;
    }
}