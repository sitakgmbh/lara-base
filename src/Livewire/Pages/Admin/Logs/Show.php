<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Sitakgmbh\LaraBase\Models\Log;

#[Layout('lara-base::layouts.app')]
class Show extends Component
{
    public Log $log;

    public function mount(Log $log): void
    {
        $this->log = $log;
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.logs.show')
            ->layoutData(['pageTitle' => "Log #{$this->log->id}"]);
    }
}