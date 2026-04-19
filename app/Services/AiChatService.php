<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AiChatService
{
    public function __construct(
        private readonly OpenAiClient $openAi,
    ) {
    }

    public function generateReply(string $userMessage, string $systemPrompt): ?string
    {
        try {
            return $this->openAi->chatContent([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ], [
                'timeout' => 300,
            ]);
        } catch (\Throwable $e) {
            Log::error('OpenAI Request Exception: ' . $e->getMessage());
            return null;
        }
    }
}
