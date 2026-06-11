<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Sitakgmbh\LaraBase\Models\Setting;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Update extends Component
{
    public string  $status        = 'checking';
    public string  $statusMessage = 'Suche nach Updates, bitte warten...';

    public string  $currentVersion = '';
    public string  $latestVersion  = '';
    public string  $latestDate     = '';
    public string  $checkedAt      = '';
    public string  $errorMessage   = '';

    public array   $updatedFiles   = [];
    public int     $updatedCount   = 0;

    public function mount(): void
    {
        $this->loadCurrentState();

        if (! $this->validateConfig()) {
            return;
        }

        $this->checkForUpdate();
    }

    private function loadCurrentState(): void
    {
        try {
            $this->currentVersion = (string) (Setting::getValue('app_version', '0.0.0') ?? '0.0.0');
            $this->latestVersion  = (string) (Setting::getValue('app_version_latest', '') ?? '');
            $this->latestDate     = (string) (Setting::getValue('app_version_latest_date', '') ?? '');

            $checkedAtRaw    = Setting::getValue('app_version_checked_at', '');
            $this->checkedAt = $checkedAtRaw
                ? \Carbon\Carbon::parse($checkedAtRaw)->format('d.m.Y H:i:s')
                : '';
        } catch (\Throwable $e) {
            $this->currentVersion = '0.0.0';
            $this->latestVersion  = '';
            $this->latestDate     = '';
            $this->checkedAt      = '';
        }
    }

    private function validateConfig(): bool
    {
        $updateUrl = Setting::getValue('app_update_url', '');

        if (empty($updateUrl)) {
            $this->status        = 'error';
            $this->statusMessage = 'Konfiguration unvollständig.';
            $this->errorMessage  = 'app_update_url ist nicht konfiguriert. Bitte in den Einstellungen hinterlegen.';
            return false;
        }

        if (! filter_var($updateUrl, FILTER_VALIDATE_URL)) {
            $this->status        = 'error';
            $this->statusMessage = 'Konfiguration ungültig.';
            $this->errorMessage  = 'app_update_url ist keine gültige URL: ' . $updateUrl;
            return false;
        }

        return true;
    }

    public function checkForUpdate(): void
    {
        if (! $this->validateConfig()) {
            return;
        }

        $this->status        = 'checking';
        $this->statusMessage = 'Suche nach Updates, bitte warten...';
        $this->errorMessage  = '';
        $this->updatedFiles  = [];
        $this->updatedCount  = 0;

        try {
            $exitCode = Artisan::call('server:check-update');

            $this->loadCurrentState();

            if ($exitCode !== 0) {
                $this->status        = 'error';
                $this->statusMessage = 'Update-Check fehlgeschlagen.';
                $this->errorMessage  = trim(Artisan::output()) ?: 'Unbekannter Fehler.';
                return;
            }

            if ($this->latestVersion && version_compare($this->latestVersion, $this->currentVersion, '>')) {
                $this->status        = 'available';
                $this->statusMessage = 'Update verfügbar!';
            } else {
                $this->status        = 'current';
                $this->statusMessage = 'Die Anwendung ist aktuell.';
            }

        } catch (\Throwable $e) {
            $this->status        = 'error';
            $this->statusMessage = 'Fehler beim Update-Check.';
            $this->errorMessage  = $e->getMessage();
        }
    }

    public function installUpdate(): void
    {
        if (! $this->validateConfig()) {
            return;
        }

        $this->status        = 'updating';
        $this->statusMessage = 'Update wird installiert, bitte warten...';
        $this->updatedFiles  = [];
        $this->updatedCount  = 0;

        try {
            $exitCode = Artisan::call('server:check-update', ['--update' => true]);
            $output   = Artisan::output();

            $this->loadCurrentState();

            if ($exitCode !== 0) {
                $this->status        = 'error';
                $this->statusMessage = 'Installation fehlgeschlagen.';
                $this->errorMessage  = trim($output) ?: 'Unbekannter Fehler.';
                return;
            }

            preg_match_all('/\[UPD\]\s+(.+)/', $output, $matches);
            $this->updatedFiles = $matches[1] ?? [];
            $this->updatedCount = count($this->updatedFiles);

            $this->status        = 'done';
            $this->statusMessage = 'Update erfolgreich installiert.';

        } catch (\Throwable $e) {
            $this->status        = 'error';
            $this->statusMessage = 'Fehler bei der Installation.';
            $this->errorMessage  = $e->getMessage();
        }
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.update')
            ->layoutData(['pageTitle' => 'Update']);
    }
}