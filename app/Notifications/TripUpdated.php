<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TripUpdated extends Notification
{
    use Queueable;

    protected $trip;
    protected $message;
    protected $url;
    protected $icon;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip, $message, $url = null, $icon = '🔔')
    {
        $this->trip = $trip;
        $this->message = $message;
        $this->url = $url ?? route('trips.show', $trip->id);
        $this->icon = $icon;
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
        return [
            'trip_id' => $this->trip->id,
            'trip_title' => $this->trip->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
            'user_name' => auth()->user()->name,  // 誰が操作したか
        ];
    }
}
