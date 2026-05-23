<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

use Sitakgmbh\LaraBase\Models\Log;

class LogContext extends BaseModal
{
    public ?Log    $log       = null;
    public ?string $permalink = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload['id'] ?? null;

        if (!$id || !($this->log = Log::find($id))) {
            $this->dispatch('open-modal', modal: 'components.modals.alert-modal', payload: [
                'message'  => 'Der Log-Eintrag konnte nicht gefunden werden.',
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);
            return false;
        }

        $this->permalink  = route('admin.logs.show', $this->log->id);
        $this->title      = "Details Log #{$this->log->id}";
        $this->size       = 'lg';
        $this->backdrop   = false;
        $this->position   = 'centered';
        $this->scrollable = true;
        $this->headerBg   = 'bg-primary';
        $this->headerText = 'text-white';

        return true;
    }

    public function render()
    {
        return view('lara-base::livewire.components.modals.log-context');
    }
}