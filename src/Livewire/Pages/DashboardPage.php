<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class DashboardPage extends Component
{
    public function render()
    {
        return view('lara-base::livewire.pages.dashboard')
            ->layoutData(['pageTitle' => 'Dashboard']);
    }
}