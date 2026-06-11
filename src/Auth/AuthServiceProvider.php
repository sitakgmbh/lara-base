<?php

namespace Sitakgmbh\LaraBase\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider('local-auth', function ($app, array $config) {
            return new LocalUserProvider();
        });

        Auth::provider('ldap-auth', function ($app, array $config) {
            return new LdapUserProvider();
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));
        });

        $authMode = config('lara-base.auth.mode', 'local');
        config(['auth.defaults.guard' => $authMode === 'sso' ? 'sso' : 'web']);
    }
}