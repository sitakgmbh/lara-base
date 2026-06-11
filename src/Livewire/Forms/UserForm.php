<?php

namespace Sitakgmbh\LaraBase\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserForm extends Form
{
    public $user = null;

    public string $username              = '';
    public string $firstname             = '';
    public string $lastname              = '';
    public string $email                 = '';
    public string $auth_type             = 'local';
    public bool   $is_enabled            = false;
    public string $role                  = 'user';
    public string $password              = '';
    public string $password_confirmation = '';
    public bool   $isCreate              = true;

    public function setUser($user, bool $isCreate = true): void
    {
        $this->user     = $user;
        $this->isCreate = $isCreate;

        if ($user) {
            $this->username   = $user->username   ?? '';
            $this->firstname  = $user->firstname  ?? '';
            $this->lastname   = $user->lastname   ?? '';
            $this->email      = $user->email      ?? '';
            $this->auth_type  = $user->auth_type  ?? 'local';
            $this->is_enabled = (bool) $user->is_enabled;
            $this->role       = $user->roles->pluck('name')->first() ?? $this->role;
        }
    }

    public function rules(): array
    {
        if ($this->auth_type === 'ldap') {
            return [
                'username'  => ['required', 'string', 'max:30'],
                'firstname' => ['required', 'string', 'max:255'],
                'lastname'  => ['required', 'string', 'max:255'],
                'email'     => ['nullable', 'email', 'max:255'],
                'role'      => ['required', 'exists:roles,name'],
            ];
        }

        if ($this->isCreate) {
            return [
                'username'              => ['required', 'string', 'max:30'],
                'firstname'             => ['required', 'string', 'max:255'],
                'lastname'              => ['required', 'string', 'max:255'],
                'email'                 => ['nullable', 'email', 'max:255', 'unique:users,email'],
                'password'              => ['required', 'string', 'min:8', 'same:password_confirmation'],
                'password_confirmation' => ['required', 'same:password'],
                'is_enabled'            => ['boolean'],
                'role'                  => ['required', 'exists:roles,name'],
            ];
        }

        return [
            'username'              => ['required', 'string', 'max:30'],
            'firstname'             => ['required', 'string', 'max:255'],
            'lastname'              => ['required', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'password'              => ['nullable', 'string', 'min:8', 'same:password_confirmation'],
            'password_confirmation' => ['nullable', 'same:password'],
            'is_enabled'            => ['boolean'],
            'role'                  => ['required', 'exists:roles,name'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'username'              => 'Benutzername',
            'firstname'             => 'Vorname',
            'lastname'              => 'Nachname',
            'email'                 => 'E-Mail',
            'auth_type'             => 'Authentifizierungstyp',
            'is_enabled'            => 'Status',
            'role'                  => 'Rolle',
            'password'              => 'Passwort',
            'password_confirmation' => 'Passwortbestätigung',
        ];
    }

    public function roles(): array
    {
        return Role::pluck('name')->toArray();
    }
}