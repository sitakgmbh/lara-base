<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('lara-base::livewire.pages.admin.users.index')
            ->layoutData(['pageTitle' => 'Benutzerverwaltung']);
    }
}