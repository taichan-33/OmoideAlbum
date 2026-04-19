<?php

namespace App\Actions;

use App\Models\Trip;
use App\Services\OpenAiClient;
use App\Support\TrixContentCleaner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateTripSummaryAction
{
    public function __construct(
        private readonly OpenAiClient $openAi,
    ) {
    }

    public function __invoke(Trip $trip): Trip
    {
        $trip->load('photos');

        $memoText = TrixContentCleaner::clean($trip->description);

        if (mb_strlen($memoText) < 50 && $trip->photos->isEmpty()) {
            throw new RuntimeException('要約するには、50文字以上のメモか、1枚以上の写真が必要です。');
        }

        $summary = trim($this->openAi->chatContent([
            [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ],
            [
                'role' => 'user',
                'content' => $this->buildContentPayload($trip, $memoText),
            ],
        ], [
            'timeout' => 350,
        ]));

        $trip->update([
            'summary' => $summary,
        ]);

        return $trip->fresh();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildContentPayload(Trip $trip, string $memoText): array
    {
        $contentPayload = [[
            'type' => 'text',
            'text' => $memoText . "\n\n上記メモと、添付された旅行の写真（複数枚）を総合的に判断して、最高のハイライトを作成してください。",
        ]];

        foreach ($trip->photos as $photo) {
            try {
                if (!Storage::disk('public')->exists($photo->path)) {
                    Log::warning('AI Summary: Photo file not found at path: ' . $photo->path);
                    continue;
                }

                $fileContents = Storage::disk('public')->get($photo->path);
                $mimeType = File::mimeType(Storage::disk('public')->path($photo->path));

                if (!str_starts_with((string) $mimeType, 'image/')) {
                    continue;
                }

                $contentPayload[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:' . $mimeType . ';base64,' . base64_encode($fileContents),
                        'detail' => 'low',
                    ],
                ];
            } catch (\Throwable $e) {
                Log::error('AI Summary: Failed to process image file: ' . $e->getMessage());
            }
        }

        return $contentPayload;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
あなたはプロの編集者です。ユーザーから、旅行の思い出に関する「メモ（テキスト）」と「複数の写真」が提供されます。
両方の情報を総合的に分析し、メモに書かれていない写真の雰囲気（例：楽しそうな笑顔、美しい夕焼け、豪華な食事）も汲み取ってください。
その上で、旅行の重要なポイントを抽出し、感情豊かに、「ハイライト」として彩ってください。
読み手がその旅行に行きたくなるような魅力的な文章を心がけましょう。絵文字も使いながら多彩な表現を心がけましょう！
閲覧者はこの日記の夫婦ですので、思い出が蘇るように彩った文章にして肉付けしましょう！
PROMPT;
    }
}
