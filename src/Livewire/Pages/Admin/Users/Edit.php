<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Sitakgmbh\LaraBase\Livewire\Forms\UserForm;

#[Layout('lara-base::layouts.app')]
class Edit extends Component
{
    public UserForm $form;

    public function mount($user): void
    {
        $model = config('auth.providers.users.model', \App\Models\User::class);
        $user  = $model::findOrFail($user);
        $this->form->setUser($user, false);
    }

    public function save()
    {
        $this->form->validate();

        $user = $this->form->user;

        $user->update([
            'username'   => $this->form->username,
            'firstname'  => $this->form->firstname,
            'lastname'   => $this->form->lastname,
            'email'      => $this->form->email,
            'auth_type'  => $this->form->auth_type,
            'is_enabled' => $this->form->is_enabled,
            'password'   => $this->form->password ? bcrypt($this->form->password) : $user->password,
        ]);

        $user->syncRoles([$this->form->role]);

		\Sitakgmbh\LaraBase\Support\LaraToast::success('Benutzer aktualisiert.', '');
		return redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.users.edit', [
            'roles' => $this->form->roles(),
        ])->layoutData(['pageTitle' => 'Benutzer bearbeiten']);
    }
}