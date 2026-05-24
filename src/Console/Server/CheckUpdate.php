<?php

namespace Sitakgmbh\LaraBase\Console\Server;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Sitakgmbh\LaraBase\Logging\Logger;
use Sitakgmbh\LaraBase\Models\Setting;
use ZipArchive;

class CheckUpdate extends Command
{
    protected $signature   = 'server:check-update {--update : Automatisch aktualisieren wenn Update verfügbar}';
    protected $description = 'Prüft ob eine neue Version verfügbar ist und aktualisiert optional.';

    private string $zipPath = 'C:/xampp/web_latest_download.zip';
    private string $target  = 'C:/xampp/htdocs';

    public function handle(): int
    {
        $this->info('=== Update-Check ===');

        try {
            $baseUrl        = rtrim(Setting::getValue('app_update_url'), '/');
            $currentVersion = Setting::getValue('app_version', '0.0.0');

            // Version abrufen
            $this->info('► Prüfe Version...');
            $response = Http::timeout(10)->get($baseUrl . '/version.txt');

            if (!$response->successful()) {
                throw new \Exception('Version konnte nicht abgerufen werden: HTTP ' . $response->status());
            }

            $latestVersion = trim($response->body());

            Setting::setValue('app_version_latest',     $latestVersion);
            Setting::setValue('app_version_checked_at', now()->toDateTimeString());

            $this->line('   Installiert: ' . $currentVersion);
            $this->line('   Verfügbar:   ' . $latestVersion);
            $this->newLine();

            if (!version_compare($latestVersion, $currentVersion, '>')) {
                $this->info('Anwendung ist aktuell.');
                return self::SUCCESS;
            }

            $this->warn('Update verfügbar: ' . $currentVersion . ' → ' . $latestVersion);
            Logger::db('system', 'info', 'Update verfügbar', [
                'installiert' => $currentVersion,
                'verfügbar'   => $latestVersion,
            ]);

            if (!$this->option('update')) {
                $this->line('Starte mit --update um zu aktualisieren.');
                return self::SUCCESS;
            }

            // Update durchführen
            $this->newLine();
            $this->info('► Starte Update...');
            $this->runUpdate($baseUrl);

            // Version aktualisieren
            Setting::setValue('app_version', $latestVersion);
            $this->newLine();
            $this->info('Version aktualisiert: ' . $latestVersion);

            Logger::db('system', 'info', 'Update erfolgreich', [
                'version' => $latestVersion,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Fehler: ' . $e->getMessage());
            Logger::db('system', 'error', 'Update fehlgeschlagen', [
                'fehler' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }

    private function runUpdate(string $baseUrl): void
    {
        $zipUrl = $baseUrl . '/web_latest.zip';

        // Download
        $this->info('► Lade ZIP herunter...');
        $response = Http::timeout(120)->get($zipUrl);

        if (!$response->successful()) {
            throw new \Exception('Download fehlgeschlagen: HTTP ' . $response->status());
        }

        $this->line('   Grösse: ' . round(strlen($response->body()) / 1024, 1) . ' KB');

        // Speichern
        $this->info('► Speichere ZIP...');
        file_put_contents($this->zipPath, $response->body());
        $this->line('   Gespeichert: ' . $this->zipPath);

        // Öffnen
        $this->info('► Öffne ZIP...');
        $zip = new ZipArchive();
        if ($zip->open($this->zipPath) !== true) {
            throw new \Exception('ZIP konnte nicht geöffnet werden.');
        }
        $this->line('   Dateien im ZIP: ' . $zip->numFiles);
        $this->newLine();

        // Entpacken
        $this->info('► Verarbeite Dateien...');
        $updated = [];
        $skipped = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name     = $zip->getNameIndex($i);
            $destPath = str_replace('/', DIRECTORY_SEPARATOR, $this->target . '/' . $name);

            if (str_ends_with($name, '/')) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                    $this->line('   [DIR] Erstellt: ' . $name);
                }
                continue;
            }

            $newContent = $zip->getFromIndex($i);
            if ($newContent === false) {
                $this->warn('   [SKIP] Konnte nicht lesen: ' . $name);
                continue;
            }

            if (is_dir($destPath)) continue;

            $dir = dirname($destPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (file_exists($destPath) && file_get_contents($destPath) === $newContent) {
                $skipped[] = $name;
                continue;
            }

            file_put_contents($destPath, $newContent);
            $updated[] = $name;
            $this->line('   [UPD] ' . $name);
        }

        $zip->close();

        if (file_exists($this->zipPath)) {
            unlink($this->zipPath);
        }

        $this->newLine();
        $this->info('=== Zusammenfassung ===');
        $this->line('   Aktualisiert: ' . count($updated));
        $this->line('   Unverändert:  ' . count($skipped));
    }
}