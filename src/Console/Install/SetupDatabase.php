<?php

namespace Sitakgmbh\LaraBase\Console\Install;

use Illuminate\Support\Facades\Artisan;

trait SetupDatabase
{
    protected function setupDatabase(): void
    {
        $this->publishVendors();
		$this->setupLivewireConfig();
        $this->runMigrations();
        $this->runSeeders();
    }

    private function publishVendors(): void
    {
        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--force'    => true,
            '--quiet'    => true,
        ]);
        $this->info('✓ Spatie Permission publiziert');

        Artisan::call('vendor:publish', [
            '--tag'   => 'lara-base-config',
            '--force' => true,
            '--quiet' => true,
        ]);
        $this->info('✓ lara-base Config publiziert');

		Artisan::call('vendor:publish', [
			'--tag'   => 'livewire:config',
			'--force' => true,
			'--quiet' => true,
		]);
		$this->info('✓ Livewire Config publiziert');

		Artisan::call('vendor:publish', [
			'--tag'   => 'lara-base-pagination',
			'--force' => true,
			'--quiet' => true,
		]);
		$this->info('✓ Pagination View publiziert');

    }

    private function runMigrations(): void
    {
        if (config('session.driver') === 'database') {
            Artisan::call('session:table', ['--quiet' => true]);
            $this->info('✓ Session-Migration erstellt');
        }

        Artisan::call('migrate:fresh', ['--quiet' => true]);
        $this->info('✓ Migrationen ausgeführt');
    }

    private function runSeeders(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Sitakgmbh\LaraBase\Database\Seeders\RolesSeeder',
            '--quiet' => true,
        ]);
        $this->info('✓ Rollen angelegt');

        Artisan::call('db:seed', [
            '--class' => 'Sitakgmbh\LaraBase\Database\Seeders\AdminUserSeeder',
            '--quiet' => true,
        ]);
        $this->info('✓ Admin-User angelegt');

        Artisan::call('db:seed', [
            '--class' => 'Sitakgmbh\LaraBase\Database\Seeders\SettingsSeeder',
            '--quiet' => true,
        ]);
        $this->info('✓ Settings angelegt');
    }

	private function setupLivewireConfig(): void
	{
		$path    = config_path('livewire.php');

		if (!file_exists($path)) {
			$this->warn('⚠ config/livewire.php nicht gefunden – übersprungen');
			return;
		}

		$content = file_get_contents($path);
		$content = str_replace(
			"'pagination_theme' => 'tailwind'",
			"'pagination_theme' => 'bootstrap'",
			$content
		);

		file_put_contents($path, $content);
		$this->info('✓ Livewire Pagination auf Bootstrap gesetzt');
	}
}