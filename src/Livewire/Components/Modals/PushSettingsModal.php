<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

class PushSettingsModal extends BaseModal
{
    public array $pushCategories = [];

    protected function openWith(array $payload): bool
    {
        if (! config('lara-base.pwa.enabled') || ! config('lara-base.pwa.push.enabled')) {
            return false;
        }

        $this->pushCategories = \LaraNotify::categoriesForUser(auth()->user());

        if (empty($this->pushCategories)) {
            return false;
        }

        $this->title      = 'Push-Benachrichtigungen';
        $this->size       = 'md';
        $this->backdrop   = true;
        $this->position   = 'centered';
        $this->scrollable = false;
        $this->headerBg   = 'bg-primary';
        $this->headerText = 'text-white';

        return true;
    }

    public function render()
    {
        return view('lara-base::livewire.components.modals.push-settings');
    }
}