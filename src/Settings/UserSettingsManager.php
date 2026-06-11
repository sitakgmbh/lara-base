<?php

namespace Sitakgmbh\LaraBase\Settings;

use Illuminate\Contracts\Auth\Authenticatable;

class UserSettingsManager
{
    protected ?Authenticatable $user = null;

    public function for(Authenticatable $user): static
    {
        $clone       = clone $this;
        $clone->user = $user;
        return $clone;
    }

    protected function resolveUser(): ?Authenticatable
    {
        return $this->user ?? auth()->user();
    }

    public function get(string $key, $default = null)
    {
        $user = $this->resolveUser();
        if (!$user) return $default;
        return $user->getSetting($key, $default);
    }

    public function set(string $key, $value): void
    {
        $user = $this->resolveUser();
        if (!$user) return;
        $user->setSetting($key, $value);
    }

    public function all(): array
    {
        $user = $this->resolveUser();
        if (!$user) return [];
        return $user->settings ?? [];
    }
}