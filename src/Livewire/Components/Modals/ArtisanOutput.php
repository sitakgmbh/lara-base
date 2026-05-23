<?php

namespace Sitakgmbh\LaraBase\Livewire\Components\Modals;

class ArtisanOutput extends BaseModal
{
    public string  $command  = '';
    public string  $output   = '';
    public ?string $started  = null;
    public ?string $ended    = null;
    public ?string $duration = null;

	protected function openWith(array $payload): bool
	{
		$this->command    = $payload['command']    ?? '';
		$this->output     = $payload['output']     ?? '';
		$this->started    = $payload['started']    ?? null;
		$this->ended      = $payload['ended']      ?? null;
		$this->duration   = $payload['duration']   ?? null;
		$this->title      = 'Artisan Output';
		$this->size       = 'lg';
		$this->headerBg   = $payload['headerBg']   ?? 'bg-primary';
		$this->headerText = $payload['headerText'] ?? 'text-white';
		$this->backdrop   = false;
		$this->position   = 'centered';
		$this->scrollable = true;

		return true;
	}

    public function render()
    {
        return view('lara-base::livewire.components.modals.artisan-output');
    }
}