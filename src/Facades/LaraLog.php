<?php

namespace Sitakgmbh\LaraBase\Facades;

use Illuminate\Support\Facades\Facade;

class LaraLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laralog';
    }
}