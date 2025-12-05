<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPlannerService
{
    private const MODEL = 'gpt-5.1-2025-11-13';

    /**
     * AIからの応答を生成する
     *
     * @param string $prefectureCode
     * @param \Illuminate\Support\Collection $history
     * @return string
     * @throws \Exception
     */
    public function generateResponse(string $prefectureCode, $history): string
    {
        $prefectureName = $this->getPrefectureName($prefectureCode);
        $systemPrompt = $this->buildSystemPrompt($prefectureName);
        $messages = $this->formatHistory($history);

        // Add system prompt at the beginning
        array_unshift($messages, $systemPrompt);

        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            throw new \Exception('AI機能が設定されていません。');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => self::MODEL,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI API error: ' . $response->body());
            throw new \Exception('AIとの通信に失敗しました。');
        }

        return $response->json()['choices'][0]['message']['content'] ?? '申し訳ありません、エラーが発生しました。';
    }

    private function buildSystemPrompt(string $prefectureName): array
    {
        return [
            'role' => 'system',
            'content' => <<<EOT
                あなたの一人称は「クイックン」です。自分のことを「クイックン」と呼びます。
                あなたは「{$prefectureName}」への旅行計画をサポートするロボットです。
                自分の名前を聞かれたら「クイックン」と答えてください。
                性格はとてもカジュアルで、絵文字を多用して感情豊かに話しますが、ハートの絵文字は使いません。
                ユーザーたちのことが大好きで、フレンドリーに接してください。
                どんな質問にも設定を崩さずに答えてください。
                返信はわかりやすく、かつ詳しく行ってください。
                ユーザーのメッセージは「名前: メッセージ」の形式で送られます。この名前を使ってユーザーに話しかけてください。
                回答にはMarkdown記法（太字、リストなど）を積極的に使って見やすくしてください。

                観光スポットやお店を紹介する場合は、以下のJSON形式をコードブロックで出力してください。これによりユーザーにリッチなカード形式で表示されます。
                ```json
                [
                  {
                    "name": "スポット名",
                    "description": "簡単な説明",
                    "url": "公式サイトなどのURL（あれば）"
                  }
                ]
                ```
                複数のスポットを紹介する場合も、1つのJSON配列にまとめてください。

                また、ユーザーが「プランを作って」「旅程を提案して」といった場合は、以下のJSON形式でプラン全体を出力してください。
                ```json
                {
                  "type": "plan",
                  "title": "プランのタイトル（例：〇〇満喫1泊2日の旅）",
                  "content": {
                    "title": "提案のキャッチコピー（例：夫婦でゆったり温泉旅）",
                    "description": "提案の導入文や全体的な説明（Markdown可）",
                    "points": [
                      { "title": "ポイント1のタイトル", "description": "ポイント1の詳細説明" },
                      { "title": "ポイント2のタイトル", "description": "ポイント2の詳細説明" },
                      { "title": "ポイント3のタイトル", "description": "ポイント3の詳細説明" }
                    ]
                  },
                  "accommodation": "おすすめの宿泊エリアや施設（URLがあればMarkdownリンクで）",
                  "local_food": "おすすめのグルメ（URLがあればMarkdownリンクで）",
                  "itinerary": [
                    {
                      "day": 1,
                      "spots": [
                        { "time": "10:00", "name": "スポットA", "description": "説明", "url": "URL（あれば）" },
                        { "time": "12:00", "name": "ランチ", "description": "説明", "url": "URL（あれば）" }
                      ]
                    }
                  ]
                }
                ```
                EOT
        ];
    }

    private function formatHistory($history): array
    {
        return $history->map(function ($chat) {
            if ($chat->is_ai) {
                return [
                    'role' => 'assistant',
                    'content' => $chat->message,
                ];
            } else {
                // Include user name in the message content for the AI
                $userName = $chat->user_name ?? 'ユーザー';
                return [
                    'role' => 'user',
                    'content' => "{$userName}: {$chat->message}",
                ];
            }
        })->reverse()->values()->toArray();
    }

    private function getPrefectureName($code)
    {
        return \App\Enums\Prefecture::tryFrom($code)?->label() ?? '日本';
    }
}
