<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BadgeEarned extends Notification
{
    use Queueable;

    protected $badge;

    /**
     * Create a new notification instance.
     */
    public function __construct($badge)
    {
        $this->badge = $badge;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        $prefs = $notifiable->notification_preferences ?? [];

        // Default to true if not set
        if (!isset($prefs['badge_earned']) || $prefs['badge_earned']) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the WebPush representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🏆 新しい称号を獲得！')
            ->body("「{$this->badge->name}」を獲得しました！\n{$this->badge->description}")
            ->icon('/icons/icon-192x192.png')
            ->action('確認する', 'view_badge')
            ->data(['url' => route('profile.edit')]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_icon' => $this->badge->icon_path,
            'message' => "新しい称号「{$this->badge->name}」を獲得しました！",
            'url' => route('profile.edit'),
            'icon' => '🏆',
        ];
    }
}
