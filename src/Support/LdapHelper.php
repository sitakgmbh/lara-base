<?php

namespace Sitakgmbh\LaraBase\Support;

use Carbon\Carbon;
use LdapRecord\Models\ActiveDirectory\Group;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Exceptions\ConstraintException;
use LdapRecord\Exceptions\InsufficientAccessException;
use LdapRecord\Exceptions\LdapRecordException;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LdapHelper
{
	public static function getAdUser(string $username): ?LdapUser
	{
		if (! $username) 
		{
			return null;
		}

		return LdapUser::query()
			->whereEquals("samaccountname", $username)
			->first();
	}
	
	public static function getAdGroups(string $username): array
	{
		$ldapUser = LdapUser::query()
			->whereEquals("samaccountname", $username)
			->first();

		if (! $ldapUser) 
		{
			return [];
		}

		return collect($ldapUser->getAttribute("memberOf", []))
			->map(fn($dn) => preg_match("/CN=([^,]+)/i", $dn, $m) ? $m[1] : null)
			->filter()
			->values()
			->toArray();
	}

	public static function setAdAttribute(string $username, string $attribute, string|array|null $value): void
	{
		$user = self::getAdUser($username);

		if (! $user) 
		{
			throw new \RuntimeException("Fehler beim Aktualisieren von {$attribute}: AD-Benutzer nicht gefunden");
		}

		try 
		{
			$user->setAttribute($attribute, $value);
			$user->save();
		} 
		catch (\Exception $e) 
		{
			throw new \RuntimeException("Fehler beim Aktualisieren von {$attribute} bei Benutzer {$username}: " . $e->getMessage(), 0, $e);
		}
	}

	public static function getGroupMemberUsernames(string $groupName): array
	{
		$group = \LdapRecord\Models\ActiveDirectory\Group::query()
			->whereEquals('cn', $groupName)
			->first();

		if (!$group) {
			LaraLog::debug("LdapHelper: Gruppe [{$groupName}] nicht gefunden.");
			return [];
		}

		return $group->members()->get()
			->map(fn($m) => $m->getFirstAttribute('samaccountname'))
			->filter()
			->map(fn($s) => strtolower($s))
			->values()
			->toArray();
	}

	public static function updateGroupMembership(string $username, array $groups, bool $ignoreErrors = false): void
	{
		$user = self::getAdUser($username);

		if (! $user) 
		{
			throw new \RuntimeException("AD-Benutzer {$username} nicht gefunden.");
		}

		// SamAccountName absichern (kann Array sein)
		$sam = is_array($user->samaccountname) ? ($user->samaccountname[0] ?? $username) : $user->samaccountname;

		foreach ($groups as $groupName => $shouldBeMember) {
			$group = \LdapRecord\Models\ActiveDirectory\Group::query()
				->whereEquals("cn", $groupName)
				->first();

			if (! $group) 
			{
				$msg = "AD-Gruppe {$groupName} nicht gefunden. Benutzer {$sam} wird übersprungen.";
				LaraLog::debug($msg);
				
				if ($ignoreErrors) 
				{
					continue;
				}
				
				throw new \RuntimeException($msg);
			}

			try 
			{
				if ($shouldBeMember) 
				{
					$group->members()->attach($user);
					LaraLog::debug("{$sam} zu Gruppe {$groupName} hinzugefügt");
				} 
				else 
				{
					$group->members()->detach($user);
					LaraLog::debug("{$sam} aus Gruppe {$groupName} entfernt");
				}
			} 
			catch (\Exception $e) 
			{
				$msg = "Fehler bei Gruppe {$groupName} für Benutzer {$sam}: " . $e->getMessage();
				Logger::error($msg);

				if (! $ignoreErrors) 
				{
					throw new \RuntimeException($msg, 0, $e);
				}
			}
		}
	}

	public static function emailExists(string $email, ?string $ignoreUsername = null): bool
	{
		if (! $email) 
		{
			return false;
		}

		$email = strtolower($email);

		$query = \LdapRecord\Models\ActiveDirectory\User::query()
			->whereEquals('mail', $email);

		if ($ignoreUsername) 
		{
			$query->where('samaccountname', '!=', $ignoreUsername);
		}

		if ($query->exists()) 
		{
			return true;
		}

		$query = \LdapRecord\Models\ActiveDirectory\User::query()
			->whereContains('proxyAddresses', "smtp:$email")
			->orWhereContains('proxyAddresses', "SMTP:$email");

		if ($ignoreUsername) 
		{
			$query->where('samaccountname', '!=', $ignoreUsername);
		}

		return $query->exists();
	}

	public function isReachable(): bool
	{
		try 
		{
			// einfacher Bind-Test
			LdapUser::query()->first();
			return true;
		} 
		catch (\Throwable $e) 
		{
			return false;
		}
	}

    public function findByGuid(string $guid): ?LdapUser
    {
        return LdapUser::findByGuid($guid);
    }

    public function resetPassword(LdapUser $user, string $newPassword): true|string
    {
        try 
		{
            $user->unicodepwd = $newPassword;
            $user->save();
            return true;
        }
        catch (InsufficientAccessException) 
		{
            return "Bind-User hat keine Rechte für Passwort Reset";
        }
        catch (ConstraintException) 
		{
            return "Passwort entspricht nicht Domain-Policy";
        }
        catch (LdapRecordException $e) 
		{
            return "LDAP Fehler: ".$e->getDetailedError()->getDiagnosticMessage();
        }
        catch (\Exception $e) 
		{
            return $e->getMessage();
        }
    }

    public function unlock(LdapUser $user): true|string
    {
        try 
		{
            $user->update(['lockouttime' => 0]);
            return true;
        }
        catch (\Exception $e) 
		{
            return $e->getMessage();
        }
    }

	public function enable(LdapUser $user): true|string
	{
		try 
		{
			$uac = (int)$user->getFirstAttribute('userAccountControl');

			// 512 = Normal Account
			if ($uac !== 512) {
				$user->userAccountControl = 512;
				$user->save();
			}

			return true;
		}
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}

    public function disable(LdapUser $user): true|string
    {
        try 
		{
            // 514 = 512 + Disabled (2)
            $user->userAccountControl = 514;
            $user->save();
            return true;
        }
        catch (\Exception $e) 
		{
            return $e->getMessage();
        }
    }

    public static function delete(LdapUser $user): bool|string
    {
        try 
		{
            $user->delete();

            return true;
        } 
		catch (\Throwable $e) 
		{
            return $e->getMessage();
        }
    }

	/*
    public function forceChangeOnNextLogin(LdapUser $user): true|string
    {
        try {
            $user->update(['pwdlastset' => 0]); // Benutzer muss Passwort beim nächsten Logon ändern
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
	*/
	
	public function forceChangeOnNextLogin(LdapUser $user): void
	{
		// Clear DONT_EXPIRE (0x10000)
		$uac = (int)$user->getFirstAttribute('userAccountControl');
		$uac = $uac & ~0x10000;
		$user->setAttribute('userAccountControl', $uac);

		// Force change: pwdLastSet = 0
		$user->setAttribute('pwdLastSet', '0');
		$user->save();
	}

	public function clearForceChange(LdapUser $user): true|string
	{
		try 
		{
			$user->update(['pwdlastset' => -1]); // Kennwort gilt als gesetzt und erfordert keinen Wechsel
			return true;
		}
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}
	
	public function findBySamAccountName(string $sam): ?LdapUser
	{
		return LdapUser::query()
			->where('sAMAccountName', '=', $sam)
			->first();
	}

	public function isLocked(LdapUser $user): bool
	{
		$value = $user->getFirstAttribute('lockoutTime');

		if (!$value) 
		{
			return false;
		}

		// Fall 1: Wenn AD → DateTime/Carbon
		if ($value instanceof \DateTimeInterface) 
		{
			// locked, wenn Zeitpunkt in Vergangenheit liegt (AD speichert Sperrzeitpunkt)
			return true;
		}

		// Fall 2: Wenn String/Integer (klassischer LDAP Rückgabewert)
		$numeric = (int)$value;

		return $numeric !== 0;
	}

	public function isEnabled(LdapUser $user): bool
	{
		$uac = (int)$user->getFirstAttribute('userAccountControl');

		// Bit 0x2 = ACCOUNTDISABLE
		return ($uac & 0x2) === 0;
	}

	public function requiresPasswordChange(LdapUser $user): bool
	{
		$pwdLastSet = $user->getFirstAttribute('pwdlastset');

		// Fall 1: AD liefert ein Datum / Carbon -> Passwort wurde schon gesetzt
		if ($pwdLastSet instanceof \DateTimeInterface) 
		{
			return false;
		}

		// Fall 2: klassischer Fall (Integer / String): 0 = muss PW ändern
		return ((int) ($pwdLastSet ?? 0)) === 0;
	}

	public function hasActiveExpiration(LdapUser $user): bool
	{
		$dt = $user->getFirstAttribute('accountExpires');

		if (!$dt instanceof \DateTimeInterface) {
			return false;
		}

		// AD "never expire" prüfen (30828-09-13)
		if ((int)$dt->format('Y') >= 30000) {
			return false;
		}

		return $dt > new \DateTime();
	}

	public static function getExpirationDate(LdapUser $user): ?\DateTimeInterface
	{
		$raw = $user->getFirstAttribute('accountExpires');

		if ($raw === null) 
		{
			return null;
		}

		// Fall 1: AD kann direkt Carbon/DateTime liefern → sofort zurück
		if ($raw instanceof \DateTimeInterface) 
		{
			// Prüfungen auf "never expire"
			if ((int)$raw->format('Y') >= 30000) 
			{
				return null;
			}
			
			return $raw;
		}

		// Fall 2: AD liefert numerischen FileTime → prüfen
		$raw = (string)$raw;

		// Never Expires 1: raw == "0"
		if ($raw === '0') 
		{
			return null;
		}

		// Never Expires 2: 9223372036854775807 (INT64_MAX)
		if ($raw == "9223372036854775807") 
		{
			return null;
		}

		// Rest: FileTime -> convert
		$ts = ((int)$raw / 10000000) - 11644473600;

		return Carbon::createFromTimestampUTC($ts);
	}

	public static function setExitDate(LdapUser $user, ?\DateTimeInterface $date): void
	{
		if ($date === null) 
		{
			// Kein Ablauf -> nie ablaufen
			$user->setAttribute('accountExpires', '0');
			$user->save();
			return;
		}

		// Laravel App TZ holen
		$tz = config('app.timezone', 'UTC');

		// +1 Tag gemäss AD-Semantik (Expiry = Tag nach letztem Arbeitstag)
		$exit = Carbon::instance($date)
			->addDay()
			->setTimezone($tz)
			->startOfDay(); // 00:00:00 lokale Zeit

		// Lokale Zeit in Unix Timestamp
		$localTs = $exit->timestamp;

		// FileTime Formel (lokale Zeit, nicht UTC)
		$filetime = ($localTs + 11644473600) * 10000000;

		$user->setAttribute('accountExpires', (string)$filetime);
		$user->save();
	}

}
