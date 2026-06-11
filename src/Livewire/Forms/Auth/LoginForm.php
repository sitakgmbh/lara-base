<?php

namespace Sitakgmbh\LaraBase\Livewire\Forms\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        LaraLog::debug("LoginForm: Versuche Login mit Benutzer '{$this->username}' über Guard 'web'");

        $credentials = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $provider = Auth::guard('web')->getProvider();
        $user     = $provider?->retrieveByCredentials($credentials);

        if (!$user) {
            LaraLog::debug("LoginForm: Benutzer '{$this->username}' nicht gefunden");
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'form.username' => trans('auth.failed'),
            ]);
        }

        if (!$provider->validateCredentials($user, $credentials)) {
            LaraLog::debug("LoginForm: Passwortprüfung fehlgeschlagen für '{$this->username}'");
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'form.username' => trans('auth.failed'),
            ]);
        }

        LaraLog::debug("LoginForm: Login erfolgreich für '{$this->username}' – Benutzer-ID: {$user->id}");

        Auth::guard('web')->login($user, $this->remember);
        RateLimiter::clear($this->throttleKey());
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->username) . '|' . request()->ip();
    }
}