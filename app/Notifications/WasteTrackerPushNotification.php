<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use NotificationChannels\FCM\FCMChannel;

class WasteTrackerPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public String $title;
    public String  $body;
    public ?String $image;
    public ?array $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $body, $data = null, $image = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->image = $image;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FCMChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return CloudMessage
     */
    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'android_channel_id' => 'high_importance_channel',
                'title' => $this->title,
                'body' => $this->body,
                'sound' => 'default',
                'image' => $this->image
            ])
            ->withData($this->data);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
