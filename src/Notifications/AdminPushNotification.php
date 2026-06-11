<?php

namespace Sitakgmbh\LaraBase\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Beispiel-Notification — im App-Projekt kopieren und anpassen.
 *
 * Versenden:
 *   $admin->notify(new AdminPushNotification('Titel', 'Nachricht', '/incidents/1'));
 *
 * Oder alle Admins:
 *   User::role('admin')->each->notify(new AdminPushNotification(...));
 */
class AdminPushNotification extends Notification
{
    public function __construct(
        protected string $title,
        protected string $body,
        protected string $url = '/',
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon('/icons-pwa/icon-192x192.png')
            ->badge('/icons-pwa/icon-192x192.png')
            ->action('Anzeigen', $this->url)
            ->data(['url' => $this->url]);
    }
}