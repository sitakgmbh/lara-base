<?php

namespace Sitakgmbh\LaraBase\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LdapUserProvider implements UserProvider
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
        $user->setRememberToken($token);
        $user->saveQuietly();
    }

    public function retrieveByCredentials(array $credentials)
    {
		// SSO Test
		// $_SERVER['REMOTE_USER'] = 'SITAK\\pase';
		
		// SSO: REMOTE_USER hat Vorrang
		if (isset($_SERVER['REMOTE_USER'])) {
			$raw = $_SERVER['REMOTE_USER'];
			// Normalisieren: \\ → \
			$raw = str_replace('\\\\', '\\', $raw);
			$username = Str::of($raw)
				->after('\\')->before('@')->lower()->toString();
		} else {
            $username = trim($credentials['username'] ?? '');
        }

        if (empty($username)) return null;

        LaraLog::debug("LdapUserProvider: Suche Benutzer '{$username}'");

        // Zuerst lokal suchen
        $model = $this->getUserModel();
        $user  = $model::where('username', $username)->first();

        if ($user) {
            LaraLog::debug("LdapUserProvider: Lokaler Benutzer gefunden – ID {$user->id}");
            return $user;
        }

        // In LDAP suchen
        $ldapUser = LdapUser::query()
            ->where('samaccountname', '=', $username)
            ->first();

        if (!$ldapUser) {
            LaraLog::debug("LdapUserProvider: Kein LDAP-Benutzer gefunden für '{$username}'");
            return null;
        }

        $provisioner = app(LdapProvisioningService::class);

        if (!$provisioner->userHasAccess($ldapUser)) {
            LaraLog::debug("LdapUserProvider: Benutzer '{$username}' hat keine Berechtigung");
            return null;
        }

        $user = $provisioner->provisionOrUpdateUserFromLdap($ldapUser, $username, true);
        LaraLog::debug("LdapUserProvider: Benutzer '{$username}' provisioniert – ID {$user->id}");

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!$user->is_enabled) {
            LaraLog::debug("LdapUserProvider: Benutzer '{$user->username}' ist deaktiviert");
            return false;
        }

        // SSO: kein Passwort nötig
        if (isset($_SERVER['REMOTE_USER'])) {
            LaraLog::db('auth', 'info', "SSO-Login Benutzer '{$user->username}' erfolgreich", [
                'user_id' => $user->id,
            ]);
            return true;
        }

        // Formular: Passwort gegen LDAP prüfen
        try {
            $ldapUser = LdapUser::query()
                ->where('samaccountname', '=', $user->username)
                ->first();

            if (!$ldapUser) {
                LaraLog::debug("LdapUserProvider: LDAP-Benutzer '{$user->username}' nicht gefunden");
                return false;
            }

            $connection = Container::getDefaultConnection();
            $ok         = $connection->auth()->attempt($ldapUser->getDn(), $credentials['password'] ?? '');

            if ($ok) {
                // User-Daten aktualisieren
                app(LdapProvisioningService::class)
                    ->provisionOrUpdateUserFromLdap($ldapUser, $user->username, false, $user);

                LaraLog::db('auth', 'info', "Login Benutzer '{$user->username}' erfolgreich (LDAP)", [
                    'user_id' => $user->id,
                ]);
            } else {
                LaraLog::db('auth', 'warning', "Login Benutzer '{$user->username}' fehlgeschlagen: Falsches Passwort", [
                    'user_id' => $user->id,
                ]);
            }

            return $ok;

        } catch (\Throwable $e) {
            LaraLog::error("LdapUserProvider: Fehler bei LDAP-Auth: {$e->getMessage()}");
            return false;
        }
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Kein Rehashing bei LDAP
    }

    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }
}