<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SuggestionController extends Controller
{
    /**
     * Trix Editor のHTMLをクリーンアップする (AIに渡すため)
     */
    private function cleanTrixContent(string $htmlContent): string
    {
        $cleaned = preg_replace('/<figure data-trix-attachment=".*?figure>/', '', $htmlContent);
        $text = strip_tags($cleaned);
        $text = preg_replace('/(\s\s+)/', ' ', $text);
        return trim($text);
    }

    /**
     * AI旅行提案の一覧を表示 (★検索機能付きに改修)
     */
    public function index(Request $request): View
    {
        // ログイン中のユーザーの提案クエリを準備
        $query = Auth::user()->suggestions();

        // 1. キーワード検索 (タイトルと内容)
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            // title または content (TrixのHTML) の中を検索
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', $keyword)
                    ->orWhere('content', 'LIKE', $keyword);
            });
        }

        // 2. 並び替え (デフォルトは新しい順)
        $sort = $request->input('sort', 'created_at_desc');

        if ($sort === 'score_desc') {
            $query->orderBy('recommendation_score', 'desc');
        } elseif ($sort === 'score_asc') {
            $query->orderBy('recommendation_score', 'asc');
        } else {
            // created_at_desc (デフォルト)
            $query->orderBy('created_at', 'desc');
        }

        // 検索・ソート結果を取得 (ページネーションは後でもOK)
        $suggestions = $query->get();

        return view('suggestions.index', compact('suggestions'));
    }

    /**
     * AIに新しい旅行提案を生成させ、保存する (★プロンプトを大幅強化)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // ★ (追加) 1. 任意入力フォームのバリデーション
        $validatedOptional = $request->validate([
            'optional_destination' => 'nullable|string|max:255',
            'optional_season' => 'nullable|string|max:255',
            'optional_budget' => 'nullable|string|max:255',
            'optional_interest' => 'nullable|string|max:255',
            'optional_memo' => 'nullable|string|max:500',
        ]);

        // 2. 過去の「旅行」データを取得 (タグによる絞り込み)
        $travelTagNames = ['旅行', '宿泊'];
        $trips = $user->trips()
            ->with('tags')
            ->whereHas('tags', function ($query) use ($travelTagNames) {
                $query->whereIn('name', $travelTagNames);
            })
            ->get();

        if ($trips->isEmpty()) {
            return redirect()->route('suggestions.index')
                ->with('error', '提案を生成するための「' . implode('・', $travelTagNames) . '」タグがついた思い出がありません。');
        }

        // 3. 過去の「AI提案履歴」を取得 (重複回避のため)
        $suggestions = $user->suggestions()->get();

        // 4. AIに渡すためのプロンプト（過去データ）を整形
        $pastData = "--- ユーザーの過去の旅行履歴 (分析対象) ---\n";
        foreach ($trips as $index => $trip) {
            $pastData .= ($index + 1) . ". ";
            $pastData .= "タイトル: " . $trip->title . " | ";
            $pastData .= "場所: " . $trip->prefecture . " | ";
            $pastData .= "泊数: " . $trip->nights . "泊 | ";
            if ($trip->tags->isNotEmpty()) {
                $pastData .= "タグ: " . $trip->tags->pluck('name')->join(', ') . " | ";
            }
            if ($trip->description) {
                $pastData .= "メモ: " . $this->cleanTrixContent($trip->description) . "\n";
            } else {
                $pastData .= "メモ: (記載なし)\n";
            }
        }

        if ($suggestions->isNotEmpty()) {
            $pastData .= "\n--- AIによる過去の提案履歴 (これとは重複させないこと) ---\n";
            foreach ($suggestions as $index => $suggestion) {
                $pastData .= ($index + 1) . ". " . $suggestion->title . "\n";
            }
        }

        // ★★★ (追加) 5. AIに渡すプロンプトに「任意入力」を追加 ★★★
        $pastData .= "\n--- ユーザーからの追加リクエスト (最優先事項) ---\n";
        $hasOptionalRequest = false;
        foreach ($validatedOptional as $key => $value) {
            if (!empty($value)) {
                $pastData .= $key . ": " . $value . "\n";
                $hasOptionalRequest = true;
            }
        }
        if (!$hasOptionalRequest) {
            $pastData .= "（今回は特に追加リクエストなし）\n";
        }
        // ★★★ (追加) ここまで ★★★

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            Log::error('OpenAI API Key is not configured.');
            return redirect()->route('suggestions.index')
                ->with('error', 'AI機能が設定されていません。');
        }

        // 6. (★修正) AIへの命令（プロンプト）を「宿泊先」「名産物」を追加した最終版に変更
        $systemPrompt = "あなたは優秀な旅行プランナーです。ユーザーの過去の旅行データを分析し、彼らが次に行きたくなるような最高の旅行プランを1つ提案してください。
**最重要：** ユーザーから「追加リクエスト (最優先事項)」が提供されている場合は、その内容を**最優先**して、旅行プランを提案してください。
**重要：** 「AIによる過去の提案履歴」を読み、それらとは**絶対に重複しない**、全く新しい場所やテーマの提案を行ってください。

提案には「行くべき場所」「推奨する泊数」「具体的な観光スポット」「そのプランをお勧めする理由」に加えて、
**「おすすめの宿泊先（宿・ホテルの具体名やエリア）」**と**「その地域の代表的な名産物や料理」**も必ず含めてください。
タイトルや説明文には、**適度に絵文字 (例: ✈️, ♨️, 🍣) を使い**、魅力的で楽しい提案にしてください。
あなたの分析に基づき、この提案がどれだけユーザーの好みに合致しているかを1〜5の整数で「おすすめ度」として評価してください。

レスポンスは必ず以下のキーを持つJSONオブジェクト形式で、キー以外の文字列は一切含めないでください:
1. `title`: 提案する旅行プランのキャッチーなタイトル (文字列)。
2. `recommendation_score`: 1〜5の「おすすめ度」 (整数)。
3. `content`: 提案の詳細な説明と、なぜそれをお勧めするのかの理由 (Trix Editorで表示できるリッチなHTML形式の文字列)。
4. `accommodation`: おすすめの宿泊先（宿・ホテルの具体名やエリア） (文字列)。
5. `local_food`: その地域の代表的な名産物や料理 (例: 「カニ、但馬牛、出石そば」) (文字列)。
6. `itinerary`: 「モデル日程表」を、以下の形式のJSON配列 (Array) で作成してください。
   [
     { \"day\": \"1日目\", \"icon\": \"bi-airplane-fill\", \"title\": \"〇〇到着と市内散策\", \"details\": \"空港からホテルへ移動。チェックイン後、△△広場を散策し、地元のレストランで夕食。\" },
     { \"day\": \"2日目\", \"icon\": \"bi-camera-fill\", \"title\": \"主要観光地めぐり\", \"details\": \"午前中は〇〇博物館、午後は△△城を見学。\" },
     { \"day\": \"3日目\", \"icon\": \"bi-bag-check-fill\", \"title\": \"お土産と帰路\", \"details\": \"朝市でお土産を購入し、空港へ。\" }
   ]
";

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-5',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt // ★ 強化したプロンプトを適用
                        ],
                        [
                            'role' => 'user',
                            'content' => $pastData
                        ]
                    ],
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error (Suggestion): ' . $response->body());
                return redirect()->route('suggestions.index')
                    ->with('error', 'AIとの通信に失敗しました。');
            }

            // 8. レスポンス処理
            $jsonString = $response->json('choices.0.message.content');
            $suggestionData = json_decode($jsonString, true);

            // (★修正) 'accommodation', 'local_food' もチェック
            if (
                json_last_error() !== JSON_ERROR_NONE ||
                !isset($suggestionData['title']) || !isset($suggestionData['content']) ||
                !isset($suggestionData['recommendation_score']) || !isset($suggestionData['itinerary']) ||
                !isset($suggestionData['accommodation']) || !isset($suggestionData['local_food']) ||
                !is_array($suggestionData['itinerary'])
            ) {
                Log::error('AI response JSON decode error or missing keys. Response: ' . $jsonString);
                return redirect()->route('suggestions.index')
                    ->with('error', 'AIからの応答が不正な形式（キー不足）でした。');
            }

            // 9. (★修正) データベースに全データを保存
            $user->suggestions()->create([
                'title' => $suggestionData['title'],
                'recommendation_score' => (int)$suggestionData['recommendation_score'],
                'content' => $suggestionData['content'],
                'accommodation' => $suggestionData['accommodation'], // ★ 追加
                'local_food' => $suggestionData['local_food'],       // ★ 追加
                'itinerary_data' => $suggestionData['itinerary'],
            ]);

            return redirect()->route('suggestions.index')
                ->with('success', '新しい旅行プランが提案されました！');
        } catch (\Exception $e) {
            Log::error('AI Suggestion Exception: ' . $e->getMessage());
            return redirect()->route('suggestions.index')
                ->with('error', 'AI提案中に予期せぬエラーが発生しました。(' . $e->getMessage() . ')');
        }
    }

    /**
     * AI旅行提案を削除
     */
    public function destroy(Suggestion $suggestion): RedirectResponse
    {
        if ($suggestion->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $suggestion->delete();

        return redirect()->route('suggestions.index')
            ->with('success', '提案を削除しました。');
    }
}
