<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostInteracted extends Notification
{
    use Queueable;

    protected $actor;
    protected $post;
    protected $targetPost;
    protected $type;  // 'reply' or 'quote'

    /**
     * Create a new notification instance.
     */
    public function __construct($actor, $post, $targetPost, $type)
    {
        $this->actor = $actor;
        $this->post = $post;
        $this->targetPost = $targetPost;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = match ($this->type) {
            'reply' => "{$this->actor->name}さんがあなたの投稿に返信しました",
            'quote' => "{$this->actor->name}さんがあなたの投稿を引用しました",
            'like' => "{$this->actor->name}さんがあなたの投稿にいいねしました",
            'want_to_go' => "{$this->actor->name}さんがあなたの投稿に行きたい！しました",
            'mention' => "{$this->actor->name}さんがあなたをメンションしました",
            default => "{$this->actor->name}さんがあなたのアクションに反応しました",
        };

        $icon = match ($this->type) {
            'reply' => '💬',
            'quote' => 'Ql',
            'like' => '❤️',
            'want_to_go' => '✨',
            'mention' => '👋',
            default => '🔔',
        };

        return [
            'message' => $message,
            'url' => route('timeline.show', $this->post->id),  // Link to the new post (reply/quote)
            'icon' => $icon,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_profile_photo_url' => $this->actor->profile_photo_url,
        ];
    }
}
