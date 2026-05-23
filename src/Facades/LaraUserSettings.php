<?php

namespace Sitakgmbh\LaraBase\Facades;

use Illuminate\Support\Facades\Facade;

class LaraUserSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'larausersettings';
    }
}