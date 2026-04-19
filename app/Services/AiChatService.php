<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model = config('services.openai.model');
    }

    public function generateReply(string $userMessage, string $systemPrompt): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API key is not configured.');
            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(300)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Request Exception: ' . $e->getMessage());
            return null;
        }
    }
}
