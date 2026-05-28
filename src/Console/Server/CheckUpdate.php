<?php

namespace Sitakgmbh\LaraBase\Console\Server;

use Illuminate\Console\Command;
use Sitakgmbh\LaraBase\Logging\Logger;
use Sitakgmbh\LaraBase\Models\Setting;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class CheckUpdate extends Command
{
    protected $signature   = 'server:check-update {--update : Automatisch aktualisieren wenn Update verfügbar}';
    protected $description = 'Prüft ob eine neue Version verfügbar ist und aktualisiert optional.';

    private string $zipPath;
    private string $target = 'C:/xampp/htdocs';

    public function __construct()
    {
        parent::__construct();
        $this->zipPath = storage_path('app/updates/web_latest_download.zip');
    }

    public function handle(): int
    {
        $this->info('=== WinStage Update-Check ===');

        try {
            $baseUrl        = rtrim(Setting::getValue('app_update_url'), '/');
            $currentVersion = Setting::getValue('app_version', '0.0.0');

            // Version abrufen via cURL
			$this->info('► Prüfe Version...');
			$response = Http::timeout(120)->get($baseUrl . '/version.php');

			if (!$response->successful()) {
				throw new \Exception('Version konnte nicht abgerufen werden: HTTP ' . $response->status());
			}

			$data          = $response->json();
			$latestVersion = $data['version'] ?? null;
			$latestDate    = $data['date']    ?? null;

            if (!$latestVersion) {
                throw new \Exception('Ungültige Versionsantwort vom Server.');
            }

            Setting::setValue('app_version_latest',      $latestVersion);
            Setting::setValue('app_version_latest_date', $latestDate);
            Setting::setValue('app_version_checked_at',  now()->toDateTimeString());

            $this->line('   Installiert: ' . $currentVersion);
            $this->line('   Verfügbar:   ' . $latestVersion . ($latestDate ? ' (' . $latestDate . ')' : ''));
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

            Setting::setValue('app_version', $latestVersion);
            $this->newLine();
            $this->info('Version aktualisiert auf: ' . $latestVersion);

            Logger::db('system', 'info', 'Update erfolgreich', [
                'version' => $latestVersion,
                'datum'   => $latestDate,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Fehler: ' . $e->getMessage());
            Logger::db('system', 'error', 'Update-Check fehlgeschlagen', [
                'fehler' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }

	private function runUpdate(string $baseUrl): void
	{
		$zipUrl = $baseUrl . '/web_latest.zip';

		// Download via Guzzle
		$this->info('► Lade ZIP herunter...');
		$response = Http::timeout(120)->get($zipUrl);

		if (!$response->successful()) {
			throw new \Exception('Download fehlgeschlagen: HTTP ' . $response->status());
		}

		$body = $response->body();
		$this->line('   Grösse: ' . round(strlen($body) / 1024, 1) . ' KB');

		// Speichern
		$this->info('► Speichere ZIP...');
		$dir = dirname($this->zipPath);
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		file_put_contents($this->zipPath, $body);
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
			$stat     = $zip->statIndex($i);
			$name     = $stat['name'];
			$destPath = str_replace('/', DIRECTORY_SEPARATOR, $this->target . '/' . $name);

			// Verzeichniseinträge
			if (str_ends_with($name, '/') || str_ends_with($destPath, DIRECTORY_SEPARATOR)) {
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

			// Zielverzeichnis erstellen
			$destDir = dirname($destPath);
			if (!is_dir($destDir)) {
				mkdir($destDir, 0755, true);
			}

			// Unveränderte Dateien überspringen
			if (file_exists($destPath) && file_get_contents($destPath) === $newContent) {
				$skipped[] = $name;
				continue;
			}

			file_put_contents($destPath, $newContent);
			$updated[] = $name;
			$this->line('   [UPD] ' . $name);
		}

		$zip->close();
		unset($zip);
		gc_collect_cycles();
		sleep(1);

		try {
			if (file_exists($this->zipPath)) {
				unlink($this->zipPath);
			}
		} catch (\Exception $e) {
			$this->warn('ZIP konnte nicht gelöscht werden: ' . $e->getMessage());
		}

		$this->newLine();
		$this->info('=== Zusammenfassung ===');
		$this->line('   Aktualisiert: ' . count($updated));
		$this->line('   Unverändert:  ' . count($skipped));
	}
}