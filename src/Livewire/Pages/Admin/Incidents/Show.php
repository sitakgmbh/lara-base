<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Incidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Sitakgmbh\LaraBase\Models\Incident;

#[Layout('lara-base::layouts.app')]
class Show extends Component
{
    public ?Incident $incident = null;

    public function mount(int $id): void
    {
        $this->incident = Incident::with(['creator', 'resolver'])->findOrFail($id);
    }

    public function resolveIncident(): void
    {
        if (!$this->incident || $this->incident->resolved_at) return;

        $this->incident->resolve();
        $this->incident = $this->incident->fresh(['creator', 'resolver']);
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.incidents.show')
            ->layoutData(['pageTitle' => "Details Incident #{$this->incident->id}"]);
    }
}