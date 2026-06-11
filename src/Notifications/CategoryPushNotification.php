<?php

namespace Sitakgmbh\LaraBase\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CategoryPushNotification extends Notification
{
    public function __construct(
        protected string $category,
        protected string $title,
        protected string $body,
        protected string $url = '/',
        protected string $icon = '/icons-pwa/icon-192x192.png',
        protected string $badge = '/icons-pwa/icon-192x192.png',
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Nur Subscriptions der passenden Kategorie verwenden.
     * Der WebPushChannel iteriert über diese Collection.
     */
    public function routeNotificationForWebPush(object $notifiable): mixed
    {
        return $notifiable->pushSubscriptions()
            ->where('category', $this->category)
            ->get();
    }

    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon)
            ->badge($this->badge)
            ->tag($this->category)
            ->data(['url' => $this->url]);
    }
}