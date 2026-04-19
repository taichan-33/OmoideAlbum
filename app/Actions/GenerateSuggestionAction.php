<?php

namespace App\Actions;

use App\Models\Suggestion;
use App\Models\User;
use App\Services\OpenAiClient;
use App\Support\TrixContentCleaner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GenerateSuggestionAction
{
    public function __construct(
        private readonly OpenAiClient $openAi,
    ) {
    }

    /**
     * @param  array<string, mixed>  $optionalRequest
     */
    public function __invoke(User $user, array $optionalRequest): Suggestion
    {
        $travelTagNames = ['旅行', '宿泊'];

        $trips = $user
            ->trips()
            ->with('tags')
            ->whereHas('tags', function ($query) use ($travelTagNames) {
                $query->whereIn('name', $travelTagNames);
            })
            ->get();

        if ($trips->isEmpty()) {
            throw new RuntimeException(
                '提案を生成するための「' . implode('・', $travelTagNames) . '」タグがついた思い出がありません。'
            );
        }

        $jsonString = $this->openAi->chatContent([
            [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ],
            [
                'role' => 'user',
                'content' => $this->buildPastData(
                    $trips,
                    $user->suggestions()->get(),
                    $optionalRequest
                ),
            ],
        ], [
            'timeout' => 120,
            'response_format' => ['type' => 'json_object'],
        ]);

        $suggestionData = json_decode($jsonString, true);

        if (!$this->isValidSuggestionPayload($suggestionData)) {
            Log::error('AI response JSON decode error or missing keys.', [
                'response' => $jsonString,
            ]);

            throw new RuntimeException('AIからの応答が不正な形式（キー不足）でした。');
        }

        return $user->suggestions()->create([
            'title' => $suggestionData['title'],
            'recommendation_score' => (int) $suggestionData['recommendation_score'],
            'content' => $suggestionData['content'],
            'accommodation' => $suggestionData['accommodation'],
            'local_food' => $suggestionData['local_food'],
            'itinerary_data' => $suggestionData['itinerary'],
        ]);
    }

    /**
     * @param  Collection<int, mixed>  $trips
     * @param  Collection<int, Suggestion>  $suggestions
     * @param  array<string, mixed>  $optionalRequest
     */
    private function buildPastData(Collection $trips, Collection $suggestions, array $optionalRequest): string
    {
        $pastData = "--- ユーザーの過去の旅行履歴 (分析対象) ---\n";

        foreach ($trips as $index => $trip) {
            $pastData .= ($index + 1) . '. ';
            $pastData .= 'タイトル: ' . $trip->title . ' | ';

            $prefectureStr = is_array($trip->prefecture)
                ? implode(', ', $trip->prefecture)
                : $trip->prefecture;

            $pastData .= '場所: ' . $prefectureStr . ' | ';
            $pastData .= '泊数: ' . $trip->nights . '泊 | ';

            if ($trip->tags->isNotEmpty()) {
                $pastData .= 'タグ: ' . $trip->tags->pluck('name')->join(', ') . ' | ';
            }

            $pastData .= $trip->description
                ? 'メモ: ' . TrixContentCleaner::clean($trip->description) . "\n"
                : "メモ: (記載なし)\n";
        }

        if ($suggestions->isNotEmpty()) {
            $pastData .= "\n--- AIによる過去の提案履歴 (これとは重複させないこと) ---\n";

            foreach ($suggestions as $index => $suggestion) {
                $pastData .= ($index + 1) . '. ' . $suggestion->title . "\n";
            }
        }

        $pastData .= "\n--- ユーザーからの追加リクエスト (最優先事項) ---\n";
        $hasOptionalRequest = false;

        foreach ($optionalRequest as $key => $value) {
            if (!empty($value)) {
                $pastData .= $key . ': ' . $value . "\n";
                $hasOptionalRequest = true;
            }
        }

        if (!$hasOptionalRequest) {
            $pastData .= "（今回は特に追加リクエストなし）\n";
        }

        return $pastData;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
あなたは優秀な旅行プランナーです。ユーザーの過去の旅行データを分析し、彼らが次に行きたくなるような最高の旅行プランを1つ提案してください。
**最重要：** ユーザーから「追加リクエスト (最優先事項)」が提供されている場合は、その内容を**最優先**して、旅行プランを提案してください。
**重要：** 「AIによる過去の提案履歴」を読み、それらとは**絶対に重複しない**、全く新しい場所やテーマの提案を行ってください。

提案には「行くべき場所」「推奨する泊数」「具体的な観光スポット」「そのプランをお勧めする理由」に加えて、
**「おすすめの宿泊先（宿・ホテルの具体名やエリア）」**と**「その地域の代表的な名産物や料理」**も必ず含めてください。
タイトルや説明文には、**適度に絵文字 (例: ✈️, ♨️, 🍣) を使い**、魅力的で楽しい提案にしてください。
あなたの分析に基づき、この提案がどれだけユーザーの好みに合致しているかを1〜5の整数で「おすすめ度」として評価してください。

レスポンスは必ず以下のキーを持つJSONオブジェクト形式で、キー以外の文字列は一切含めないでください:
1. `title`: 提案する旅行プランのキャッチーなタイトル (文字列)。
2. `recommendation_score`: 1〜5の「おすすめ度」 (整数)。
3. `content`: 提案の詳細な説明と、なぜそれをお勧めするのかの理由。以下のJSONオブジェクト形式で出力してください:
   {
     "title": "提案のキャッチコピー（例：夫婦でゆったり温泉旅）",
     "description": "提案の導入文や全体的な説明（Markdown可）",
     "points": [
       { "title": "ポイント1のタイトル", "description": "ポイント1の詳細説明" },
       { "title": "ポイント2のタイトル", "description": "ポイント2の詳細説明" }
     ]
   }
4. `accommodation`: おすすめの宿泊先（宿・ホテルの具体名やエリア）。URLがある場合は必ず含めてください (文字列)。
5. `local_food`: その地域の代表的な名産物や料理 (例: 「カニ、但馬牛、出石そば」)。URLがある場合は必ず含めてください (文字列)。
6. `itinerary`: 「モデル日程表」を、以下の形式のJSON配列 (Array) で作成してください。
   [
     {
       "day": 1,
       "spots": [
         { "time": "10:00", "name": "〇〇空港到着", "description": "空港でレンタカーを借りて出発。", "url": "https://example.com" },
         { "time": "12:00", "name": "△△レストラン", "description": "名物の海鮮丼ランチ。", "url": "https://restaurant.example.com" }
       ]
     },
     {
       "day": 2,
       "spots": [ ... ]
     }
   ]
PROMPT;
    }

    /**
     * @param  mixed  $suggestionData
     */
    private function isValidSuggestionPayload(mixed $suggestionData): bool
    {
        return json_last_error() === JSON_ERROR_NONE
            && is_array($suggestionData)
            && isset($suggestionData['title'])
            && isset($suggestionData['content'])
            && isset($suggestionData['recommendation_score'])
            && isset($suggestionData['itinerary'])
            && isset($suggestionData['accommodation'])
            && isset($suggestionData['local_food'])
            && is_array($suggestionData['itinerary']);
    }
}
