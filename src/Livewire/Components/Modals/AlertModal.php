<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

use Livewire\Component;
use Livewire\Attributes\On;

class AlertModal extends Component
{
    public string $message  = '';
    public string $icon     = 'ri-close-circle-line';
    public string $color    = 'bg-danger';
    public string $headline = 'Fehler';

    protected function getModalId(): string
    {
        return 'components.modals.alert-modal';
    }

    #[On('open-modal')]
    public function handleOpen(string $modal, array $payload = []): void
    {
        if ($modal !== $this->getModalId()) {
            return;
        }

        $this->message  = $payload['message']  ?? 'Ein unbekannter Fehler ist aufgetreten.';
        $this->headline = $payload['headline'] ?? $this->headline;
        $this->icon     = $payload['icon']     ?? $this->icon;
        $this->color    = $payload['color']    ?? $this->color;

        $this->dispatch(
            'show-bs-modal',
            id:       $this->getModalId(),
            backdrop: 'static',
            keyboard: false
        );
    }

    public function closeModal(): void
    {
        $this->dispatch('hide-bs-modal', id: $this->getModalId());
    }

    public function render()
    {
        return view('lara-base::livewire.components.modals.alert-modal');
    }
}