<?php

namespace Sitakgmbh\LaraBase\Settings;

use Sitakgmbh\LaraBase\Models\Setting;

class SettingsManager
{
    public function get(string $key, $default = null)
    {
        return Setting::getValue($key, $default);
    }

    public function set(string $key, $value): void
    {
        Setting::setValue($key, $value);
    }

    public function all(): array
    {
        return Setting::all()->pluck('value', 'key')->toArray();
    }
}