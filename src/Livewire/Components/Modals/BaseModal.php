<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

use Livewire\Component;
use Livewire\Attributes\On;

abstract class BaseModal extends Component
{
    public string $title      = '';
    public string $size       = 'md';
    public bool   $backdrop   = false;
    public string $position   = 'centered';
    public bool   $scrollable = true;
    public string $headerBg   = '';
    public string $headerText = '';

    protected function getModalId(): string
    {
        return static::getName();
    }

    #[On('open-modal')]
    public function handleOpen(string $modal, array $payload = []): void
    {
        if ($modal !== $this->getModalId()) {
            return;
        }

        if ($this->openWith($payload) !== false) {
            $this->openModal();
        }
    }

    protected function openWith(array $payload): bool
    {
        return true;
    }

    public function openModal(): void
    {
        $this->dispatch(
            'show-bs-modal',
            id:       $this->getModalId(),
            backdrop: $this->backdrop ? 'static' : true,
            keyboard: $this->backdrop ? false : true
        );
    }

    public function closeModal(): void
    {
        $this->dispatch('hide-bs-modal', id: $this->getModalId());
    }
}