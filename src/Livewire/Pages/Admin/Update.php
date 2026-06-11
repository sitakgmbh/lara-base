<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Sitakgmbh\LaraBase\Models\Setting;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Update extends Component
{
    public string $status        = 'checking'; // checking | current | available | updating | done | error
    public string $statusMessage = 'Suche nach Updates, bitte warten...';

    public string $currentVersion = '';
    public string $latestVersion  = '';
    public string $latestDate     = '';
    public string $checkedAt      = '';
    public string $errorMessage   = '';

    public array $updatedFiles = [];
    public int   $updatedCount = 0;

    public function mount(): void
    {
        $this->checkForUpdate();
    }

    public function checkForUpdate(): void
    {
        $this->status        = 'checking';
        $this->statusMessage = 'Suche nach Updates, bitte warten...';
        $this->errorMessage  = '';
        $this->updatedFiles  = [];
        $this->updatedCount  = 0;

        try {
            $exitCode = Artisan::call('server:check-update');

            $this->currentVersion = Setting::getValue('app_version',              '0.0.0');
            $this->latestVersion  = Setting::getValue('app_version_latest',       '');
            $this->latestDate     = Setting::getValue('app_version_latest_date',  '');
			$checkedAtRaw    = Setting::getValue('app_version_checked_at', '');
			$this->checkedAt = $checkedAtRaw ? \Carbon\Carbon::parse($checkedAtRaw)->format('d.m.Y H:i:s') : '';

            if ($exitCode !== 0) {
                $this->status        = 'error';
                $this->statusMessage = 'Update-Check fehlgeschlagen.';
                $this->errorMessage  = Artisan::output();
                return;
            }

            if ($this->latestVersion && version_compare($this->latestVersion, $this->currentVersion, '>')) {
                $this->status        = 'available';
                $this->statusMessage = 'Update verfügbar!';
            } else {
                $this->status        = 'current';
                $this->statusMessage = 'Die Anwendung ist aktuell.';
            }

        } catch (\Exception $e) {
            $this->status        = 'error';
            $this->statusMessage = 'Fehler beim Update-Check.';
            $this->errorMessage  = $e->getMessage();
        }
    }

    public function installUpdate(): void
    {
        $this->status        = 'updating';
        $this->statusMessage = 'Update wird installiert, bitte warten...';
        $this->updatedFiles  = [];
        $this->updatedCount  = 0;

        try {
            $exitCode = Artisan::call('server:check-update', ['--update' => true]);
            $output   = Artisan::output();

            $this->currentVersion = Setting::getValue('app_version',             '0.0.0');
            $this->latestVersion  = Setting::getValue('app_version_latest',      '');
            $this->latestDate     = Setting::getValue('app_version_latest_date', '');

            if ($exitCode !== 0) {
                $this->status        = 'error';
                $this->statusMessage = 'Installation fehlgeschlagen.';
                $this->errorMessage  = $output;
                return;
            }

            // Geänderte Dateien aus Output parsen
            preg_match_all('/\[UPD\]\s+(.+)/', $output, $matches);
            $this->updatedFiles = $matches[1] ?? [];
            $this->updatedCount = count($this->updatedFiles);

            $this->status        = 'done';
            $this->statusMessage = 'Update erfolgreich installiert.';

        } catch (\Exception $e) {
            $this->status        = 'error';
            $this->statusMessage = 'Fehler bei der Installation.';
            $this->errorMessage  = $e->getMessage();
        }
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.update')
            ->layout('layouts.app', ['pageTitle' => 'Update']);
    }
}