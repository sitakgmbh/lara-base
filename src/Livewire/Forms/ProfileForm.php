<?php

namespace Sitakgmbh\LaraBase\Livewire\Forms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProfileForm extends Form
{
    public $user = null;

    public string $firstname             = '';
    public string $lastname              = '';
    public string $email                 = '';
    public string $auth_type             = '';
    public string $current_password      = '';
    public string $password              = '';
    public string $password_confirmation = '';

    public function setUser(): void
    {
        $user = Auth::user();
        $this->user      = $user;
        $this->firstname = $user->firstname ?? '';
        $this->lastname  = $user->lastname  ?? '';
        $this->email     = $user->email     ?? '';
        $this->auth_type = $user->auth_type ?? 'local';
    }

    public function rules(): array
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'email'     => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
        ];

        if ($this->password || $this->current_password) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password']         = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function validationAttributes(): array
    {
        return [
            'firstname'             => 'Vorname',
            'lastname'              => 'Nachname',
            'email'                 => 'E-Mail',
            'current_password'      => 'Aktuelles Passwort',
            'password'              => 'Passwort',
            'password_confirmation' => 'Passwortbestätigung',
        ];
    }
}