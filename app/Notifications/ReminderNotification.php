<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $app,
        public string $title,
        public string $body,
        public string $url,
        public string $tag,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, ?Notification $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon("/images/icons/{$this->app}-192.png")
            ->badge("/images/icons/{$this->app}-192.png")
            ->tag($this->tag)
            ->data(['url' => $this->url, 'app' => $this->app])
            ->options(['TTL' => 3600]);
    }
}
