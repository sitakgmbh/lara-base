<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Profile;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Sitakgmbh\LaraBase\Livewire\Forms\ProfileForm;

#[Layout('lara-base::layouts.app')]
class Edit extends Component
{
    public ProfileForm $form;

    public function mount(): void
    {
        $this->form->setUser();
    }

    public function save(): void
    {
        $this->form->validate();

        $user = $this->form->user;

        $user->update([
            'firstname' => $this->form->firstname,
            'lastname'  => $this->form->lastname,
            'email'     => $this->form->email,
            'password'  => $this->form->password ? bcrypt($this->form->password) : $user->password,
        ]);

		\Sitakgmbh\LaraBase\Support\LaraToast::success('Profil aktualisiert', '', $this);
    }

    public function render()
    {
        return view('lara-base::livewire.pages.profile.edit')
            ->layoutData(['pageTitle' => 'Profil bearbeiten']);
    }
}