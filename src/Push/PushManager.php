<?php

namespace Sitakgmbh\LaraBase\Push;

use Illuminate\Database\Eloquent\Model;
use Sitakgmbh\LaraBase\Jobs\SendPushNotificationJob;
use Sitakgmbh\LaraBase\Notifications\CategoryPushNotification;

class PushManager
{
    /**
     * Notification an alle Subscriber einer Kategorie senden.
     * Läuft via Queue wenn verfügbar.
     *
     * LaraNotify::send('incidents', 'Titel', 'Text', '/url');
     */
    public function send(string $category, string $title, string $body, string $url = '/'): void
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        $users = $userModel::whereHas('pushSubscriptions', function ($q) use ($category) {
            $q->where('category', $category);
        })->get();

        foreach ($users as $user) {
            SendPushNotificationJob::dispatch($user, $category, $title, $body, $url);
        }
    }

    /**
     * Notification synchron senden (kein Queue).
     *
     * LaraNotify::sendNow('incidents', 'Titel', 'Text', '/url');
     */
    public function sendNow(string $category, string $title, string $body, string $url = '/'): void
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        $users = $userModel::whereHas('pushSubscriptions', function ($q) use ($category) {
            $q->where('category', $category);
        })->get();

        foreach ($users as $user) {
            $user->notify(new CategoryPushNotification(
                category: $category,
                title:    $title,
                body:     $body,
                url:      $url,
            ));
        }
    }

    /**
     * Notification an einen spezifischen User senden (via Queue).
     *
     * LaraNotify::sendTo($user, 'system', 'Titel', 'Text', '/url');
     */
    public function sendTo(Model $user, string $category, string $title, string $body, string $url = '/'): void
    {
        $hasSub = $user->pushSubscriptions()->where('category', $category)->exists();
        if (! $hasSub) return;

        SendPushNotificationJob::dispatch($user, $category, $title, $body, $url);
    }

    /**
     * Gibt die für den aktuellen User sichtbaren Kategorien zurück (nach Rollen gefiltert).
     */
    public function categoriesForUser(Model $user): array
    {
        $categories = config('lara-base.pwa.push.categories', []);

        return array_values(array_filter($categories, function ($cat) use ($user) {
            $roles = $cat['roles'] ?? [];
            if (empty($roles)) return true;
            return $user->hasAnyRole($roles);
        }));
    }

    /**
     * Gibt die aktiven Kategorie-Keys eines Users für einen spezifischen Endpoint zurück.
     * Wird vom JS nach SW.getSubscription() aufgerufen.
     */
    public function activeCategoriesForEndpoint(Model $user, string $endpoint): array
    {
        return $user->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->pluck('category')
            ->toArray();
    }

    /**
     * Abgelaufene Subscriptions eines Users entfernen.
     * Wird nach fehlgeschlagenen Push-Sends aufgerufen.
     */
    public function cleanupExpiredSubscriptions(Model $user, array $expiredEndpoints): void
    {
        if (empty($expiredEndpoints)) return;

        $user->pushSubscriptions()
            ->whereIn('endpoint', $expiredEndpoints)
            ->delete();
    }
}