<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('lara-base::layouts.app')]
class UserSettings extends Component
{
    public bool $darkmode_enabled = false;

    public function mount(): void
    {
        $this->darkmode_enabled = (bool) Auth::user()->getSetting('darkmode_enabled', false);
    }

    public function save(): void
    {
        Auth::user()->setSetting('darkmode_enabled', $this->darkmode_enabled);
        \Sitakgmbh\LaraBase\Support\LaraToast::success('Einstellungen gspeichert', '', $this);
    }

    public function render()
    {
        return view('lara-base::livewire.pages.profile.user-settings')
            ->layoutData(['pageTitle' => 'Benutzereinstellungen']);
    }
}