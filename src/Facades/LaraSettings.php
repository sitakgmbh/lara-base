<?php

namespace Sitakgmbh\LaraBase\Facades;

use Illuminate\Support\Facades\Facade;

class LaraSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'larasettings';
    }
}