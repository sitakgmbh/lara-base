<?php

namespace Sitakgmbh\LaraBase\Console\Install;

trait SetupProject
{
    protected function setupProject(): void
    {
        $this->setupWebRoute();
        $this->cleanUpFiles();
		$this->copyReadme();
		$this->copyChangelog();
		$this->copyHelpFiles();
    }

    private function setupWebRoute(): void
    {
        $path    = base_path('routes/web.php');
        $content = file_get_contents($path);

        if (!$this->option('force') && str_contains($content, "redirect()->route('login')")) {
            $this->warn('⚠ routes/web.php bereits angepasst – übersprungen');
            return;
        }

        $content = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Projekt-spezifische Routen hier
PHP;

        file_put_contents($path, $content);
        $this->info('✓ routes/web.php angepasst');
    }

    private function cleanUpFiles(): void
    {
        $files = [
            base_path('phpunit.xml'),
            base_path('vite.config.js'),
            base_path('README.md'),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->info('✓ ' . basename($file) . ' gelöscht');
            }
        }
    }

	private function copyReadme(): void
	{
		$src  = __DIR__ . '/../../../README.md';
		$dest = base_path('README.md');

		if (!file_exists($src)) {
			$this->warn('⚠ README.md nicht gefunden – übersprungen');
			return;
		}

		if (!$this->option('force') && file_exists($dest)) {
			$this->warn('⚠ README.md bereits vorhanden – übersprungen');
			return;
		}

		copy($src, $dest);
		$this->info('✓ README.md kopiert');
	}

	private function copyChangelog(): void
	{
		$src  = __DIR__ . '/../../../CHANGELOG.md';
		$dest = base_path('CHANGELOG.md');

		if (!file_exists($src)) {
			$this->warn('⚠ CHANGELOG.md nicht gefunden – übersprungen');
			return;
		}

		if (!$this->option('force') && file_exists($dest)) {
			$this->warn('⚠ CHANGELOG.md bereits vorhanden – übersprungen');
			return;
		}

		copy($src, $dest);
		$this->info('✓ CHANGELOG.md kopiert');
	}

	private function copyHelpFiles(): void
	{
		$src  = __DIR__ . '/../../../resources/help';
		$dest = resource_path('help');

		if (!is_dir($src)) {
			$this->warn('⚠ Help-Dateien nicht gefunden – übersprungen');
			return;
		}

		if (!is_dir($dest)) {
			mkdir($dest, 0755, true);
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS)
		);

		foreach ($files as $file) {
			$destFile = $dest . DIRECTORY_SEPARATOR . $file->getFilename();

			if (!$this->option('force') && file_exists($destFile)) {
				continue;
			}

			copy($file->getRealPath(), $destFile);
		}

		$this->info('✓ Help-Dateien kopiert');
	}
}