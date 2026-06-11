<?php

namespace Sitakgmbh\LaraBase\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Sitakgmbh\LaraBase\Models\Incident;
use Sitakgmbh\LaraBase\Facades\LaraUserSettings;

class Topbar extends Component
{
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'));
    }

    public function render()
    {
        $openIncidents = collect();

        try {
            if (auth()->user()?->hasRole('admin')) {
                $openIncidents = Incident::open()
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Throwable) {
            // Tabelle existiert noch nicht
        }

        return view('lara-base::livewire.layout.topbar', [
            'openIncidents' => $openIncidents,
        ]);
    }

	public function toggleDarkMode(): void
	{
		$current = (bool) LaraUserSettings::get('darkmode_enabled', false);
		$new     = !$current;

		LaraUserSettings::set('darkmode_enabled', $new);
		session()->put('darkmode_enabled', $new);
		session()->save();

		$this->dispatch('theme-changed', dark: $new);
	}

}