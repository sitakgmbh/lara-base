<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Auth;

use Sitakgmbh\LaraBase\Livewire\Forms\Auth\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.auth')]
class LoginPage extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirectIntended(route('dashboard'));
        }
    }

	public function login(): void
	{
		$this->validate();
		$this->form->authenticate();
		Session::regenerate();

		$user = auth()->user();

		$this->redirectIntended(route('dashboard'));
	}

    public function render()
    {
        return view('lara-base::livewire.pages.auth.login');
    }
}