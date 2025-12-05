<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AiPlannerController extends Controller
{
    public function index($prefectureCode)
    {
        $chats = DB::table('planning_chats')
            ->leftJoin('users', 'planning_chats.user_id', '=', 'users.id')
            ->where('planning_chats.prefecture_code', $prefectureCode)
            ->select(
                'planning_chats.id',
                'planning_chats.message',
                'planning_chats.is_ai',
                'planning_chats.user_id',
                'planning_chats.created_at',
                'users.name as user_name'
            )
            ->orderBy('planning_chats.created_at', 'asc')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'role' => $chat->is_ai ? 'assistant' : 'user',
                    'content' => $chat->message,
                    'user_id' => $chat->user_id,
                    'user_name' => $chat->is_ai ? 'AI Planner' : ($chat->user_name ?? 'Unknown'),
                    'is_me' => $chat->user_id === Auth::id(),
                ];
            });

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'trigger_ai' => 'boolean',
        ]);

        $userId = Auth::id();
        $message = $request->input('message');
        $triggerAi = $request->input('trigger_ai', true);
        $prefectureCode = $request->input('prefectureCode');  // Get from query or body

        if (!$prefectureCode) {
            return response()->json(['error' => 'Prefecture code is required'], 400);
        }

        // Save user message
        DB::table('planning_chats')->insert([
            'prefecture_code' => $prefectureCode,
            'user_id' => $userId,
            'message' => $message,
            'is_ai' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If not triggering AI, return early
        if (!$triggerAi) {
            return response()->json(['status' => 'success']);
        }

        // Fetch recent history for context (last 10 messages) with user names
        $history = DB::table('planning_chats')
            ->leftJoin('users', 'planning_chats.user_id', '=', 'users.id')
            ->select('planning_chats.*', 'users.name as user_name')
            ->where('planning_chats.prefecture_code', $prefectureCode)
            ->orderBy('planning_chats.created_at', 'desc')
            ->limit(30)
            ->get();

        $prefectureName = $this->getPrefectureName($prefectureCode);

        // System Prompt for "Quickn"
        $systemPrompt = [
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
                EOT
        ];

        $messages = $history->map(function ($chat) {
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

        // Add system prompt at the beginning
        array_unshift($messages, $systemPrompt);

        // Call OpenAI API
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            return response()->json(['error' => 'AI機能が設定されていません。'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-5.1-2025-11-13',
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error: ' . $response->body());
                return response()->json(['error' => 'AIとの通信に失敗しました。'], 500);
            }

            $aiMessage = $response->json()['choices'][0]['message']['content'] ?? '申し訳ありません、エラーが発生しました。';

            // Save AI response
            DB::table('planning_chats')->insert([
                'prefecture_code' => $prefectureCode,
                'user_id' => null,
                'message' => $aiMessage,
                'is_ai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('AiPlanner Error: ' . $e->getMessage());
            return response()->json(['error' => 'AIとの通信に失敗しました。'], 500);
        }
    }

    private function getPrefectureName($code)
    {
        $prefectures = [
            'JP-01' => '北海道', 'JP-02' => '青森県', 'JP-03' => '岩手県', 'JP-04' => '宮城県', 'JP-05' => '秋田県',
            'JP-06' => '山形県', 'JP-07' => '福島県', 'JP-08' => '茨城県', 'JP-09' => '栃木県', 'JP-10' => '群馬県',
            'JP-11' => '埼玉県', 'JP-12' => '千葉県', 'JP-13' => '東京都', 'JP-14' => '神奈川県', 'JP-15' => '新潟県',
            'JP-16' => '富山県', 'JP-17' => '石川県', 'JP-18' => '福井県', 'JP-19' => '山梨県', 'JP-20' => '長野県',
            'JP-21' => '岐阜県', 'JP-22' => '静岡県', 'JP-23' => '愛知県', 'JP-24' => '三重県', 'JP-25' => '滋賀県',
            'JP-26' => '京都府', 'JP-27' => '大阪府', 'JP-28' => '兵庫県', 'JP-29' => '奈良県', 'JP-30' => '和歌山県',
            'JP-31' => '鳥取県', 'JP-32' => '島根県', 'JP-33' => '岡山県', 'JP-34' => '広島県', 'JP-35' => '山口県',
            'JP-36' => '徳島県', 'JP-37' => '香川県', 'JP-38' => '愛媛県', 'JP-39' => '高知県', 'JP-40' => '福岡県',
            'JP-41' => '佐賀県', 'JP-42' => '長崎県', 'JP-43' => '熊本県', 'JP-44' => '大分県', 'JP-45' => '宮崎県',
            'JP-46' => '鹿児島県', 'JP-47' => '沖縄県'
        ];

        return $prefectures[$code] ?? '日本';
    }
}
