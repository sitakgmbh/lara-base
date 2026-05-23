<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

class ConfirmModal extends BaseModal
{
    public string $message       = '';
    public string $hint			 = '';
	public string $confirmLabel  = 'Bestätigen';
    public string $confirmClass  = 'btn-primary';
    public string $cancelLabel   = 'Abbrechen';
    public string $confirmEvent  = '';
    public array  $confirmPayload = [];

    protected function openWith(array $payload): bool
    {
        $this->message        = $payload['message']        ?? 'Soll dieser Vorgang wirklich ausgeführt werden?';
		$this->hint = $payload['hint'] ?? '';
        $this->confirmLabel   = $payload['confirmLabel']   ?? 'Bestätigen';
        $this->confirmClass   = $payload['confirmClass']   ?? 'btn-primary';
        $this->cancelLabel    = $payload['cancelLabel']    ?? 'Abbrechen';
        $this->confirmEvent   = $payload['confirmEvent']   ?? '';
        $this->confirmPayload = $payload['confirmPayload'] ?? [];

        $this->title      = $payload['title']     ?? 'Bestätigung';
        $this->size       = 'md';
        $this->backdrop   = true;
        $this->position   = 'centered';
        $this->headerBg   = $payload['headerBg']  ?? 'bg-primary';
        $this->headerText = 'text-white';

        return true;
    }

    public function confirm(): void
    {
        $this->closeModal();

        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, ...$this->confirmPayload);
        }
    }

    public function render()
    {
        return view('lara-base::livewire.components.modals.confirm-modal');
    }
}