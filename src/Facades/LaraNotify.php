<?php

namespace Sitakgmbh\LaraBase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void send(string $category, string $title, string $body, string $url = '/')
 * @method static void sendNow(string $category, string $title, string $body, string $url = '/')
 * @method static void sendTo(\Illuminate\Database\Eloquent\Model $user, string $category, string $title, string $body, string $url = '/')
 * @method static array categoriesForUser(\Illuminate\Database\Eloquent\Model $user)
 * @method static array activeCategoriesForEndpoint(\Illuminate\Database\Eloquent\Model $user, string $endpoint)
 * @method static void cleanupExpiredSubscriptions(\Illuminate\Database\Eloquent\Model $user, array $expiredEndpoints)
 *
 * @see \Sitakgmbh\LaraBase\Push\PushManager
 */
class LaraNotify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'larapush';
    }
}