<?php

namespace Sitakgmbh\LaraBase\Console\Test;

use Illuminate\Console\Command;
use Sitakgmbh\LaraBase\Facades\LaraNotify;

class TestPushNotification extends Command
{
    protected $signature = 'pwa:test-push
                            {--category= : Kategorie-Key (leer = erste verfügbare)}
                            {--user= : User-ID (leer = alle Subscriber der Kategorie)}
                            {--title=Test-Benachrichtigung : Titel}
                            {--body=Dies ist eine Test-Push-Notification von lara-base. : Text}
                            {--url=/ : Ziel-URL beim Klick}
                            {--sync : Synchron senden (kein Queue)}';

    protected $description = 'Sendet eine Test-Push-Notification';

    public function handle(): int
    {
        if (! config('lara-base.pwa.push.enabled', false)) {
            $this->error('PWA Push ist deaktiviert. Setze PWA_PUSH_ENABLED=true in .env');
            return self::FAILURE;
        }

        if (! config('lara-base.pwa.push.vapid.public_key')) {
            $this->error('VAPID Keys fehlen. Führe zuerst "php artisan webpush:vapid" aus.');
            return self::FAILURE;
        }

        $categories   = config('lara-base.pwa.push.categories', []);
        $categoryKeys = array_column($categories, 'key');

        if (empty($categoryKeys)) {
            $this->error('Keine Kategorien in lara-base.pwa.push.categories definiert.');
            return self::FAILURE;
        }

        $category = $this->option('category') ?? $categoryKeys[0];

        if (! in_array($category, $categoryKeys)) {
            $this->error("Ungültige Kategorie '{$category}'. Verfügbar: " . implode(', ', $categoryKeys));
            return self::FAILURE;
        }

        $title = $this->option('title');
        $body  = $this->option('body');
        $url   = $this->option('url');
        $sync  = $this->option('sync');

        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        if ($userId = $this->option('user')) {
            $user = $userModel::find($userId);
            if (! $user) {
                $this->error("User mit ID {$userId} nicht gefunden.");
                return self::FAILURE;
            }

            $this->info("Sende [{$category}] an User [{$user->id}] {$user->email}...");
            LaraNotify::sendTo($user, $category, $title, $body, $url);
            $this->info($sync ? 'Fertig (sync).' : 'Job dispatched.');
            return self::SUCCESS;
        }

        $users = $userModel::whereHas('pushSubscriptions', fn($q) => $q->where('category', $category))->get();

        if ($users->isEmpty()) {
            $this->warn("Keine Subscriptions für Kategorie '{$category}' gefunden.");
            return self::SUCCESS;
        }

        $this->info("Sende [{$category}] an {$users->count()} User...");
        $this->newLine();

        foreach ($users as $user) {
            $count = $user->pushSubscriptions()->where('category', $category)->count();
            $this->info("  [{$user->id}] {$user->email} — {$count} Subscription(s)");
        }

        if ($sync) {
            LaraNotify::sendNow($category, $title, $body, $url);
            $this->newLine();
            $this->info('Fertig (sync).');
        } else {
            LaraNotify::send($category, $title, $body, $url);
            $this->newLine();
            $this->info('Jobs dispatched. Queue verarbeiten mit: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}