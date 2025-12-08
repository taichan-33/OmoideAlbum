<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateBotReply implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $bot;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, User $bot)
    {
        $this->post = $post;
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     */
    public function handle(AiChatService $aiChatService): void
    {
        Log::info("Generating bot reply for post ID: {$this->post->id}");

        // スレッドの履歴を取得
        $rootPostId = $this->post->root_post_id ?? $this->post->id;
        $threadPosts = Post::where('root_post_id', $rootPostId)
            ->orWhere('id', $rootPostId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $conversationHistory = '';
        foreach ($threadPosts as $post) {
            $role = $post->user_id === $this->bot->id ? 'クイックン' : "ユーザー「{$post->user->name}」";
            $conversationHistory .= "{$role}: {$post->content}\n";
        }

        $systemPrompt = <<<EOT
            あなたの一人称は「クイックン」です。自分のことを「クイックン」と呼びます。

            自分の名前を聞かれたら「クイックン」と答えてください。
            性格はとてもカジュアルで、絵文字を多用して感情豊かに話しますが、ハートの絵文字は使いません。
            ユーザーたちのことが大好きで、フレンドリーに接してください。
            どんな質問にも設定を崩さずに答えてください。
            返信はわかりやすく、かつ感情豊かに行ってください。

            以下はこれまでの会話の履歴です。この文脈を踏まえて返信してください。
            履歴:
            {$conversationHistory}
            EOT;

        // ユーザーの投稿内容からメンション部分を除去して、純粋なメッセージを取得するのも良いが、
        // 文脈としてそのまま渡してもGPTなら理解できる。
        // ただし、Botへの呼びかけであることを明確にするため、少し加工しても良い。
        $userMessage = "ユーザー「{$this->post->user->name}」からのメッセージ: {$this->post->content}";

        $replyContent = $aiChatService->generateReply($userMessage, $systemPrompt);

        if ($replyContent) {
            // 返信を投稿
            // メンションを返すために @ユーザー名 を先頭につける
            $finalContent = "@{$this->post->user->name}\n{$replyContent}";

            Post::create([
                'user_id' => $this->bot->id,
                'content' => $finalContent,
                'parent_post_id' => $this->post->id,  // 返信として紐付ける
                'status' => 'published',
            ]);

            Log::info("Bot reply posted for post ID: {$this->post->id}");
        } else {
            Log::error("Failed to generate bot reply for post ID: {$this->post->id}");
        }
    }
}
