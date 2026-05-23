<?php

namespace Sitakgmbh\LaraBase\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Sitakgmbh\LaraBase\Auth\LdapProvisioningService;

class ApiAuthSwitcher
{
    public function handle($request, Closure $next)
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            return $this->handleSso($request, $next);
        }

        return $this->handleBasicAuth($request, $next);
    }

    private function handleSso($request, Closure $next)
    {
        $rawUser  = $_SERVER['REMOTE_USER'];
        $username = explode('\\', $rawUser)[1] ?? $rawUser;
        $model    = config('auth.providers.users.model', \App\Models\User::class);
        $user     = $model::where('username', $username)->first();

        if (!$user) {
            $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

            if (!$ldapUser) {
                return response()->json(['message' => 'Ungültiges Login'], 403);
            }

            $user = app(LdapProvisioningService::class)->provisionOrUpdateUserFromLdap($ldapUser, $username, true);
        }

        Auth::setUser($user);
        return $next($request);
    }

    private function handleBasicAuth($request, Closure $next)
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        if (!$username || !$password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        $model = config('auth.providers.users.model', \App\Models\User::class);
        $user  = $model::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        Auth::setUser($user);
        return $next($request);
    }
}