<?php

namespace Sitakgmbh\LaraBase\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    protected $listeners = ['perform-logout' => 'logout'];

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'));
    }

    public function render()
    {
        return view('lara-base::livewire.actions.logout');
    }
}