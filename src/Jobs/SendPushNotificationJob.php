<?php

namespace Sitakgmbh\LaraBase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sitakgmbh\LaraBase\Notifications\CategoryPushNotification;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        protected mixed  $user,
        protected string $category,
        protected string $title,
        protected string $body,
        protected string $url = '/',
    ) {}

    public function handle(): void
    {
        // Prüfen ob Subscription noch existiert
        $hasSub = $this->user->pushSubscriptions()
            ->where('category', $this->category)
            ->exists();

        if (! $hasSub) return;

        $this->user->notify(new CategoryPushNotification(
            category: $this->category,
            title:    $this->title,
            body:     $this->body,
            url:      $this->url,
        ));
    }
}