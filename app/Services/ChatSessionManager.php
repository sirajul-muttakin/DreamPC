<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Product;
use Illuminate\Support\Str;

class ChatSessionManager
{
    /**
     * Retrieve an existing chat session or create a new one using the session token.
     *
     * @param string|null $sessionToken
     * @param int|null $userId
     * @return ChatSession
     */
    public function getOrCreateSession(?string $sessionToken = null, ?int $userId = null): ChatSession
    {
        if (!$sessionToken) {
            $sessionToken = session('chat_session_token');
            if (!$sessionToken) {
                $sessionToken = Str::uuid()->toString();
                session(['chat_session_token' => $sessionToken]);
            }
        }

        return ChatSession::firstOrCreate(
            ['session_token' => $sessionToken],
            ['user_id' => $userId ?? auth()->id(), 'title' => 'Hardware Assistant Chat']
        );
    }

    /**
     * Store a message in the specified chat session.
     *
     * @param ChatSession $session
     * @param string $sender 'user' or 'assistant'
     * @param string $text
     * @param array|null $payload
     * @return ChatMessage
     */
    public function storeMessage(ChatSession $session, string $sender, string $text, ?array $payload = null): ChatMessage
    {
        return $session->messages()->create([
            'sender' => $sender,
            'message_text' => $text,
            'json_payload' => $payload,
        ]);
    }

    /**
     * Construct the conversation payload for the Gemini API.
     * Retrieves up to the last 10 messages to maintain context for follow-up requests.
     *
     * @param ChatSession $session
     * @param string|null $newMessageText Optionally append a new user message before fetching from DB
     * @return array The payload structure required by Gemini API
     */
    public function buildGeminiPayload(ChatSession $session, ?string $newMessageText = null): array
    {
        // Retrieve the last 10 messages from the database
        $messages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $contents = [];

        foreach ($messages as $msg) {
            // Map our sender 'assistant' to Gemini's 'model' role
            $role = ($msg->sender === 'assistant' || $msg->sender === 'bot') ? 'model' : 'user';
            
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg->message_text]
                ]
            ];
        }

        // If a new message is provided but not yet stored, append it to the payload
        if ($newMessageText) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $newMessageText]
                ]
            ];
        }

        // Catalog context for accurate recommendations
        $catalogSummary = $this->getCatalogSummary();

        $systemInstruction = "You are the expert AI Hardware Assistant for DreamPC. " .
            "Your goal is to help users select PC components and assemble 100% compatible custom PC builds.\n\n" .
            "Available Products in DreamPC Store Catalog:\n" . $catalogSummary . "\n\n" .
            "Rules for Recommendations:\n" .
            "1. When recommending a full PC build, ALWAYS recommend exactly one component for each of the 7 essential categories: CPU, Motherboard, RAM, GPU, Storage, PSU, Case.\n" .
            "2. Never recommend duplicate categories (e.g. NEVER give 2 GPUs or 2 CPUs for a standard build).\n" .
            "3. Ensure CPU socket matches Motherboard socket (e.g. AMD Ryzen 7000 requires AM5; Intel 13th/14th Gen requires LGA1700).\n" .
            "4. Ensure RAM type matches Motherboard (AM5 requires DDR5).\n" .
            "5. Ensure PSU wattage is >= (Total TDP * 1.25).\n" .
            "6. Always prefer selecting exact product names from the DreamPC Store Catalog above.\n" .
            "7. Provide clear, enthusiastic explanations.";

        return [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1000,
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ]
        ];
    }

    protected function getCatalogSummary(): string
    {
        try {
            $products = Product::with(['category', 'specifications'])->where('stock_quantity', '>', 0)->get();
            $lines = [];
            foreach ($products as $p) {
                $specs = [];
                foreach ($p->specifications as $s) {
                    $specs[] = $s->spec_key . ': ' . $s->spec_value;
                }
                $specStr = !empty($specs) ? ' (' . implode(', ', $specs) . ')' : '';
                $lines[] = "- [" . ($p->category->name ?? 'Hardware') . "] " . $p->name . " ($" . number_format($p->price, 2) . ")" . $specStr;
            }
            return implode("\n", $lines);
        } catch (\Exception $e) {
            return "- AMD Ryzen 5 7600 ($229.99)\n- Intel Core i5-13400F ($199.99)\n- NVIDIA GeForce RTX 4070 ($549.99)\n- NVIDIA GeForce RTX 4060 ($299.99)";
        }
    }
}
