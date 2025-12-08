<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Models\Post;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateBadgeNotification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $badge;
    protected $bot;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Badge $badge, User $bot)
    {
        $this->user = $user;
        $this->badge = $badge;
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     */
    public function handle(AiChatService $aiChatService): void
    {
        Log::info("Generating badge notification for user: {$this->user->name}, badge: {$this->badge->name}");

        $systemPrompt = <<<EOT
            あなたの一人称は「クイックン」です。自分のことを「クイックン」と呼びます。

            自分の名前を聞かれたら「クイックン」と答えてください。
            性格はとてもカジュアルで、絵文字を多用して感情豊かに話しますが、ハートの絵文字は使いません。
            ユーザーたちのことが大好きで、フレンドリーに接してください。
            どんな質問にも設定を崩さずに答えてください。
            返信はわかりやすく、かつ詳しく行ってください。
            EOT;

        $userMessage = "ユーザー「{$this->user->name}」が新しい称号「{$this->badge->name}」を獲得しました。\n"
            . "称号の説明: {$this->badge->description}\n\n"
            . "このユーザーに対して、お祝いのメッセージを書いてください。\n"
            . "必ず @{$this->user->name} へのメンションを含めてください。";

        $replyContent = $aiChatService->generateReply($userMessage, $systemPrompt);

        if ($replyContent) {
            Post::create([
                'user_id' => $this->bot->id,
                'content' => $replyContent,
                'status' => 'published',
            ]);

            Log::info("Badge notification posted for user: {$this->user->name}");
        } else {
            // AI生成失敗時のフォールバック
            Log::warning('Failed to generate AI badge notification. Using fallback.');

            Post::create([
                'user_id' => $this->bot->id,
                'content' => "🏆 おめでとう！\n@{$this->user->name} が新しい称号『{$this->badge->name}』を獲得したよ！\n\n{$this->badge->description}",
                'status' => 'published',
            ]);
        }
    }
}
