<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class AdminDashboard extends Component
{
    public function render()
    {
        $groups = collect(config('lara-base.admin_dashboard', []))
            ->groupBy('group')
            ->toArray();

        return view('lara-base::livewire.pages.admin.admin-dashboard', compact('groups'))
            ->layoutData(['pageTitle' => 'Systemsteuerung']);
    }
}