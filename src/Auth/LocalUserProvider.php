<?php

namespace Sitakgmbh\LaraBase\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LocalUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return $this->getUserModel()::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return $this->getUserModel()::where('id', $identifier)
            ->where('remember_token', $token)
            ->first();
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        try {
            $user->setRememberToken($token);
            $user->saveQuietly();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                LaraLog::debug("Duplicate remember_token für Benutzer-ID {$user->id} ignoriert");
            } else {
                throw $e;
            }
        }
    }

    public function retrieveByCredentials(array $credentials)
    {
        $mode = config('lara-base.auth.mode', 'local');

        if ($mode === 'sso') return null;

        $username = trim($credentials['username'] ?? '');
        LaraLog::debug("Suche lokalen Benutzer '{$username}'");

        $user = $this->getUserModel()::where('username', $username)
            ->where('auth_type', 'local')
            ->first();

        if ($user) {
            LaraLog::debug("Benutzer gefunden – ID: {$user->id}, username: {$user->username}");
        } else {
            LaraLog::debug("Kein lokaler Benutzer gefunden für '{$username}'");
        }

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $mode = config('lara-base.auth.mode', 'local');

        if ($mode !== 'local') return false;

        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';

        if (!$user->is_enabled) {
            $this->logDb('auth', 'warning', "Login Benutzer {$username} fehlgeschlagen: Benutzer deaktiviert", [
                'user_id' => $user->id,
            ]);
            return false;
        }

        $ok = Hash::check($password, $user->getAuthPassword());

        $this->logDb('auth', $ok ? 'info' : 'warning',
            $ok ? "Login Benutzer '{$username}' erfolgreich" : "Login Benutzer '{$username}' fehlgeschlagen: Passwort falsch",
            ['user_id' => $user->id]
        );

        return $ok;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        $mode = config('lara-base.auth.mode', 'local');

        if ($mode === 'local' && isset($credentials['password']) && Hash::needsRehash($user->getAuthPassword())) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
            LaraLog::debug("Passworthash für Benutzer-ID {$user->id} wurde erneuert");
        }
    }

    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }

    private function logDb(string $category, string $level, string $message, array $extra = []): void
    {
        try {
            LaraLog::db($category, $level, $message, array_merge([
                'ip'        => request()->ip(),
                'userAgent' => request()->userAgent(),
                'guard'     => Auth::getDefaultDriver(),
            ], $extra));
        } catch (\Throwable $e) {
            LaraLog::debug("Fehler beim Schreiben des Login-Logs: {$e->getMessage()}");
        }
    }
}