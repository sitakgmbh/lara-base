<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('lara-base::livewire.pages.admin.incidents.index')
            ->layoutData(['pageTitle' => 'Incidents']);
    }
}