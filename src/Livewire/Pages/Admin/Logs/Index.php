<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('lara-base::livewire.pages.admin.logs.index')
            ->layoutData(['pageTitle' => 'Logs']);
    }
}