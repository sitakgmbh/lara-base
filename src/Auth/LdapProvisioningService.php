<?php

namespace Sitakgmbh\LaraBase\Auth;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LdapProvisioningService
{
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', \App\Models\User::class);
    }

    public function provisionOrUpdateUserFromLdap(LdapUser $ldapUser, string $username, bool $create = false, $existingUser = null)
    {
        $sid    = $ldapUser->getConvertedSid();
        $groups = $this->getGroups($ldapUser);

        $adminGroup = config('lara-base.ldap.groups.admin');
        $userGroup  = config('lara-base.ldap.groups.user');

        $newRole = null;
        if ($adminGroup && in_array($adminGroup, $groups, true)) {
            $newRole = 'admin';
        } elseif ($userGroup && in_array($userGroup, $groups, true)) {
            $newRole = 'user';
        }

        $model = $this->getUserModel();
        $user  = $existingUser ?? new $model();

        $user->fill([
            'username'   => $username,
            'firstname'  => $ldapUser->getFirstAttribute('givenname') ?? null,
            'lastname'   => $ldapUser->getFirstAttribute('sn') ?? null,
            'email'      => $ldapUser->getFirstAttribute('mail') ?? null,
            'auth_type'  => 'ldap',
            'ad_sid'     => $sid,
            'is_enabled' => true,
        ]);

        $user->password = null;
        $user->save();

        if ($newRole) {
            try {
                $user->syncRoles([$newRole]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Concurrent insert – Rolle wurde bereits gesetzt
            }
        } else {
            $user->syncRoles([]);
            LaraLog::warning("Benutzer {$username} hat keine gültige AD-Gruppe, Rollen entfernt");
        }

        return $user;
    }

    public function userHasAccess(LdapUser $ldapUser): bool
    {
        $groups     = $this->getGroups($ldapUser);
        $adminGroup = config('lara-base.ldap.groups.admin');
        $userGroup  = config('lara-base.ldap.groups.user');

        return in_array($adminGroup, $groups, true) || in_array($userGroup, $groups, true);
    }

    protected function getGroups(LdapUser $ldapUser): array
    {
        return collect($ldapUser->getAttribute('memberOf') ?? [])
            ->map(fn($dn) => preg_match('/CN=([^,]+)/i', $dn, $m) ? $m[1] : null)
            ->filter()
            ->values()
            ->toArray();
    }
}