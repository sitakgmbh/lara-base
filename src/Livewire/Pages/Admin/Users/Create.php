<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Sitakgmbh\LaraBase\Livewire\Forms\UserForm;

#[Layout('lara-base::layouts.app')]
class Create extends Component
{
    public UserForm $form;

    public function mount(): void
    {
        $this->form->isCreate = true;
    }

    public function save()
    {
        $this->form->validate();

        $model = config('auth.providers.users.model', \App\Models\User::class);

        $user = $model::create([
            'username'   => $this->form->username,
            'firstname'  => $this->form->firstname,
            'lastname'   => $this->form->lastname,
            'email'      => $this->form->email,
            'auth_type'  => 'local',
            'is_enabled' => $this->form->is_enabled,
            'password'   => bcrypt($this->form->password),
        ]);

        $user->assignRole($this->form->role);

        session()->flash('success', 'Benutzer erfolgreich angelegt.');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.users.create', [
            'roles' => $this->form->roles(),
        ])->layoutData(['pageTitle' => 'Benutzer erstellen']);
    }
}