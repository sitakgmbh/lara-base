<?php

namespace Sitakgmbh\LaraBase\Livewire\Traits;

use Livewire\Attributes\On;

trait ModalFlow
{
    public array $modalQueue  = [];
    public bool  $isModalOpen = false;

    public function startModalFlow(array $modals): void
    {
        $this->modalQueue = $modals;

        if (!empty($this->modalQueue)) {
            $this->isModalOpen = false;
            $this->openNextModal();
        }
    }

    protected function openNextModal(): void
    {
        if ($this->isModalOpen) return;

        $next = array_shift($this->modalQueue);

        if (!$next) return;

        $this->isModalOpen = true;
        $this->dispatch('open-modal', $next['id'], $next['payload'] ?? []);
    }

    #[On('modal-closed')]
    public function handleModalClosed($payload = []): void
    {
        $this->isModalOpen = false;
        $this->openNextModal();
    }
}