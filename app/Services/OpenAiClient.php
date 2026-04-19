<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiClient
{
    private string $baseUrl = 'https://api.openai.com/v1/chat/completions';
    private ?string $apiKey;
    private ?string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? config('services.openai.key');
        $this->model = $model ?? config('services.openai.model');
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('AI機能が設定されていません。');
        }

        $timeout = $options['timeout'] ?? 120;
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], Arr::except($options, ['timeout']));

        $response = Http::withToken($this->apiKey)
            ->timeout($timeout)
            ->post($this->baseUrl, $payload);

        if ($response->failed()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('AIとの通信に失敗しました。');
        }

        return $response->json();
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chatContent(array $messages, array $options = []): string
    {
        return $this->chat($messages, $options)['choices'][0]['message']['content'] ?? '';
    }
}
