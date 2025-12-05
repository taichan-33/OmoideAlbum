<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // ★ (追加) 画像ファイルの読み込みに必要
use Illuminate\Support\Facades\File;      // ★ (追加) 画像のMIMEタイプ取得に必要

class AiSummaryController extends Controller
{
    /**
     * Trix Editor のHTMLをクリーンアップする (AIに不要な情報を渡さないため)
     */
    private function cleanTrixContent(string $htmlContent): string
    {
        $cleaned = preg_replace('/<figure data-trix-attachment=".*?figure>/', '', $htmlContent);
        $text = strip_tags($cleaned);
        $text = preg_replace('/(\s\s+)/', ' ', $text);

        return trim($text);
    }

    /**
     * AI要約を生成・保存する (★ 画像認識対応)
     */
    public function generate(Trip $trip): RedirectResponse
    {
        // 1. 認可チェック: 自分の旅行か？
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ★ (追加) 関連する写真情報を読み込む
        $trip->load('photos');

        // 2. Trix Editor のHTMLをプレーンテキストに変換
        $memoText = $this->cleanTrixContent($trip->description ?? '');

        // 3. メモも写真もない場合は何もしない
        if (mb_strlen($memoText) < 50 && $trip->photos->isEmpty()) {
            return redirect()->route('trips.show', $trip)
                ->with('error', '要約するには、50文字以上のメモか、1枚以上の写真が必要です。');
        }

        // 4. APIキーを安全に取得
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            Log::error('OpenAI API Key is not configured.');
            return redirect()->route('trips.show', $trip)
                ->with('error', 'AI機能が設定されていません。');
        }

        // 5. ★ (修正) AIに渡す「マルチモーダル」のペイロードを構築
        $contentPayload = [];

        // 5a. テキストブロックを追加
        $contentPayload[] = [
            'type' => 'text',
            'text' => $memoText . "\n\n上記メモと、添付された旅行の写真（複数枚）を総合的に判断して、最高のハイライトを作成してください。"
        ];

        // 5b. 画像ブロックを追加 (最大5枚までなど、制限を設けても良い)
        foreach ($trip->photos as $photo) {
            try {
                // publicディスク（storage/app/public）から画像ファイルが存在するか確認
                if (Storage::disk('public')->exists($photo->path)) {
                    // 画像ファイルを読み込む
                    $fileContents = Storage::disk('public')->get($photo->path);
                    // Base64にエンコード
                    $base64Image = base64_encode($fileContents);
                    // 画像のMIMEタイプ（image/jpeg, image/pngなど）を取得
                    $mimeType = File::mimeType(Storage::disk('public')->path($photo->path));

                    // APIが要求する形式で画像データを追加
                    if (str_starts_with($mimeType, 'image/')) {
                        $contentPayload[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                                // 'detail' => 'low' にすると速度とコストが改善されます
                                'detail' => 'low'
                            ]
                        ];
                    }
                } else {
                    Log::warning('AI Summary: Photo file not found at path: ' . $photo->path);
                }
            } catch (\Exception $e) {
                Log::error('AI Summary: Failed to process image file: ' . $e->getMessage());
                // 1枚の画像エラーで全体を停止させない
            }
        }

        // 6. (★修正) OpenAI APIへのリクエスト
        try {
            // (★修正) プロンプトを「画像も受け取る」前提に修正
            $systemPrompt = 'あなたはプロの編集者です。ユーザーから、旅行の思い出に関する「メモ（テキスト）」と「複数の写真」が提供されます。
両方の情報を総合的に分析し、メモに書かれていない写真の雰囲気（例：楽しそうな笑顔、美しい夕焼け、豪華な食事）も汲み取ってください。
その上で、旅行の重要なポイントを抽出し、感情豊かに、「ハイライト」として彩ってください。
読み手がその旅行に行きたくなるような魅力的な文章を心がけましょう。絵文字も使いながら多彩な表現を心がけましょう！
閲覧者はこの日記の夫婦ですので、思い出が蘇るように彩った文章にして肉付けしましょう！';

            $response = Http::withToken($apiKey)
                ->timeout(350) // 画像処理は時間がかかるためタイムアウトを延長
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-5.1-2025-11-13', // (gpt-4o や gpt-4-turbo など、画像認識対応モデル)
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $contentPayload // ★ テキストと画像の配列を渡す
                        ]
                    ],
                ]);

            // 7. APIエラーハンドリング
            if ($response->failed()) {
                Log::error('OpenAI API error: ' . $response->body());
                return redirect()->route('trips.show', $trip)
                    ->with('error', 'AIとの通信に失敗しました。');
            }

            // 8. 成功：レスポンスから要約テキストを抽出
            $summary = $response->json('choices.0.message.content');
            $summary = trim($summary); // 不要な前後の空白を削除

            // 9. データベースに要約を保存
            $trip->update([
                'summary' => $summary
            ]);

            return redirect()->route('trips.show', $trip)
                ->with('success', 'AIによる要約が完了しました！');
        } catch (\Exception $e) {
            Log::error('AI Summary Exception: ' . $e->getMessage());
            return redirect()->route('trips.show', $trip)
                ->with('error', 'AI要約中に予期せぬエラーが発生しました。');
        }
    }
}
